<?php

namespace DvsaCommonApi\Service\Exception;

/**
 * Class InvalidFieldValueException.
 */
class InvalidFieldValueException extends ServiceException
{
    const MESSAGE = 'Invalid field value';
    const ERROR_CODE_INVALID_FIELD_VALUE = 21;

    public function __construct($message = null)
    {
        $msg = $message ?: self::MESSAGE;

        parent::__construct($msg, self::BAD_REQUEST_STATUS_CODE);

        $this->addError($msg, self::ERROR_CODE_INVALID_FIELD_VALUE, $msg);
    }
}
