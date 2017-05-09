<?php

namespace DvsaEntities\Repository;

use DvsaCommonApi\Service\Exception\NotFoundException;
use DvsaEntities\Entity\ContactDetail;
use DvsaEntities\Entity\SiteContact;

/**
 * Class SiteContactRepository.
 *
 * Custom Doctrine Repository for reusable DQL queries
 *
 * @codeCoverageIgnore
 */
class SiteContactRepository extends AbstractMutableRepository
{
    /**
     * Get First founded site contact by specified type code in Site.
     *
     * @param int    $siteId
     * @param string $typeCode
     *
     * @return SiteContact
     *
     * @throws NotFoundException
     */
    public function getHydratedByTypeCode($siteId, $typeCode)
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder
            ->select('sc')
            ->addSelect('cd, cde, cdp, cda')
            ->from(SiteContact::class, 'sc')
            ->innerJoin('sc.type', 'sct')
            ->innerJoin('sc.contactDetail', 'cd')
            ->leftJoin('cd.emails', 'cde')
            ->leftJoin('cd.phones', 'cdp')
            ->leftJoin('cd.address', 'cda')
            ->where('sc.site = :SITE_ID')
            ->andWhere('sct.code = :TYPE_CODE')
            ->setParameter('SITE_ID', $siteId)
            ->setParameter('TYPE_CODE', $typeCode)
            ->setMaxResults(1);

        /** @var SiteContact $result */
        $result = $queryBuilder->getQuery()->getSingleResult();
        if (empty($result)) {
            throw new NotFoundException('SiteContact');
        }

        return $result;
    }
}
