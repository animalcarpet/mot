<?php

namespace DvsaCommonApi\Service\Exception;

/**
 * Class TooManyResultsException.
 */
class TooManyResultsException extends ServiceException
{
    const MESSAGE = 'Too many results.';
    const ERROR_CODE_TOO_MANY_RESULTS = 22;

    public function __construct($message = null)
    {
        $msg = $message ?: self::MESSAGE;

        parent::__construct($msg, self::BAD_REQUEST_STATUS_CODE);

        $this->addError($msg, self::ERROR_CODE_TOO_MANY_RESULTS, $msg);
    }
}
