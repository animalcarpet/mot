<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Dvsa\Mot\Frontend\MotTestModule\Controller;

use Core\Controller\AbstractAuthActionController;
use DateTime;
use Dvsa\Mot\Frontend\MotTestModule\Model\SurveyRating;
use Dvsa\Mot\Frontend\MotTestModule\Service\SurveyService;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Constants\FeatureToggle;
use Zend\Http\Headers;
use Zend\Http\PhpEnvironment\Response;
use Zend\View\Model\ViewModel;

/**
 * Class SurveyPageController.
 */
class SurveyPageController extends AbstractAuthActionController
{
    const SATISFACTION_RATING = 'satisfactionRating';
    const TOKEN_KEY = 'token';

    /**
     * @var SurveyService
     */
    private $surveyService;

    /**
     * @var array
     */
    private $reports;

    /**
     * @var
     */
    protected $csvHandle;

    /**
     * SurveyPageController constructor.
     *
     * @param SurveyService $surveyService
     */
    public function __construct(SurveyService $surveyService)
    {
        $this->surveyService = $surveyService;
    }

    /**
     * @return array|ViewModel
     */
    public function indexAction()
    {
        if (true !== $this->isFeatureEnabled(FeatureToggle::SURVEY_PAGE)) {
            return $this->notFoundAction();
        }

        // Token will be null unless set when event is triggered.
        $token = $this->params()->fromRoute(self::TOKEN_KEY);

        return $this->handleSurveyRequest($token);
    }

    /**
     * @return array|ViewModel
     */
    public function thanksAction()
    {
        if (true !== $this->isFeatureEnabled(FeatureToggle::SURVEY_PAGE)) {
            return $this->notFoundAction();
        }

        $ref = $_SERVER['HTTP_REFERER'];
        if (strpos($ref, 'survey') === false) {
            return $this->notFoundAction();
        }

        $this->layout('layout/layout-govuk.phtml');

        return $this->createViewModel('survey-page/thanks.phtml', []);
    }

    /**
     * @return ViewModel|array
     */
    public function reportsAction()
    {
        $this->assertGranted(PermissionInSystem::GENERATE_SATISFACTION_SURVEY_REPORT);
        if (!$this->isFeatureEnabled(FeatureToggle::SURVEY_PAGE)) {
            return $this->notFoundAction();
        }

        $this->layout('layout/layout-govuk.phtml');
        $this->reports = $this->surveyService->getSurveyReports();

        return $this->createViewModel('survey-reports/reports.phtml', ['reports' => $this->reports]);
    }

    /**
     * @return Response|array
     */
    public function downloadReportCsvAction()
    {
        $this->assertGranted(PermissionInSystem::GENERATE_SATISFACTION_SURVEY_REPORT);
        if (true !== $this->isFeatureEnabled(FeatureToggle::SURVEY_PAGE)) {
            return $this->notFoundAction();
        }

        $reportMonth = $this->params()->fromRoute('month');

        $headers = (new Headers())->addHeaders(
            [
                'Content-Type' => 'text/csv; charset=utf-8',
                'Content-Disposition' => 'attachment; filename="' . $reportMonth . '.csv"',
                'Accept-Ranges' => 'bytes',
                'Cache-Control' => 'no-cache, no-store, max-age=0, must-revalidate',
                'Pragma' => 'no-cache',
            ]
        );

        $this->response = new Response();
        $this->response->setHeaders($headers);
        $this->response->sendHeaders();

        $this->csvHandle = fopen('php://output', 'w');
        fputs($this->csvHandle, $this->getCsvDataForMonth($reportMonth));
        flush();
        fclose($this->csvHandle);

        return $this->response;
    }

    /**
     * @param string $token
     *
     * @return ViewModel|array
     */
    private function handleSurveyRequest($token)
    {
        $this->layout('layout/layout-govuk.phtml');

        if ($this->getRequest()->isPost()) {
            $satisfactionRating = $this->getRequest()->getPost(self::SATISFACTION_RATING);

            // Trying to submit a survey without a linked MOT test.
            if ($token == null) {
                return $this->notFoundAction();
            }

            $surveyData = [
                'token'               => $token,
                'satisfaction_rating' => $satisfactionRating,
            ];

            $this->surveyService->submitSurveyResult($surveyData);

            return $this->redirect()->toRoute('survey/thanks', ['token' => $token]);
        }

        if ($token == null) {
            return $this->redirect()->toRoute('login');
        }

        if (!$this->surveyService->isTokenValid($token)) {
            return $this->redirect()->toRoute('login');
        }

        return $this->createViewModel(
            'survey-page/index.phtml', [
                'token' => $token,
                'ratings' => new SurveyRating()
        ]);
    }

    /**
     * @param string $template
     * @param array  $variables
     *
     * @return ViewModel
     */
    private function createViewModel($template, array $variables)
    {
        $viewModel = new ViewModel();
        $viewModel->setTemplate($template);
        $viewModel->setVariables($variables);

        return $viewModel;
    }

    /**
     * @param $month
     *
     * @return string
     */
    private function getCsvDataForMonth($month)
    {
        $this->reports = $this->surveyService->getSurveyReports();

        if (empty($this->reports)) {
            return '';
        }

        foreach ($this->reports['data'] as $report) {
            if (strtolower($this->getMonthNameFromReportMonth($report['month'])) == strtolower($month)) {
                return $report['csv'];
            }
        }
    }

    /**
     * @param $reportMonth
     *
     * @return string
     */
    private function getMonthNameFromReportMonth($reportMonth)
    {
        $date = DateTime::createFromFormat('Y-m', $reportMonth);

        return $date->format('F');
    }
}
