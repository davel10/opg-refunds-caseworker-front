<?php

namespace App\Action\Poa;

use App\Action\AbstractModelAction;
use App\Service\Claim as ClaimService;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class PoaSiriusNoneFoundAction
 * @package App\Action\Poa
 */
class PoaSiriusNoneFoundAction extends AbstractModelAction
{
    /**
     * @var ClaimService
     */
    private $claimService;

    /**
     * PoaSiriusNoneFoundAction constructor.
     * @param ClaimService $claimService
     */
    public function __construct(ClaimService $claimService)
    {
        $this->claimService = $claimService;
    }

    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     * @return ResponseInterface
     */
    public function indexAction(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $this->claimService->setNoSiriusPoas($this->modelId, true);

        return $this->redirectToRoute('claim', ['id' => $this->modelId]);
    }
}