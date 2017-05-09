<?php

namespace Site\ViewModel\Sidebar;

use Core\ViewModel\Sidebar\GeneralSidebarStatusItem;
use Core\ViewModel\Badge\Badge;
use Site\Service\RiskAssessmentScoreRagClassifier;

class VtsOverviewRagStatus extends GeneralSidebarStatusItem
{
    private $score;

    public function __construct(RiskAssessmentScoreRagClassifier $classifier)
    {
        $this->score = $classifier->getScore();

        parent::__construct('risk-assessment-score', 'Risk assessment ', $classifier->getRagScore(), $this->badgeFromRagClassifier($classifier));
    }

    public function getSecondaryText()
    {
        return 'Score: '.$this->score;
    }

    public function escapeSecondaryText()
    {
        return false;
    }

    private function badgeFromRagClassifier(RiskAssessmentScoreRagClassifier $ragClassifier)
    {
        $ragStatus = $ragClassifier->getRagScore();

        switch ($ragStatus) {
            case RiskAssessmentScoreRagClassifier::WHITE_STATUS:
                return Badge::normal();
            case RiskAssessmentScoreRagClassifier::RED_STATUS:
                return Badge::alert();
            case RiskAssessmentScoreRagClassifier::AMBER_STATUS:
                return Badge::warning();
            case RiskAssessmentScoreRagClassifier::GREEN_STATUS:
                return Badge::success();
        }

        throw new \Exception("Unknown rag status: '$ragStatus' value.");
    }
}
