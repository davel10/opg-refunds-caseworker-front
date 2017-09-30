<?php

namespace App\Action\Claim;

use App\Service\Claim\Claim as ClaimService;
use App\Service\Poa\PoaFormatter as PoaFormatterService;
use Interop\Container\ContainerInterface;

/**
 * Class ClaimAcceptActionFactory
 * @package App\Action\Claim
 */
class ClaimAcceptActionFactory
{
    /**
     * @param ContainerInterface $container
     * @return ClaimAcceptAction
     */
    public function __invoke(ContainerInterface $container)
    {
        return new ClaimAcceptAction(
            $container->get(ClaimService::class),
            $container->get(PoaFormatterService::class)
        );
    }
}