<?php

namespace App\Action;

use App\Form\AbstractForm;
use App\Form\Log;
use App\Form\PoaNoneFound;
use App\Form\ProcessNewClaim;
use Exception;
use Opg\Refunds\Caseworker\DataModel\Cases\Claim as ClaimModel;
use Opg\Refunds\Caseworker\DataModel\Cases\Poa as PoaModel;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;
use Slim\Flash\Messages;
use Zend\Diactoros\Response\HtmlResponse;

/**
 * Class ClaimAction
 * @package App\Action
 */
class ClaimAction extends AbstractClaimAction
{
    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     * @return HtmlResponse
     * @throws Exception
     */
    public function indexAction(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $claim = $this->getClaim($request);

        if ($claim === null) {
            throw new Exception('Claim not found', 404);
        }

        $form = $this->getForm($request, $claim);

        return new HtmlResponse($this->getTemplateRenderer()->render(
            'app::claim-page',
            $this->getViewModel($request, $claim, $form)
        ));
    }

    public function addAction(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $session = $request->getAttribute('session');
        $form = new ProcessNewClaim([
            'csrf' => $session['meta']['csrf'],
        ]);

        $form->setData($request->getParsedBody());

        if ($form->isValid()) {
            $userId = $request->getAttribute('identity')->getId();
            $assignedClaimId = $this->claimService->assignNextClaim($userId);

            if ($assignedClaimId === 0) {
                //No available claims

                /** @var Messages $flash */
                $flash = $request->getAttribute('flash');
                $flash->addMessage('info', 'There are no more claims to process');

                return $this->redirectToRoute('home');
            }

            return $this->redirectToRoute('claim', ['id' => $assignedClaimId]);
        }

        // The only reason the form can be invalid is a CSRF check fail so no need to recover gracefully
        throw new Exception('CSRF failure', 500);
    }

    public function editAction(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        //Even though we are adding a log message here,
        //we are technically editing the claim by adding a log message to it
        $claim = $this->getClaim($request);

        $form = $this->getForm($request, $claim);

        if ($request->getMethod() == 'POST') {
            $form->setData($request->getParsedBody());

            if ($form->isValid()) {
                $message = $form->get('message')->getValue();

                $log = $this->claimService->addLog($this->modelId, 'Caseworker note', $message);

                if ($log === null) {
                    throw new RuntimeException('Failed to add new log to claim with id: ' . $this->modelId);
                }

                return $this->redirectToRoute('claim', ['id' => $this->modelId]);
            }
        }

        return new HtmlResponse($this->getTemplateRenderer()->render(
            'app::claim-page',
            $this->getViewModel($request, $claim, $form)
        ));
    }

    /**
     * @param ServerRequestInterface $request
     * @param ClaimModel $claim
     * @return AbstractForm
     */
    public function getForm(ServerRequestInterface $request, ClaimModel $claim): AbstractForm
    {
        $session = $request->getAttribute('session');
        $form = new Log([
            'claim' => $claim,
            'csrf'  => $session['meta']['csrf'],
        ]);
        return $form;
    }

    /**
     * @param ServerRequestInterface $request
     * @param $claim
     * @param $form
     * @return array
     */
    private function getViewModel($request, $claim, $form): array
    {
        $session = $request->getAttribute('session');
        $poaNoneFoundForm = new PoaNoneFound([
            'csrf' => $session['meta']['csrf'],
        ]);

        return [
            'claim'                 => $claim,
            'form'                  => $form,
            'poaNoneFoundForm'      => $poaNoneFoundForm,
            'poaSiriusNoneFoundUrl' => $this->getUrlHelper()->generate('claim.poa.none.found', [
                'id' => $this->modelId,
                'system' => PoaModel::SYSTEM_SIRIUS
            ]),
            'poaMerisNoneFoundUrl' => $this->getUrlHelper()->generate('claim.poa.none.found', [
                'id' => $this->modelId,
                'system' => PoaModel::SYSTEM_MERIS
            ])
        ];
    }
}
