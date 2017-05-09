<?php

namespace DvsaEntities\Repository;

use Doctrine\ORM\EntityRepository;
use DvsaCommonApi\Service\Exception\NotFoundException;
use DvsaEntities\Entity\OrganisationSiteMap;

/**
 * Repository for @see \DvsaEntities\Entity\OrganisationSiteMap.
 *
 * @codeCoverageIgnore
 */
class OrganisationSiteMapRepository extends EntityRepository
{
    const ERR_ORG_SITE_LINK_NOT_FOUND = 'Association between Authorised Examiner and Site';

    /**
     * @return OrganisationSiteMap
     *
     * @throws NotFoundException
     */
    public function get($id, $statusCode = null)
    {
        $qb = $this->createQueryBuilder('map')
            ->where('map.id = :ID')
            ->setParameter('ID', $id);

        if ($statusCode !== null) {
            $qb->join('map.status', 'st')
                ->andWhere('st.code = :STATUS_CODE')
                ->setParameter(':STATUS_CODE', $statusCode);
        }

        try {
            $map = $qb->getQuery()->getSingleResult();
        } catch (\Doctrine\ORM\NoResultException $e) {
            throw new NotFoundException(self::ERR_ORG_SITE_LINK_NOT_FOUND);
        }

        return $map;
    }

    public function getByOrgSiteAndStatus($orgId, $siteNumber, $statusCode = null)
    {
        $qb = $this
            ->createQueryBuilder('map')
            ->join('map.site', 's')
            ->where('map.organisation = :ORG_ID')
            ->andWhere('s.siteNumber = :SITE_NR')
            ->setParameter('ORG_ID', $orgId)
            ->setParameter('SITE_NR', $siteNumber)
            ->setMaxResults(1);

        if ($statusCode !== null) {
            $qb->join('map.status', 'st')
                ->andWhere('st.code = :STATUS_CODE')
                ->setParameter(':STATUS_CODE', $statusCode);
        }

        try {
            $map = $qb->getQuery()->getSingleResult();
        } catch (\Doctrine\ORM\NoResultException $e) {
            throw new NotFoundException(self::ERR_ORG_SITE_LINK_NOT_FOUND);
        }

        return $map;
    }
}
