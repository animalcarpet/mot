<?php

namespace DvsaEntities\Repository;

use DvsaEntities\Entity\AuthorisationForTestingMotAtSite;

/**
 * Class AuthorisationForTestingMotAtSiteRepository.
 *
 * @codeCoverageIgnore
 */
class AuthorisationForTestingMotAtSiteRepository extends AbstractMutableRepository
{
    /**
     * @param AuthorisationForTestingMotAtSite $entity
     *
     * @return AuthorisationForTestingMotAtSite
     */
    public function persist($entity)
    {
        /** @var AuthorisationForTestingMotAtSite $result */
        $result = $this->findOneBy(
            [
                'site' => $entity->getSite()->getId(),
                'vehicleClass' => $entity->getVehicleClass()->getId(),
            ]
        );

        if ($result) {
            $ret = $result->setStatus($entity->getStatus());
        } else {
            $this->getEntityManager()->persist($entity);
            $ret = $entity;
        }

        return $ret;
    }
}
