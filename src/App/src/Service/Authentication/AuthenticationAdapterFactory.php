<?php

namespace App\Service\Authentication;

use Api\Service\Client as ApiClient;
use Interop\Container\ContainerInterface;

/**
 * Class AuthenticationAdapterFactory
 * @package App\Service\Auth
 */
class AuthenticationAdapterFactory
{
    /**
     * @param ContainerInterface $container
     * @return AuthenticationAdapter
     */
    public function __invoke(ContainerInterface $container)
    {
        return new AuthenticationAdapter(
            $container->get(ApiClient::class)
        );
    }
}
