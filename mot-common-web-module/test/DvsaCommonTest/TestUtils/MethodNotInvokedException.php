<?php

namespace DvsaCommonTest\TestUtils;

class MethodNotInvokedException extends \Exception
{
    public function __construct($methodName)
    {
        parent::__construct("Method '" . $methodName. "' has not been invoked'");
    }
}
