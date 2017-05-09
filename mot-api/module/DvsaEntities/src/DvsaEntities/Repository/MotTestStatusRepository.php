<?php

namespace DvsaEntities\Repository;

use Doctrine\ORM\EntityRepository;
use DvsaCommon\Enum\MotTestStatusName;
use DvsaEntities\Entity\MotTestStatus;

/**
 * Retrieves MotTestStatus.
 */
class MotTestStatusRepository extends EntityRepository
{
    /**
     * @param string $name
     *
     * @return MotTestStatus
     */
    public function findByName($name)
    {
        $qb = $this->createQueryBuilder('mts');
        $qb
            ->where('mts.name LIKE :name')
            ->setParameter('name', $name);

        return $qb->getQuery()->getSingleResult();
    }

    /**
     * @return MotTestStatus
     */
    public function findActive()
    {
        return $this->findByName(MotTestStatusName::ACTIVE);
    }
}
