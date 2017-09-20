<?php

namespace App\Action;

use App\Service\RefundCaseService;
use Interop\Container\ContainerInterface;

/**
 * Class ProcessNewClaimActionFactory
 * @package App\Action
 */
class ProcessNewClaimActionFactory
{
    /**
     * @param ContainerInterface $container
     * @return ProcessNewClaimAction
     */
    public function __invoke(ContainerInterface $container)
    {
        return new ProcessNewClaimAction(
            $container->get(RefundCaseService::class)
        );
    }
}