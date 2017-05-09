<?php

namespace DvsaCommonApi\Service\Exception;

/**
 * Class BadRequestExceptionWithMultipleErrors.
 */
class BadRequestExceptionWithMultipleErrors extends BadRequestException
{
    public function __construct($mainErrors = [], $formFieldErrors = [])
    {
        foreach ($mainErrors as $error) {
            if (count($this->getErrors()) === 0) {
                parent::__construct(
                    $error->getMessage(),
                    $error->getErrorCode(),
                    $error->getDisplayMessage(),
                    $error->getFieldDataStructure()
                );
            } else {
                $this->addError(
                    $error->getMessage(),
                    $error->getErrorCode(),
                    $error->getDisplayMessage(),
                    $error->getFieldDataStructure()
                );
            }
        }

        foreach ($formFieldErrors as $error) {
            if (count($this->getErrors()) === 0) {
                parent::__construct(
                    $error->getMessage(),
                    $error->getErrorCode(),
                    $error->getDisplayMessage(),
                    $error->getFieldDataStructure()
                );
            } else {
                $this->addError(
                    $error->getMessage(),
                    $error->getErrorCode(),
                    $error->getDisplayMessage(),
                    $error->getFieldDataStructure()
                );
            }
        }
    }
}
