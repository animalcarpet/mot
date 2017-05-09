<?php

namespace DvsaCommonApi\Service\Exception;

/**
 * Class TooFewResultsException.
 */
class TooFewResultsException extends ServiceException
{
    const MESSAGE = 'Too few results.';
    const ERROR_CODE_TOO_FEW_RESULTS = 23;

    public function __construct($message = null)
    {
        $msg = $message ?: self::MESSAGE;

        parent::__construct($msg, self::BAD_REQUEST_STATUS_CODE);

        $this->addError($msg, self::ERROR_CODE_TOO_FEW_RESULTS, $msg);
    }
}
