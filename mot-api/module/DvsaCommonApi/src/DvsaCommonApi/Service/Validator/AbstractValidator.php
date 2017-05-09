<?php

namespace DvsaCommonApi\Service\Validator;

use DvsaCommon\Utility\TypeCheck;
use DvsaCommonApi\Service\Exception\BadRequestException;
use DvsaCommonApi\Service\Exception\RequiredFieldException;
use Zend\Validator\Between as ValidateBetween;

/**
 * Class AbstractValidator.
 */
abstract class AbstractValidator
{
    protected $errors;

    public function __construct($errors = null)
    {
        if (null !== $errors) {
            $this->errors = $errors;
        } else {
            $this->errors = new ErrorSchema();
        }
    }

    /**
     * @return BadRequestException
     */
    protected function getEmptyBadRequestException() //TODO create exception at end, use builder to gather errors
    {
        $exception = new BadRequestException(
            'Error in brake test configuration validation',
            BadRequestException::ERROR_CODE_INVALID_DATA
        );
        $exception->clearErrors();

        return $exception;
    }

    protected function addMessageToException(BadRequestException $exception, $message)
    {
        $exception->addError($message, BadRequestException::ERROR_CODE_INVALID_DATA, $message);
    }

    protected function isPositiveNumber($value)
    {
        return is_numeric($value) && $value > 0;
    }

    protected function isPositiveInteger($value)
    {
        return filter_var($value, FILTER_VALIDATE_INT) && $value > 0;
    }

    protected function isPositiveNumberOrZero($value)
    {
        return is_numeric($value) && $value >= 0;
    }

    protected function isPositiveIntegerOrNull($value)
    {
        return $value === null || $this->isPositiveInteger($value);
    }

    protected function isPositiveNumberOrZeroOrNull($value)
    {
        return $value === null || $this->isPositiveNumberOrZero($value);
    }

    protected function isPositiveNumberOrZeroOrNullOrEmpty($value)
    {
        return $this->isEmpty($value) || $this->isPositiveNumberOrZeroOrNull($value);
    }

    protected function isBoolOrNull($value)
    {
        return $value === null || $this->isBool($value);
    }

    protected function isBool($value)
    {
        return is_bool($value);
    }

    protected function isNull($value)
    {
        return $value === null;
    }

    protected function isEmpty($field)
    {
        if (strval($field) === '' && !is_bool($field)) {
            return true;
        }

        return false;
    }

    public function getErrors()
    {
        return $this->errors;
    }

    protected function atLeastOneOfGroupIsTrue($data, $group)
    {
        $n = count($group);
        while ($n--) {
            if (isset($data[$group[$n]]) && true === $data[$group[$n]]) {
                return true;
            }
        }

        return false;
    }

    protected function checkRequiredFields(array $requiredFields, array $data)
    {
        if ($requiredFields && TypeCheck::isCollectionOfClass($requiredFields, RequiredField::class)) {
            /** @var RequiredField[] $requiredFields */
            $missingFieldNames = [];
            foreach ($requiredFields as $requiredField) {
                if (!array_key_exists($requiredField->getDataName(), $data)
                    || $data[$requiredField->getDataName()] === ''
                    || is_null($data[$requiredField->getDataName()])
                ) {
                    $missingFieldNames[] = $requiredField->getDisplayName();
                }
            }
            if ($missingFieldNames) {
                throw new RequiredFieldException($missingFieldNames);
            }
        } else {
            RequiredFieldException::CheckIfRequiredFieldsNotEmpty($requiredFields, $data);
        }

        return true;
    }

    protected function validateValuesOfRequiredFields($requiredFields, $data)
    {
        $invalidFields = [];
        foreach ($requiredFields as $requiredFieldName) {
            if (!isset($data[$requiredFieldName]) || $this->isEmpty($data[$requiredFieldName])) {
                $invalidFields[] = $requiredFieldName;
            }
        }

        if (count($invalidFields) > 0) {
            throw new RequiredFieldException($invalidFields);
        }

        return true;
    }

    protected function validateRequiredField($fieldName, $data, $message)
    {
        if ($this->isRequiredValueMissing($fieldName, $data)) {
            $this->errors->add($message, $fieldName);
        }
    }

    protected function isRequiredValueMissing($fieldName, $data)
    {
        return !array_key_exists($fieldName, $data) || empty($data[$fieldName]);
    }

    protected function isValueBetween($value, $min, $max)
    {
        $validator = new ValidateBetween(['min' => $min, 'max' => $max]);

        return $validator->isValid($value);
    }
}
