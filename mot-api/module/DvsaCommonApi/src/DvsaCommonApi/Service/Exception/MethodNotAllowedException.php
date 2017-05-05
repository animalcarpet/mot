<?php
/**
 * This file is part of the DVSA MOT API project.
 *
 * @link https://github.com/dvsa/mot
 */

namespace DvsaCommonApi\Service\Exception;

use Zend\Http\Response;

class MethodNotAllowedException extends ServiceException
{
    public function __construct(
        $message = 'Method is not allowed',
        $statusCode = Response::STATUS_CODE_405,
        \Exception $previous  = null
    )
    {
        parent::__construct($message, $statusCode, $previous);
        $this->addError($message, $statusCode);
    }
}
