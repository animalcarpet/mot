<?php

namespace DvsaAuthentication\Service;

use DvsaEntities\Entity\Person;

interface OtpServiceAdapter
{
    /**
     * @param Person $person
     * @param string $token
     *
     * @return bool
     */
    public function authenticate(Person $person, $token);
}
