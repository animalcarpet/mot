<?php

namespace DvsaEntities\Repository;

use Doctrine\ORM\EntityRepository;
use DvsaCommonApi\Service\Exception\NotFoundException;
use DvsaEntities\Entity\SiteRiskAssessment;
use DvsaEntities\Entity\Site;

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
}
