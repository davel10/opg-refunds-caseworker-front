<?php

namespace App\Action\Poa;

use App\Action\AbstractClaimAction;
use App\Form\Poa;
use Exception;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Opg\Refunds\Caseworker\DataModel\Cases\Poa as PoaModel;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\Response\RedirectResponse;

abstract class AbstractPoaAction extends AbstractClaimAction
{
    /**
     * @var string
     */
    protected $templateName;

    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     * @return HtmlResponse
     * @throws Exception
     */
    public function indexAction(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $claim = $this->getClaim($request);

        /** @var Poa $form */
        $form = $this->getForm($request, $claim);

        $viewModel = [
            'form' => $form,
            'claim' => $claim
        ];

        if ($this->modelId !== null) {
            //Edit page
            $poa = $this->getPoa($claim);
            if ($poa === null) {
                throw new Exception('POA not found', 404);
            }
            $form->bindModelData($poa);

            $viewModel['deleteUrl'] = $this->getUrlHelper()->generate('claim.poa.delete', [
                'claimId' => $request->getAttribute('claimId'),
                'id' => $this->modelId
            ]);
        }

        return new HtmlResponse($this->getTemplateRenderer()->render($this->templateName, $viewModel));
    }

    /**
     * GET/POST add action
     *
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     * @return HtmlResponse|RedirectResponse
     * @throws Exception
     */
    public function addAction(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $claim = $this->getClaim($request);

        $form = $this->getForm($request, $claim);

        if ($request->getMethod() == 'POST') {
            /** @var Poa $form */
            $form->setData($request->getParsedBody());

            if ($form->isValid()) {
                $poa = new PoaModel($form->getModelData());

                $claim = $this->claimService->addPoa($claim, $poa);

                if ($claim === null) {
                    throw new RuntimeException('Failed to add new POA to claim with id: ' . $this->modelId);
                }

                //TODO: Find a better way
                if ($_POST['submit'] === 'Save and add another') {
                    return $this->redirectToRoute('claim.poa.' . $poa->getSystem(), [
                        'claimId' => $request->getAttribute('claimId'),
                        'id' => null
                    ]);
                }

                return $this->redirectToRoute('claim', ['id' => $request->getAttribute('claimId')]);
            }
        }

        return new HtmlResponse($this->getTemplateRenderer()->render($this->templateName, [
            'claim' => $claim,
            'form'  => $form
        ]));
    }

    /**
     * GET/POST edit action
     *
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     * @return HtmlResponse|RedirectResponse
     * @throws Exception
     */
    public function editAction(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $claim = $this->getClaim($request);

        /** @var Poa $form */
        $form = $this->getForm($request, $claim);

        $form->setData($request->getParsedBody());

        if ($form->isValid()) {
            $poa = new PoaModel($form->getModelData());

            $claim = $this->claimService->editPoa($claim, $poa, $this->modelId);

            if ($claim === null) {
                throw new RuntimeException('Failed to edit POA with id: ' . $this->modelId);
            }

            //TODO: Find a better way
            if ($_POST['submit'] === 'Save and add another') {
                return $this->redirectToRoute('claim.poa.' . $poa->getSystem(), [
                    'claimId' => $request->getAttribute('claimId'),
                    'id' => null
                ]);
            }

            return $this->redirectToRoute('claim', ['id' => $request->getAttribute('claimId')]);
        }

        return new HtmlResponse($this->getTemplateRenderer()->render($this->templateName, [
            'form'  => $form,
            'claim' => $claim
        ]));
    }
}