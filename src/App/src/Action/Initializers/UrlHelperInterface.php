<?php

namespace App\Action\Initializers;

use Zend\Expressive\Helper\UrlHelper;

/**
 * Declares Action Middleware support for UrlHelper
 *
 * Interface UrlHelperInterface
 * @package App\Action\Initializers
 */
interface UrlHelperInterface
{
    /**
     * @param UrlHelper $template
     * @return mixed
     */
    public function setUrlHelper(UrlHelper $template);

    /**
     * @return UrlHelper
     */
    public function getUrlHelper() : UrlHelper;
}
