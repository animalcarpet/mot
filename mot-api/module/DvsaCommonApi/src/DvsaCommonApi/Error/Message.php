<?php

namespace DvsaCommonApi\Error;

/**
 * Class Message.
 *
 * A lightweight class to store error messages
 */
class Message
{
    public $message = '';
    public $displayMessage = '';
    public $errorCode = null;
    public $fieldDataStructure = null;

    public function __construct($message, $errorCode, $fieldDataStructure = null, $displayMessage = null)
    {
        if ($displayMessage === null) {
            $displayMessage = $message;
        }

        $this->displayMessage = $displayMessage;
        $this->message = $message;
        $this->errorCode = $errorCode;
        $this->fieldDataStructure = $fieldDataStructure;
    }

    /**
     * @return null|string
     */
    public function getDisplayMessage()
    {
        return $this->displayMessage;
    }

    /**
     * @return null|int
     */
    public function getErrorCode()
    {
        return $this->errorCode;
    }

    /**
     * @return null|array
     */
    public function getFieldDataStructure()
    {
        return $this->fieldDataStructure;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }
}
