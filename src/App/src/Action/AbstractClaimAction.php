<?php

namespace App\Action;

use App\Form\AbstractForm;
use Opg\Refunds\Caseworker\DataModel\Cases\Claim as ClaimModel;
use App\Service\Claim as ClaimService;
use Psr\Http\Message\ServerRequestInterface;

abstract class AbstractClaimAction extends AbstractModelAction
{
    /**
     * @var ClaimService
     */
    protected $claimService;

    /**
     * ClaimAction constructor.
     * @param ClaimService $claimService
     */
    public function __construct(ClaimService $claimService)
    {
        $this->claimService = $claimService;
    }

    /**
     * @param ServerRequestInterface $request
     * @return \Opg\Refunds\Caseworker\DataModel\Cases\Claim
     */
    public function getClaim(ServerRequestInterface $request): ClaimModel
    {
        //Retrieve claim to verify it exists and the user has access to it
        $claimId = $request->getAttribute('claimId') ?: $this->modelId;
        $claim = $this->claimService->getClaim($claimId, $request->getAttribute('identity')->getId());
        return $claim;
    }

    /**
     * @param ServerRequestInterface $request
     * @param ClaimModel $claim
     * @return AbstractForm
     */
    abstract public function getForm(ServerRequestInterface $request, ClaimModel $claim): AbstractForm;
}