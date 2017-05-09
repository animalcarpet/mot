<?php

namespace DvsaCommonApi\Service\Exception;

/**
 * Class DataValidationException.
 */
class DataValidationException extends ServiceException
{
    public function __construct()
    {
        parent::__construct('Data validation error', 422);
    }

    /**
     * @return DataValidationException
     */
    public static function create()
    {
        return new self();
    }
}
