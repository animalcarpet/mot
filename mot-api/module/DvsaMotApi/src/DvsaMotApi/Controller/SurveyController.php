<?php

namespace DvsaMotApi\Controller;

use DvsaCommon\Constants\FeatureToggle;
use DvsaCommonApi\Controller\AbstractDvsaRestfulController;
use DvsaCommonApi\Model\ApiResponse;
use DvsaMotApi\Service\SurveyService;
use Zend\Json\Json;

/**
 * Class SurveyController.
 */
class SurveyController extends AbstractDvsaRestfulController
{
    /**
     * @var SurveyService
     */
    private $surveyService;

    /**
     * SurveyController constructor.
     *
     * @param SurveyService $surveyService
     */
    public function __construct(SurveyService $surveyService)
    {
        $this->surveyService = $surveyService;
    }

    /**
     * @param mixed $data
     *
     * @return \Zend\View\Model\JsonModel
     */
    public function create($data)
    {
        if (!$this->isFeatureEnabled(FeatureToggle::SURVEY_PAGE)) {
            return $this->notFoundAction();
        }

        $response = $this->surveyService->createSurveyResult($data);

        return ApiResponse::jsonOk($response);
    }
    
    public function generateReportsAction()
    {
        if (!$this->isFeatureEnabled(FeatureToggle::SURVEY_PAGE)) {
            return $this->notFoundAction();
        }

        $ratingCounts = [];
        $total = 0;

        foreach (range(1, 5) as $rating) {
            $count = count(
                $this->surveyService->getSurveyResultSatisfactionRatingsCount($rating)
            );
            $total += $count;
            $ratingCounts['rating_'.$rating] = $count;
        }

        $ratingCounts['total'] = $total;
        
        $this->surveyService->generateSurveyReports($ratingCounts);

        return ApiResponse::jsonOk(
            $ratingCounts
        );
    }

    public function getReportsAction()
    {
        if (!$this->isFeatureEnabled(FeatureToggle::SURVEY_PAGE)) {
            return $this->notFoundAction();
        }

        $response = $this->surveyService->getSurveyReports();

        return ApiResponse::jsonOk(
            $response
        );
    }

    /**
     * @return bool
     */
    public function shouldDisplaySurveyAction()
    {
        if (!$this->isFeatureEnabled(FeatureToggle::SURVEY_PAGE)) {
            return $this->notFoundAction();
        }

        $data = Json::decode($this->request->getContent());

        $motTestDetails = $data->motTestDetails;
        $surveyIsEnabled = $data->surveyEnabled;

        return ApiResponse::jsonOk(
            $this->surveyService->shouldDisplaySurvey($motTestDetails, $surveyIsEnabled)
        );
    }
}
