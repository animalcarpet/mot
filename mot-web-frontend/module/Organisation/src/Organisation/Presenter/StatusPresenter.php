<?php

namespace Organisation\Presenter;

use Core\ViewModel\Sidebar\SidebarBadge;
use Site\Service\RiskAssessmentScoreRagClassifier;

class StatusPresenter
{

    /**
     * @param string $ragScore
     * @return StatusPresenterData
     */
    public function getStatusFields($ragScore)
    {
        switch ($ragScore) {
            case RiskAssessmentScoreRagClassifier::RED_STATUS:
                return new StatusPresenterData(RiskAssessmentScoreRagClassifier::RED_STATUS, SidebarBadge::alert());
            case RiskAssessmentScoreRagClassifier::AMBER_STATUS:
                return new StatusPresenterData(RiskAssessmentScoreRagClassifier::AMBER_STATUS, SidebarBadge::warning());
            case RiskAssessmentScoreRagClassifier::GREEN_STATUS:
                return new StatusPresenterData(RiskAssessmentScoreRagClassifier::GREEN_STATUS, SidebarBadge::success());
            default :
                return new StatusPresenterData(RiskAssessmentScoreRagClassifier::WHITE_STATUS, SidebarBadge::normal());
        }
    }
}