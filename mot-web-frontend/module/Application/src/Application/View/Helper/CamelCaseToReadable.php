<?php

namespace Application\View\Helper;

use Zend\View\Helper\AbstractHelper;

/**
 * Class.
 */
class CamelCaseToReadable extends AbstractHelper
{
    public function __invoke($string = null)
    {
        if (!$string) {
            return null;
        }

        return ucwords(preg_replace('/([a-z0-9])([A-Z])/', '$1 $2', trim($string)));
    }
}
