<?php

namespace App\Action\Poa;

use Api\Exception\ApiException;
use App\Form\AbstractForm;
use App\Form\Poa;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Opg\Refunds\Caseworker\DataModel\Cases\Claim as ClaimModel;
use Opg\Refunds\Caseworker\DataModel\Cases\Poa as PoaModel;
use App\Service\Claim\Claim as ClaimService;
use App\Service\Poa\Poa as PoaService;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Exception;
use RuntimeException;

/**
 * Class PoaAction
 * @package App\Action\Poa
 */
class PoaAction extends AbstractPoaAction
{
    /**
     * @var PoaService
     */
    private $poaService;

    public function __construct(ClaimService $claimService, PoaService $poaService)
    {
        parent::__construct($claimService);

        $this->poaService = $poaService;
    }

    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     * @return HtmlResponse
     * @throws Exception
     */
    public function indexAction(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $claim = $this->getClaim($request);
        $poa = null;

        if ($this->modelId !== null) {
            //Edit page
            $poa = $this->getPoa($claim);

            if ($poa === null) {
                throw new Exception('POA not found', 404);
            }
        }

        /** @var Poa $form */
        $form = $this->getForm($request, $claim, $poa);

        $system = $request->getAttribute('system');

        return new HtmlResponse($this->getTemplateRenderer()->render('app::poa-page', [
            'form'   => $form,
            'claim'  => $claim,
            'system' => $system,
            'poaId'  => $this->modelId,
        ]));
    }

    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     * @return HtmlResponse|\Zend\Diactoros\Response\RedirectResponse
     */
    public function addAction(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $claim = $this->getClaim($request);
        $system = $request->getAttribute('system');

        $form = $this->getForm($request, $claim);

        /** @var Poa $form */
        $form->setData($request->getParsedBody());

        if ($form->isValid()) {
            $poa = new PoaModel($form->getModelData());

            try {
                $claim = $this->claimService->addPoa($claim, $poa);

                if ($claim === null) {
                    throw new RuntimeException('Failed to add new POA to claim with id: ' . $this->modelId);
                }

                //TODO: Find a better way
                if ($_POST['submit'] === 'Save and add another') {
                    return $this->redirectToRoute('claim.poa', [
                        'claimId' => $request->getAttribute('claimId'),
                        'system'  => $system,
                        'id'      => null
                    ]);
                }

                return $this->redirectToRoute('claim', ['id' => $request->getAttribute('claimId')]);
            } catch (ApiException $ex) {
                if ($ex->getCode() === 400) {
                    $form->setMessages(['case-number' => ['Case number is already registered with another claim']]);
                } else {
                    throw $ex;
                }
            }
        }

        return new HtmlResponse($this->getTemplateRenderer()->render('app::poa-page', [
            'claim'  => $claim,
            'form'   => $form,
            'system' => $system
        ]));
    }

    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     * @return HtmlResponse|\Zend\Diactoros\Response\RedirectResponse
     */
    public function editAction(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $claim = $this->getClaim($request);
        $system = $request->getAttribute('system');

        /** @var Poa $form */
        $form = $this->getForm($request, $claim);

        $form->setData($request->getParsedBody());

        if ($form->isValid()) {
            $poa = new PoaModel($form->getModelData());

            try {
                $claim = $this->claimService->editPoa($claim, $poa, $this->modelId);

                if ($claim === null) {
                    throw new RuntimeException('Failed to edit POA with id: ' . $this->modelId);
                }

                //TODO: Find a better way
                if ($_POST['submit'] === 'Save and add another') {
                    return $this->redirectToRoute('claim.poa', [
                        'claimId' => $request->getAttribute('claimId'),
                        'system'  => $system,
                        'id'      => null
                    ]);
                }

                return $this->redirectToRoute('claim', ['id' => $request->getAttribute('claimId')]);
            } catch (ApiException $ex) {
                if ($ex->getCode() === 400) {
                    $form->setMessages(['case-number' => ['Case number is already registered with another claim']]);
                } else {
                    throw $ex;
                }
            }
        }

        return new HtmlResponse($this->getTemplateRenderer()->render('app::poa-page', [
            'form'   => $form,
            'claim'  => $claim,
            'system' => $request->getAttribute('system')
        ]));
    }

    /**
     * @param ServerRequestInterface $request
     * @param ClaimModel $claim
     * @param PoaModel|null $poa
     * @return AbstractForm
     */
    protected function getForm(ServerRequestInterface $request, ClaimModel $claim, PoaModel $poa = null): AbstractForm
    {
        $session = $request->getAttribute('session');

        $form = new Poa($this->poaService, [
            'claim'  => $claim,
            'poa'    => $poa,
            'system' => $request->getAttribute('system'),
            'csrf'   => $session['meta']['csrf'],
        ]);

        return $form;
    }
}