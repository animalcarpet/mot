<?php

namespace DvsaCommonApi\Service\Exception;

/**
 * Class EmptyRequestBodyException.
 */
class EmptyRequestBodyException extends ServiceException
{
    const MESSAGE = 'Request body is missing';

    public function __construct()
    {
        parent::__construct(self::MESSAGE, self::BAD_REQUEST_STATUS_CODE);
        $this->addError(self::MESSAGE, self::BAD_REQUEST_STATUS_CODE, self::MESSAGE);
    }
}
