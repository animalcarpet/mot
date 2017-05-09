<?php

namespace DvsaEntities\Repository;

use DvsaCommon\Enum\AuthorisationForTestingMotStatusCode;
use DvsaEntities\Entity\AuthorisationForTestingMotStatus;
use DvsaEntities\Entity\Person;

/**
 * Class AuthorisationForTestingMotRepository.
 *
 * @codeCoverageIgnore
 */
class AuthorisationForTestingMotRepository extends AbstractMutableRepository
{
    public function suspendQualifiedAuthorisationsForPerson(Person $person)
    {
        $suspendedStatus = $this->getEntityManager()->getRepository(AuthorisationForTestingMotStatus::class)
            ->findOneBy(['code' => AuthorisationForTestingMotStatusCode::SUSPENDED]);
        $qualifiedStatus = $this->getEntityManager()->getRepository(AuthorisationForTestingMotStatus::class)
            ->findOneBy(['code' => AuthorisationForTestingMotStatusCode::QUALIFIED]);
        $qualifiedAuthorisations = $this->findBy(['person' => $person, 'status' => $qualifiedStatus]);
        foreach ($qualifiedAuthorisations as $auth) {
            $auth->setStatus($suspendedStatus);
            $this->getEntityManager()->persist($auth);
        }
    }

    public function activateSuspendedAuthorisationsForPerson(Person $person)
    {
        $suspendedStatus = $this->getEntityManager()->getRepository(AuthorisationForTestingMotStatus::class)
            ->findOneBy(['code' => AuthorisationForTestingMotStatusCode::SUSPENDED]);
        $qualifiedStatus = $this->getEntityManager()->getRepository(AuthorisationForTestingMotStatus::class)
            ->findOneBy(['code' => AuthorisationForTestingMotStatusCode::QUALIFIED]);
        $qualifiedAuthorisations = $this->findBy(['person' => $person, 'status' => $suspendedStatus]);
        foreach ($qualifiedAuthorisations as $auth) {
            $auth->setStatus($qualifiedStatus);
            $this->getEntityManager()->persist($auth);
        }
    }
}
