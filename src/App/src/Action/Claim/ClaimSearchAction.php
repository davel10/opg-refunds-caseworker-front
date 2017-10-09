<?php

namespace App\Action\Claim;

use App\Form\ClaimSearch;
use App\Service\Claim\Claim as ClaimService;
use App\Action\AbstractAction;
use Fig\Http\Message\RequestMethodInterface;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;

/**
 * Class ClaimSearchAction
 * @package App\Action\Claim
 */
class ClaimSearchAction extends AbstractAction
{
    /**
     * @var ClaimService
     */
    protected $claimService;

    /**
     * AbstractClaimAction constructor
     * @param ClaimService $claimService
     */
    public function __construct(ClaimService $claimService)
    {
        $this->claimService = $claimService;
    }

    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     * @return HtmlResponse
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $form = $this->getForm($request);

        $searchParameters = array_merge($request->getQueryParams(), $request->getParsedBody());

        $form->setData($searchParameters);

        if ($request->getMethod() === RequestMethodInterface::METHOD_POST) {
            //  Search
            if ($form->isValid()) {
                //  Unset non search form links so they aren't added to search links
                unset($searchParameters['secret']);
                unset($searchParameters['submit']);

                //  Redirect to search with get params
                return $this->redirectToRoute('claim.search', [], $searchParameters);
            }
        }

        $page = isset($searchParameters['page']) ? $searchParameters['page'] : null;
        $pageSize = isset($searchParameters['pageSize']) ? $searchParameters['pageSize'] : null;
        $donorName = isset($searchParameters['donorName']) ? $searchParameters['donorName'] : null;
        $assignedToId = isset($searchParameters['assignedToId']) ? $searchParameters['assignedToId'] : null;
        $status = isset($searchParameters['status']) ? $searchParameters['status'] : null;
        $accountHash = isset($searchParameters['accountHash']) ? $searchParameters['accountHash'] : null;

        $claimSummaryPage = $this->claimService->searchClaims($page, $pageSize, $donorName, $assignedToId, $status, $accountHash);

        //  Unset page so it isn't added to search links
        unset($searchParameters['page']);

        return new HtmlResponse($this->getTemplateRenderer()->render('app::claim-search-page', [
            'form'             => $form,
            'claimSummaryPage' => $claimSummaryPage,
            'searchParameters' => $searchParameters
        ]));
    }

    /**
     * @param ServerRequestInterface $request
     * @return ClaimSearch
     */
    protected function getForm(ServerRequestInterface $request): ClaimSearch
    {
        $session = $request->getAttribute('session');

        $form = new ClaimSearch([
            'csrf'   => $session['meta']['csrf'],
        ]);

        return $form;
    }
}