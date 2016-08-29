<?php

namespace DvsaMotApi\Controller;

use DvsaCommon\Constants\FeatureToggle;
use DvsaCommonApi\Controller\AbstractDvsaRestfulController;
use DvsaCommonApi\Model\ApiResponse;
use DvsaMotApi\Service\SurveyService;
use Zend\Json\Json;
use Zend\View\Model\JsonModel;

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
     * @param array $data Contains token and survey rating.
     *
     * @return JsonModel|array
     */
    public function create($data)
    {
        if (true !== $this->isFeatureEnabled(FeatureToggle::SURVEY_PAGE)) {
            return $this->notFoundAction();
        }

        $response = $this->surveyService->createSurveyResult($data);

        return ApiResponse::jsonOk($response);
    }

    /**
     * @return JsonModel|array
     */
    public function generateReportsAction()
    {
        if (true !== $this->isFeatureEnabled(FeatureToggle::SURVEY_PAGE)) {
            return $this->notFoundAction();
        }

        $ratingCounts = [];
        $total = 0;

        foreach (range(1, 5) as $rating) {
            $count = count($this->surveyService->getSurveyResultSatisfactionRatingsCount($rating));
            $total += $count;
            $ratingCounts['rating_' . $rating] = $count;
        }

        $ratingCounts['total'] = $total;

        $this->surveyService->generateSurveyReports($ratingCounts);

        return ApiResponse::jsonOk($ratingCounts);
    }

    /**
     * @return JsonModel|array
     */
    public function getReportsAction()
    {
        if (true !== $this->isFeatureEnabled(FeatureToggle::SURVEY_PAGE)) {
            return $this->notFoundAction();
        }

        $response = $this->surveyService->getSurveyReports();

        return ApiResponse::jsonOk(
            $response
        );
    }

    /**
     * Returns whether or not a survey should be displayed.
     *
     * @return JsonModel|array
     */
    public function shouldDisplaySurveyAction()
    {
        if (true !== $this->isFeatureEnabled(FeatureToggle::SURVEY_PAGE)) {
            return $this->notFoundAction();
        }

        $data = Json::decode($this->request->getContent(), Json::TYPE_ARRAY);
        if (!isset($data['motTestTypeCode']) || !isset($data['testerId'])) {
            return ApiResponse::jsonOk(false);
        }

        return ApiResponse::jsonOk(
            $this->surveyService->shouldDisplaySurvey($data['motTestTypeCode'], $data['testerId'])
        );
    }

    /**
     * @return JsonModel|array
     */
    public function createSessionTokenAction()
    {
        if (true !== $this->isFeatureEnabled(FeatureToggle::SURVEY_PAGE)) {
            return $this->notFoundAction();
        }

        $data = Json::decode($this->request->getContent(), Json::TYPE_ARRAY);

        return ApiResponse::jsonOk($this->surveyService->createSessionToken($data['motTestNumber']));
    }

    /**
     * @return JsonModel|array
     */
    public function validateTokenAction()
    {
        if (true !== $this->isFeatureEnabled(FeatureToggle::SURVEY_PAGE)) {
            return $this->notFoundAction();
        }

        $data = Json::decode($this->request->getContent(), Json::TYPE_ARRAY);
        if (!isset($data['token'])) {
            return ApiResponse::jsonError(false);
        }

        $result = $this->surveyService->sessionTokenIsValid($data['token']);

        return (true === $result) ? ApiResponse::jsonOk($result) : ApiResponse::jsonError($result);
    }
}
