<?php

namespace AccountApi\Service\Exception;

use DvsaCommonApi\Service\Exception\ServiceException;

class OpenAmChangePasswordException extends ServiceException
{
    public function __construct($message, $statusCode = self::DEFAULT_STATUS_CODE, \Exception $previous = null)
    {
        parent::__construct($message, $statusCode, $previous);
    }
}
