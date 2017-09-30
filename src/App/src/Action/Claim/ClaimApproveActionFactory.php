<?php

namespace App\Action\Claim;

use App\Service\Claim\Claim as ClaimService;
use App\Service\Poa\PoaFormatter as PoaFormatterService;
use Interop\Container\ContainerInterface;

/**
 * Class ClaimApproveActionFactory
 * @package App\Action\Claim
 */
class ClaimApproveActionFactory
{
    /**
     * @param ContainerInterface $container
     * @return ClaimApproveAction
     */
    public function __invoke(ContainerInterface $container)
    {
        return new ClaimApproveAction(
            $container->get(ClaimService::class),
            $container->get(PoaFormatterService::class)
        );
    }
}