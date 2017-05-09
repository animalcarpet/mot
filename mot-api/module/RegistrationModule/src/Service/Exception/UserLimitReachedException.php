<?php

namespace Dvsa\Mot\Api\RegistrationModule\Service\Exception;

class UserLimitReachedException extends \DomainException
{
    public function __construct($message = '', $code = 0, Exception $previous = null)
    {
        if (empty($message)) {
            $message = 'Upper limit for usernames reached';
        }

        parent::__construct($message, $code, $previous);
    }
}
