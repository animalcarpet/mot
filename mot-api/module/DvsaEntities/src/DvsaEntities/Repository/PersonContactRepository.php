<?php

namespace DvsaEntities\Repository;

use DvsaCommonApi\Service\Exception\NotFoundException;
use DvsaEntities\Entity\PersonContact;
use DvsaEntities\Entity\SiteContact;

/**
 * Class PersonContactRepository.
 *
 * Custom Doctrine Repository for reusable DQL queries
 *
 * @codeCoverageIgnore
 */
class PersonContactRepository extends AbstractMutableRepository
{
    /**
     * Get First founded site contact by specified type code in Site.
     *
     * @param int    $personId
     * @param string $typeCode
     *
     * @return PersonContact
     *
     * @throws NotFoundException
     */
    public function getHydratedByTypeCode($personId, $typeCode)
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder
            ->select('pc')
            ->addSelect('cd, cde, cdp, cda')
            ->from(PersonContact::class, 'pc')
            ->innerJoin('pc.type', 'pct')
            ->innerJoin('pc.contactDetail', 'cd')
            ->leftJoin('cd.emails', 'cde')
            ->leftJoin('cd.phones', 'cdp')
            ->leftJoin('cd.address', 'cda')
            ->where('pc.person = :personId')
            ->andWhere('pct.code = :typeCode')
            ->setParameter('personId', $personId)
            ->setParameter('typeCode', $typeCode);

        /** @var SiteContact $result */
        $result = $queryBuilder->getQuery()->getSingleResult();
        if (empty($result)) {
            throw new NotFoundException('PersonContact');
        }

        return $result;
    }
}
