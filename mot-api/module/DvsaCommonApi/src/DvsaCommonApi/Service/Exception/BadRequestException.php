<?php

namespace DvsaCommonApi\Service\Exception;

/**
 * Exception that should be thrown in case request input is syntactically incorrect
 * Class BadRequestException
 *
 * @package DvsaCommonApi\Service\Exception
 */
class BadRequestException extends ServiceException
{
    const ERROR_CODE_INVALID_DATA = 60;
    const ERROR_CODE_REPEATED_ADDITION = 80;
    const ERROR_CODE_INVALID_ENTITY_STATE = 100;
    const ERROR_CODE_BUSINESS_FAILURE = 120;
    const ERROR_CODE_INVALID_SURVEY_TOKEN = 140;

    /**
     * @param string $message
     * @param int    $errorCode
     * @param null   $displayMessage
     * @param null   $fieldDataStructure
     */
    //TODO change constructor to be less dependant on dat
    public function __construct(
        $message,
        $errorCode,
        $displayMessage = null,
        $fieldDataStructure = null
    ) {
        parent::__construct($message, self::BAD_REQUEST_STATUS_CODE);

        $this->addError($message, $errorCode, ($displayMessage ? $displayMessage : $message), $fieldDataStructure);
    }

    /**
     * @param string $message
     *
     * @return BadRequestException
     */
    public static function create($message = "")
    {
        $ex = new BadRequestException($message, self::BAD_REQUEST_STATUS_CODE);
        $ex->clearErrors();
        return $ex;
    }
}
