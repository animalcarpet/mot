<?php

namespace DvsaEntities\Repository;

use Doctrine\ORM\EntityRepository;
use DvsaCommonApi\Service\Exception\NotFoundException;
use DvsaEntities\Entity\OrganisationSiteMap;

/**
 * Risk assessment repository.
 */
class SiteRiskAssessmentRepository extends EntityRepository
{
    public function getAssessmentForSite($siteId)
    {
        $query = $this->getEntityManager()->createQuery(
            'SELECT assessment
             FROM ' .SiteRiskAssessment::class.' assessment
             JOIN assessment.site s
             WHERE s.id = :siteId
             ORDER BY assessment.id DESC'
        )->setMaxResults(1)
         ->setParameter('siteId', $siteId);

        try {
            return $query->getSingleResult();
        } catch (\Exception $e) {
            throw new NotFoundException('No assessments found for site '.$siteId);
        }
    }

    /**
     * @param $siteId
     * @param $organisationId ID of the last owner of the site
     * @param $limit number of last site assessments to return
     * @return array
     * @throws NotFoundException
     */
    public function getLatestAssessmentsForSite($siteId, $organisationId, $limit)
    {
        $maxAssessmentDateForSameSiteDifferentOrganisation = $this
            ->getEntityManager()
            ->createQueryBuilder()
            ->select('COALESCE(MAX(osm2.startDate), 0)')
            ->from(OrganisationSiteMap::class, "osm2")
            ->andWhere("osm2.site = :siteId")
            ->andWhere("osm2.organisation <> :organisationId");

        $maxAssessmentDate = $this
            ->getEntityManager()
            ->createQueryBuilder()
            ->select('DATE(MIN(osm.startDate))')
            ->from(OrganisationSiteMap::class, "osm")
            ->andWhere("osm.site = :siteId")
            ->andWhere("osm.organisation = :organisationId")
            ->andWhere('osm.endDate IS NULL OR 
                osm.startDate > (' . $maxAssessmentDateForSameSiteDifferentOrganisation->getDQL() . ' )');

        $queryBuilder = $this->createQueryBuilder('a') // assessment
            ->innerJoin('a.site', 's')
            ->where('a.aeOrganisationId = s.organisation')
            ->andWhere('a.visitDate >= (' . $maxAssessmentDate->getDQL() .')')
            ->orWhere('s.organisation is NULL AND 
                a.aeOrganisationId = :organisationId')
            ->andWhere('a.site = :siteId')
            ->addOrderBy('a.visitDate', 'DESC')
            ->addOrderBy('a.id', 'DESC')
            ->setParameters(['organisationId' => $organisationId, 'siteId' => $siteId])
            ->setMaxResults($limit);
        try {
            return $queryBuilder->getQuery()->getResult();
        } catch (\Exception $e) {
            throw new NotFoundException('No assessments found for site '.$siteId);
        }

    }
}
