<?php
/**
 * This file is part of the DVSA MOT API project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace DvsaMotApi\Controller;

use DvsaCommonApi\Controller\AbstractDvsaRestfulController;
use DvsaCommonApi\Model\ApiResponse;
use DvsaCommonApi\Service\Exception\BadRequestException;
use DvsaCommonApi\Service\Exception\NotFoundException;
use DvsaMotApi\Service\SurveyService;
use Zend\Json\Json;
use Zend\View\Model\JsonModel;

/**
 * SurveyController handles GDS Satisfaction Survey related requests such as submitting a survey and generating reports.
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
     * Flag that a specific Survey has been presented to the user.
     */
    public function markSurveyAsPresentedAction()
    {
        $data = Json::decode($this->request->getContent(), Json::TYPE_ARRAY);

        if (!array_key_exists('token', $data)) {
            throw new BadRequestException('Survey token not provided', BadRequestException::ERROR_CODE_INVALID_SURVEY_TOKEN);
        }

        $this->surveyService->markSurveyAsPresented($data['token']);

        return ApiResponse::jsonOk();
    }

    /**
     * Has a Survey, identified by $token, been presented to the user?
     */
    public function hasBeenPresentedAction()
    {
        $data = Json::decode($this->request->getContent(), Json::TYPE_ARRAY);

        if (!array_key_exists('token', $data)) {
            throw new BadRequestException('Survey token not provided', BadRequestException::ERROR_CODE_INVALID_SURVEY_TOKEN);
        }

        try {
            $hasBeenPresented = $this->surveyService->hasBeenPresented($data['token']);

            return ApiResponse::jsonOk($hasBeenPresented);
        } catch (BadRequestException $e) {
        } catch (NotFoundException $e) {
        }

        return ApiResponse::jsonOk(true);
    }

    /**
     * @param array $data Contains token and survey rating.
     *
     * @return JsonModel|array
     */
    public function create($data)
    {
        $response = $this->surveyService->createSurveyResult($data);

        return ApiResponse::jsonOk($response);
    }

    /**
     * @return JsonModel|array
     */
    public function generateReportsAction()
    {
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
        $data = Json::decode($this->request->getContent(), Json::TYPE_ARRAY);
        if (!isset($data['motTestId']) || !isset($data['motTestTypeCode']) || !isset($data['testerId'])) {
            return ApiResponse::jsonOk(false);
        }

        return ApiResponse::jsonOk(
            $this->surveyService->shouldDisplaySurvey($data['motTestId'], $data['motTestTypeCode'], $data['testerId'])
        );
    }

    /**
     * @return JsonModel|array
     */
    public function createSessionTokenAction()
    {
        $data = Json::decode($this->request->getContent(), Json::TYPE_ARRAY);

        return ApiResponse::jsonOk($this->surveyService->createSessionToken($data['motTestNumber']));
    }

    /**
     * @return JsonModel|array
     */
    public function validateTokenAction()
    {
        $data = Json::decode($this->request->getContent(), Json::TYPE_ARRAY);
        if (!isset($data['token'])) {
            return ApiResponse::jsonError(false);
        }

        $result = $this->surveyService->sessionTokenIsValid($data['token']);

        return (true === $result) ? ApiResponse::jsonOk($result) : ApiResponse::jsonError($result);
    }
}
