<?php

namespace DvsaCommonApi\Service\Exception;

use Zend\View\Model\JsonModel;

/**
 * Class ServiceException.
 */
class ServiceException extends \Exception
{
    const DEFAULT_STATUS_CODE = 500;
    const BAD_REQUEST_STATUS_CODE = 400;
    const ERROR_GENERIC_MSG = 'An error has occurred';

    protected $_errors = [];
    protected $_errorData = [];

    public function __construct($message, $statusCode = self::DEFAULT_STATUS_CODE, \Exception $previous = null)
    {
        parent::__construct($message, $statusCode, $previous);
    }

    public function setStatusCode($code)
    {
        $this->code = $code;

        return $this;
    }

    public function getErrors()
    {
        return $this->_errors;
    }

    public function noErrors()
    {
        return !$this->hasErrors();
    }

    public function hasErrors()
    {
        return count($this->_errors) > 0;
    }

    public function throwIfErrors()
    {
        if ($this->hasErrors()) {
            throw $this;
        }
    }

    public function clearErrors()
    {
        $this->_errors = [];
        $this->_errorData = [];
    }

    public function getErrorData()
    {
        return $this->_errorData;
    }

    public function addError(
        $errorMessage,
        $code,
        $displayMessage = self::ERROR_GENERIC_MSG,
        $fieldDataStructure = null
    ) {
        $count = count($this->_errors);

        if (!empty($fieldDataStructure)) {
            $this->_errors[$count] = self::createError($errorMessage, $code, $displayMessage);
            $errorData = $this->_errorData;
            if (is_array($fieldDataStructure)) {
                $fieldDataStructure = $this->createReferenceToMainError($fieldDataStructure, $count);
                $newErrorData = $this->arrayMergeRecursiveDistinct($errorData, $fieldDataStructure);
                $this->_errorData = $newErrorData;
            }
        } else {
            $this->_errors[$count] = self::createError($errorMessage, $code, $displayMessage);
        }

        return $this;
    }

    public function addErrorField(
        $field,
        $code,
        $errorMessage
    ) {
        $count = count($this->_errors);

        $this->_errors[$count] = self::createError($errorMessage, $code, $errorMessage, $field);
    }

    public static function createError(
        $errorMessage,
        $code,
        $displayMessage = self::ERROR_GENERIC_MSG,
        $fieldName = null
    ) {
        $err = [
            'message' => $errorMessage,
            'code' => $code,
            'displayMessage' => $displayMessage,
        ];

        if (!empty($fieldName)) {
            $err += ['field' => $fieldName];
        }

        return $err;
    }

    public function getJsonModel()
    {
        $responseData = ['errors' => $this->getErrors()];
        $errorData = $this->getErrorData();
        if (!empty($errorData)) {
            $responseData['errorData'] = $errorData;
        }

        return new JsonModel($responseData);
    }

    /**
     * Populate the leaves of a multi-dimensional array with data.
     *
     * This is designed to allow the errorData structure to reference the index of the
     * main errors array.
     *
     * @param array $data
     * @param mixed $ref
     *
     * @return array
     */
    private static function createReferenceToMainError($data, $ref)
    {
        if (!is_array($data)) {
            return $data;
        }

        // Here $item is the first value from the $data array.
        array_walk_recursive(
            $data, function (&$item, $key, $ref) {
                $item = $ref;
            }, $ref
        );

        return $data;
    }

    /**
     * array_merge_recursive will reindex numeric keys.  This works better for our use case.
     *
     * E.g. Merge
     *
     * array('rfrs' => array(2098 => array('justification' => null)))
     *
     * with
     *
     * array('rfrs' => array(2099 => array('justification' => null)))
     *
     * We want it to keep the rfrId keys unmodified.
     *
     *
     * === Taken and modified from php.net comments section on array_merge_recursive ====
     * array_merge_recursive does indeed merge arrays, but it converts values with duplicate
     * keys to arrays rather than overwriting the value in the first array with the duplicate
     * value in the second array, as array_merge does. I.e., with array_merge_recursive,
     * this happens (documented behavior):
     *
     * array_merge_recursive(array('key' => 'org value'), array('key' => 'new value'));
     *     => array('key' => array('org value', 'new value'));
     *
     * array_merge_recursive_distinct does not change the datatypes of the values in the arrays.
     * Matching keys' values in the second array overwrite those in the first array, as is the
     * case with array_merge, i.e.:
     *
     * array_merge_recursive_distinct(array('key' => 'org value'), array('key' => 'new value'));
     *     => array('key' => array('new value'));
     *
     * Parameters are passed by reference, though only for performance reasons. They're not
     * altered by this function.
     *
     * @param array $array1
     * @param array $array2
     *
     * @return array
     *
     * @author Daniel <daniel (at) danielsmedegaardbuus (dot) dk>
     * @author Gabriel Sobrinho <gabriel (dot) sobrinho (at) gmail (dot) com>
     */
    public function arrayMergeRecursiveDistinct(array &$array1, array &$array2)
    {
        $merged = $array1;

        foreach ($array2 as $key => &$value) {
            if (is_array($value) && isset($merged[$key]) && is_array($merged[$key])) {
                $merged[$key] = $this->arrayMergeRecursiveDistinct($merged[$key], $value);
            } else {
                if (isset($merged[$key])) {
                    $merged[$key] = [$merged[$key], $value];
                } else {
                    $merged[$key] = $value;
                }
            }
        }

        return $merged;
    }
}
