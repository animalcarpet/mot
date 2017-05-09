<?php

namespace DvsaCommonApi\Service\Exception;

/**
 * Class RequiredFieldException.
 */
class RequiredFieldException extends ServiceException
{
    const MESSAGE = 'A required field is missing';
    const ERROR_CODE_REQUIRED = 20;

    public function __construct($missingFieldNames)
    {
        parent::__construct(self::MESSAGE, self::BAD_REQUEST_STATUS_CODE);

        foreach ($missingFieldNames as $missingFieldName) {
            $this->_errors[] = self::GetRequiredFieldError($missingFieldName);
        }
    }

    public static function GetRequiredFieldError($fieldName)
    {
        $message = "$fieldName is required";

        return self::createError($message, self::ERROR_CODE_REQUIRED, $message, $fieldName);
    }

    public static function CheckIfRequiredFieldsNotEmpty($requiredFieldNames, $data)
    {
        $missingFieldNames = [];
        foreach ($requiredFieldNames as $requiredFieldName) {
            if (!array_key_exists($requiredFieldName, $data)
                || $data[$requiredFieldName] === ''
                || is_null($data[$requiredFieldName])) {
                $missingFieldNames[] = $requiredFieldName;
            }
        }

        if (count($missingFieldNames) > 0) {
            throw new self($missingFieldNames);
        }
    }
}
