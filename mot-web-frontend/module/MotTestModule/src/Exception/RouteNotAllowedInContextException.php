<?php

namespace Dvsa\Mot\Frontend\MotTestModule\Exception;

class RouteNotAllowedInContextException extends \Exception
{
    public function __construct()
    {
        parent::__construct('This url/route is not allowed in this context');
    }
}