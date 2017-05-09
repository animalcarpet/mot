<?php

namespace DvsaCommonApi\Service\Validator;

use DvsaCommonApi\Service\Exception\BadRequestException;
use DvsaCommonApi\Service\Exception\ServiceException;

/**
 * Class ErrorSchema.
 */
class ErrorSchema
{
    /**
     * @var string[][]
     */
    private $fields = []; // contains errors per field

    /**
     * @var string[]
     */
    private $globalErrors = [];

    /**
     * @param string $errorMessage
     * @param null   $field        , if specified the error will be attached to the given field,
     *                             if not then it's attached as a global error
     */
    public function add($errorMessage, $field = null)
    {
        if ($field) {
            $this->addFieldError($errorMessage, $field);
        } else {
            $this->addGlobalError($errorMessage);
        }
    }

    /**
     * Adds errors from exception.
     *
     * @param ServiceException $exception
     */
    public function addException(ServiceException $exception)
    {
        foreach ($exception->getErrors() as $error) {
            $this->add(
                $error['message'],
                (isset($error['field']) ? $error['field'] : null)
            );
        }
    }

    private function addFieldError($errorMessage, $field)
    {
        if (!array_key_exists($field, $this->fields)) {
            $this->fields[$field] = [];
        }

        $this->fields[$field][] = $errorMessage;
    }

    private function addGlobalError($errorMessage)
    {
        $this->globalErrors[] = $errorMessage;
    }

    /**
     * @return int Number of all global and field errors
     */
    public function count()
    {
        return count($this->getAll());
    }

    /**
     * @param $field
     *
     * @return string[]
     */
    public function getForField($field)
    {
        return $this->fields[$field];
    }

    /**
     * @return string[]
     */
    public function getGlobal()
    {
        return $this->globalErrors;
    }

    /**
     * @return string[] All errors, both global and field specific
     */
    public function getAll()
    {
        $allErrors = $this->globalErrors;

        foreach ($this->fields as $field) {
            $allErrors = array_merge($allErrors, $field);
        }

        return $allErrors;
    }

    public function hasErrors()
    {
        return $this->count() > 0;
    }

    public function getException()
    {
        $exceptionMassage = 'Validation errors encountered';
        $errorCode = BadRequestException::ERROR_CODE_INVALID_DATA;
        $exception = new BadRequestException($exceptionMassage, $errorCode);
        $exception->clearErrors();

        foreach ($this->getAll() as $message) {
            $exception->addError($message, $errorCode, $message);
        }

        return $exception;
    }

    public function throwIfAny()
    {
        if ($this->hasErrors()) {
            throw $this->getException();
        }
    }

    public function getExceptionField()
    {
        $exceptionMassage = 'Validation errors encountered';
        $errorCode = BadRequestException::ERROR_CODE_INVALID_DATA;
        $exception = new BadRequestException($exceptionMassage, $errorCode);
        $exception->clearErrors();

        foreach ($this->fields as $field => $message) {
            $exception->addErrorField($field, $errorCode, $message[0]);
        }

        return $exception;
    }

    public function throwIfAnyField()
    {
        if ($this->hasErrors()) {
            throw $this->getExceptionField();
        }
    }

    /**
     * Instantly throws a validation error.
     *
     * @param      $errorMessage
     * @param null $field
     *
     * @throws \DvsaCommonApi\Service\Exception\BadRequestException
     */
    public static function throwError($errorMessage, $field = null)
    {
        $errors = new self();
        $errors->add($errorMessage, $field);

        throw $errors->getException();
    }
}
