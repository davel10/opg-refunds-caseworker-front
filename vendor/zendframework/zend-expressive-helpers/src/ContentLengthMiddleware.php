<?php
/**
 * @see       https://github.com/zendframework/zend-expressive-helpers for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-expressive-helpers/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Expressive\Helper;

use Psr\Http\Message\ServerRequestInterface;
use Webimpress\HttpMiddlewareCompatibility\HandlerInterface as DelegateInterface;
use Webimpress\HttpMiddlewareCompatibility\MiddlewareInterface;

use const Webimpress\HttpMiddlewareCompatibility\HANDLER_METHOD;

/**
 * Middleware to inject a Content-Length response header.
 *
 * If the response returned by a delegate does not contain a Content-Length
 * header, and the body size is non-null, this middleware will return a new
 * response that contains a Content-Length header based on the body size.
 */
class ContentLengthMiddleware implements MiddlewareInterface
{
    /**
     * {@inheritDoc}
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $response = $delegate->{HANDLER_METHOD}($request);
        if ($response->hasHeader('Content-Length')) {
            return $response;
        }

        $body = $response->getBody();
        if (null === $body->getSize()) {
            return $response;
        }

        return $response->withHeader('Content-Length', (string) $body->getSize());
    }
}
