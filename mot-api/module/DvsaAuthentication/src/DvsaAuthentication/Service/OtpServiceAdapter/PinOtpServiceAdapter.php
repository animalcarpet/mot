<?php

namespace DvsaAuthentication\Service\OtpServiceAdapter;

use DvsaAuthentication\Service\OtpServiceAdapter;
use DvsaEntities\Entity\Person;

class PinOtpServiceAdapter implements OtpServiceAdapter
{
    /**
     * @param Person $person
     * @param string $token
     *
     * @return bool
     */
    public function authenticate(Person $person, $token)
    {
        return password_verify($token, $person->getPin());
    }
}
