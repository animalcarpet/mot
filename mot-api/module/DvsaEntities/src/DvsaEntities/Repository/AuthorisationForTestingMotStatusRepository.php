<?php

namespace DvsaEntities\Repository;

use DvsaEntities\Entity\AuthorisationForTestingMotStatus;

/**
 * Class AuthorisationForTestingMotStatusRepository.
 *
 * @codeCoverageIgnore
 */
class AuthorisationForTestingMotStatusRepository extends EnumType1Repository
{
    /**
     * @param string $code
     *
     * @return AuthorisationForTestingMotStatus
     */
    public function getByCode($code)
    {
        return parent::getByCode($code);
    }
}
