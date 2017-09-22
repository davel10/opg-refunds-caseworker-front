<?php

namespace App\Action;

use App\Form\SignIn;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Authentication\AuthenticationService;
use Zend\Diactoros\Response;

/**
 * Class SignInAction
 * @package App\Action
 */
class SignInAction extends AbstractAction
{
    /**
     * @var AuthenticationService
     */
    private $authService;

    /**
     * SignInAction constructor
     *
     * @param AuthenticationService $authService
     */
    public function __construct(AuthenticationService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     * @return Response\HtmlResponse|Response\RedirectResponse
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        if ($this->authService->hasIdentity()) {
            return $this->redirectToRoute('home');
        }

        $session = $request->getAttribute('session');

        //  There is no active session so continue
        $form = new SignIn([
            'csrf' => $session['meta']['csrf']
        ]);

        $authenticationError = null;

        if ($request->getMethod() == 'POST') {
            $form->setData($request->getParsedBody());

            if ($form->isValid()) {
                //  Set the session as the authentication storage and the credentials
                $this->authService->getAdapter()
                    ->setEmail($form->get('email')->getValue())
                    ->setPassword($form->get('password')->getValue());

                $result = $this->authService->authenticate();

                if ($result->isValid()) {
                    return $this->redirectToRoute('home');
                } else {
                    //  There should be only one error
                    $authenticationError = $result->getMessages()[0];
                }
            }
        }

        return new Response\HtmlResponse($this->getTemplateRenderer()->render('app::sign-in-page', [
            'form'  => $form,
            'error' => $authenticationError,
        ]));
    }
}
