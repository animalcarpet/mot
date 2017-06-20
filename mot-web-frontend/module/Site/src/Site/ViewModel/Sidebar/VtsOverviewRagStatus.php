<?php

namespace Site\ViewModel\Sidebar;

use Core\ViewModel\Sidebar\GeneralSidebarStatusItem;
use Core\ViewModel\Badge\Badge;
use Site\Service\RiskAssessmentScoreRagClassifier;

class VtsOverviewRagStatus extends GeneralSidebarStatusItem
{
    private $score;
    private $assessmentDate;

    public function __construct(RiskAssessmentScoreRagClassifier $classifier, $assessmentDate)
    {
        $this->score = $classifier->getScore();
        $this->assessmentDate = $assessmentDate;

        parent::__construct('risk-assessment-score', 'Site assessment ', $classifier->getRagScore(), $this->badgeFromRagClassifier($classifier));
    }

    public function getSecondaryText()
    {
        return 'Score: '.$this->score;
    }

    public function escapeSecondaryText()
    {
        return false;
    }

    public function getTertiaryText()
    {
        return $this->assessmentDate ? 'Date: '. $this->assessmentDate : null;
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
