<?php
/**
 * This file is part of the DVSA MOT API project.
 *
 * @link https://github.com/dvsa/mot
 */

namespace Account\Exception;

class LimitReachedException extends \Exception
{
    public function __construct($message = '', $code = 0, Exception $previous = null)
    {
        if (empty($message)) {
            $message = 'Reached maximum number of attempt';
        }
        parent::__construct($message, $code, $previous);
    }
}
