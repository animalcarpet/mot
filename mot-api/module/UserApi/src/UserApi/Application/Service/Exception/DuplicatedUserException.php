<?php

namespace UserApi\Application\Service\Exception;

use DvsaCommonApi\Service\Exception\ServiceException;

/**
 * Class DuplicatedUserException.
 */
class DuplicatedUserException extends ServiceException
{
    const DUPLICATED_USER_CODE = 123;

    public function __construct($message)
    {
        parent::__construct($message, self::BAD_REQUEST_STATUS_CODE);

        $this->addError($message, self::DUPLICATED_USER_CODE, $message);
    }
}
