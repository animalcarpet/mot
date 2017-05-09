<?php

namespace DvsaCommonApi\Service\Exception;

/**
 * Class NotFoundException.
 */
class NotFoundException extends ServiceException
{
    const ERROR_CODE_NOT_FOUND = 404;
    const ERROR_MSG_NOT_FOUND = '%s%s not found';

    public function __construct($object, $key = null, $useFormat = true)
    {
        $message = sprintf(self::ERROR_MSG_NOT_FOUND, $object, ($key ? ' '.$key : ''));

        if (!$useFormat) {
            $message = $object;
        }

        parent::__construct($message, 404);

        $this->addError($message, self::ERROR_CODE_NOT_FOUND, $message);
    }
}
