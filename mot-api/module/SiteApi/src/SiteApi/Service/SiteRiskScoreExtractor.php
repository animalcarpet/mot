<?php

namespace SiteApi\Service;

use DvsaCommon\Utility\TypeCheck;
use DvsaEntities\Entity\EnforcementSiteAssessment;

class SiteRiskScoreExtractor
{
    const PREVIOUS_ASSESSMENT_INDEX = 0;
    const CURRENT_ASSESSMENT_INDEX = 1;
    const ASSESSMENTS_AMOUNT = 2;

    /**
     * Gets last two site assessments from assessments ordered by visit ascending date
     * @param EnforcementSiteAssessment[] $assessments
     * @return EnforcementSiteAssessment[]
     */
    public static function getLast2SiteAssessments($assessments)
    {
        TypeCheck::assertCollectionOfClass($assessments, EnforcementSiteAssessment::class);

        return [
            self::getCurrentAssessment($assessments),
            self::getPreviousAssessment($assessments),
        ];
    }


    /**
     * @param EnforcementSiteAssessment[] $assessments
     * @return EnforcementSiteAssessment[]
     */
    private static function extractLastTwoAssessments(array $assessments)
    {
        $out = [];
        foreach ($assessments as $assessment) {
            if($assessment){
                $out[$assessment->getVisitDate()->format(\DateTime::ATOM)] = $assessment;
            }
        }

        ksort($out);

        return array_slice(array_values($out), -self::ASSESSMENTS_AMOUNT, self::ASSESSMENTS_AMOUNT);
    }

    /**
     * @param EnforcementSiteAssessment[] $assessments
     * @return EnforcementSiteAssessment
     */
    public static function getPreviousAssessment(array $assessments)
    {
        TypeCheck::assertCollectionOfClass($assessments, EnforcementSiteAssessment::class);
        $lastTwoAssessments = self::extractLastTwoAssessments($assessments);

        if (count($lastTwoAssessments) >= 2) {
            return $lastTwoAssessments[self::PREVIOUS_ASSESSMENT_INDEX] ?? null;
        }

        return null;
    }

    /**
     * @param EnforcementSiteAssessment[] $assessments
     * @return EnforcementSiteAssessment
     */
    public static function getCurrentAssessment(array $assessments)
    {
        TypeCheck::assertCollectionOfClass($assessments, EnforcementSiteAssessment::class);
        $lastTwoAssessments = self::extractLastTwoAssessments($assessments);

        if (count($lastTwoAssessments) >= 2) {
            return $lastTwoAssessments[self::CURRENT_ASSESSMENT_INDEX] ?? null;
        } else if (count($lastTwoAssessments) == self::CURRENT_ASSESSMENT_INDEX) {
            return $lastTwoAssessments[self::PREVIOUS_ASSESSMENT_INDEX] ?? null;
        }

        return null;
    }
}