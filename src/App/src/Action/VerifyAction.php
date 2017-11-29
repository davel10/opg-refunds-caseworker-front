<?php

namespace App\Action;

use App\Form\Verify as VerifyForm;
use App\Service\Refund\Refund as RefundService;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;

/**
 * Class VerifyAction
 * @package App\Action
 */
class VerifyAction extends AbstractModelAction
{
    /**
     * @var RefundService
     */
    private $verifyService;

    public function __construct(RefundService $verifyService)
    {
        $this->verifyService = $verifyService;
    }

    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     * @return HtmlResponse
     */
    public function indexAction(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $form = $this->getForm($request);

        return new HtmlResponse($this->getTemplateRenderer()->render('app::verify-page', [
            'form'  => $form,
            'messages' => $this->getFlashMessages($request)
        ]));
    }

    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     * @return HtmlResponse
     */
    public function addAction(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $form = $this->getForm($request);

        $form->setData($request->getParsedBody());

        if ($form->isValid()) {
            $notified = $this->verifyService->verifyAll();

            if ($notified['total'] === 0) {
                $message = 'No outcome notifications needed sending. Please try again later.';
            } else {
                $message = "Successfully sent outcome notifications for {$notified['processed']} claims. Verify time {$notified['verifyTime']}s.";

                $remaining = $notified['total'] - $notified['processed'];
                if ($remaining !== 0) {
                    $message .= " There are still {$remaining} claims left to send outcome notifications for. Please try again.";
                }
            }

            $this->setFlashInfoMessage($request, $message);

            return $this->redirectToRoute('verify');
        }

        return new HtmlResponse($this->getTemplateRenderer()->render('app::verify-page', [
            'form'  => $form,
            'messages' => $this->getFlashMessages($request)
        ]));
    }

    /**
     * @param ServerRequestInterface $request
     * @return VerifyForm
     */
    protected function getForm(ServerRequestInterface $request): VerifyForm
    {
        $session = $request->getAttribute('session');

        $form = new VerifyForm([
            'csrf'   => $session['meta']['csrf'],
        ]);

        return $form;
    }
}