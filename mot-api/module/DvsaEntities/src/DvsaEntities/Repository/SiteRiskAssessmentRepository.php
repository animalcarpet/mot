<?php

namespace DvsaEntities\Repository;

use Doctrine\ORM\EntityRepository;
use SiteApi\Service\SiteRiskScoreExtractor;
use DvsaCommonApi\Service\Exception\NotFoundException;
use DvsaEntities\Entity\EnforcementSiteAssessment;
use DvsaEntities\Entity\OrganisationSiteMap;

/**
 * Risk assessment repository.
 */
class SiteRiskAssessmentRepository extends EntityRepository
{
    /**
     * Gets all risk assessments for a VTS linked to AE
     * @param int $siteId
     * @param int $organisationId
     * @return EnforcementSiteAssessment[]
     */
    public function getAssessmentForSiteAtOrganisation(int $siteId, int $organisationId):array
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();

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

        $queryBuilder
            ->select('a')
            ->from(EnforcementSiteAssessment::class, 'a')
            ->where('a.site = :siteId')
            ->andWhere('a.aeOrganisationId = :organisationId')
            ->andWhere('a.visitDate >= (' . $maxAssessmentDate->getDQL() .')')
            ->setParameter('siteId', $siteId)
            ->setParameter('organisationId', $organisationId)
            ->orderBy('a.visitDate', 'ASC')
            ->addOrderBy('a.id', 'ASC');

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * Gets all risk assessments for a VTS, even if it's unlinked from AE
     * @param int $siteId
     * @param int $organisationId
     * @return \DvsaEntities\Entity\EnforcementSiteAssessment[]
     * @throws NotFoundException
     */
    public function getAllAssessmentsForSite(int $siteId, int $organisationId = null):array
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

         $queryBuilder = $this->createQueryBuilder('a')
             ->innerJoin('a.site', 's')
             ->where('a.aeOrganisationId = s.organisation')
             ->andWhere('a.visitDate >= (' . $maxAssessmentDate->getDQL() .')')
             ->orWhere('s.organisation is NULL AND a.aeOrganisationId = :organisationId')
             ->andWhere('a.site = :siteId')
             ->addOrderBy('a.visitDate', 'ASC')
             ->addOrderBy('a.id', 'ASC')
             ->setParameters(['organisationId' => $organisationId, 'siteId' => $siteId])
             ;
         try {
             return $queryBuilder->getQuery()->getResult();
         } catch (\Exception $e) {
             throw new NotFoundException('No assessments found for site '.$siteId);
         }
    }

    /**
     * @param int $siteId
     * @param int $organisationId ID of the last owner of the site
     * @return EnforcementSiteAssessment[]
     * @throws NotFoundException
     */
    public function getLastAssessmentsForSite($siteId, $organisationId)
    {
        return SiteRiskScoreExtractor::getLast2SiteAssessments($this->getAllAssessmentsForSite($siteId, $organisationId));
    }

    /**
     * Gets last assessments for multiple sites
     * @param array $sites
     * @param int $aeId
     * @return array
     */
    public function getLastAssessmentsForMultipleSites(array $sites, int $aeId):array
    {
        $out = [];
        foreach ($sites as $site) {
            $assessments = $this->getAssessmentForSiteAtOrganisation($site['id'], $aeId);
            $site['previous_assessment'] = SiteRiskScoreExtractor::getPreviousAssessment($assessments);
            $site['current_assessment'] = SiteRiskScoreExtractor::getCurrentAssessment($assessments);
            $out[] = $site;
        }

        return $out;
    }
}
