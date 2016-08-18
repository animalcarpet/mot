<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */
namespace Dvsa\Mot\Frontend\MotTestModule\Controller;

use Core\Controller\AbstractAuthActionController;
use Dvsa\Mot\Frontend\MotTestModule\Service\SurveyService;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Constants\FeatureToggle;
use DvsaCommon\Constants\MotTestNumberConstraint;
use Zend\Http\Headers;
use Zend\Http\PhpEnvironment\Response;
use Zend\View\Model\ViewModel;
use DateTime;

/**
 * Class SurveyPageController.
 */
class SurveyPageController extends AbstractAuthActionController
{
    /**
     * @var SurveyService
     */
    private $surveyService;

    /**
     * @var array
     */
    private $reports;

    const VERY_SATISFIED = 5;
    const SATISFIED = 4;
    const NEITHER_SATISFIED_NOR_DISSATISFIED = 3;
    const DISSATISFIED = 2;
    const VERY_DISSATISFIED = 1;

    const SATISFACTION_RATING = 'satisfactionRating';
    const MOT_TEST_NUMBER = 'motTestId';

    /**
     * @var
     */
    protected $csvHandle;

    /**
     * SurveyPageController constructor.
     *
     * @param SurveyService $surveyService
     */
    public function __construct(
        SurveyService $surveyService
    ) {
        $this->surveyService = $surveyService;
    }

    /**
     * @return array|\Zend\Http\Response|ViewModel
     */
    public function indexAction()
    {
        $ref = $_SERVER['HTTP_REFERER'];

        $noTestResult = strpos($ref, 'test-result') === false;
        $noSurvey = strpos($ref, 'survey') === false;

        if ($noTestResult && $noSurvey) {
            return $this->notFoundAction();
        }
        if (true !== $this->isFeatureEnabled(FeatureToggle::SURVEY_PAGE)) {
            return $this->notFoundAction();
        }

        $this->layout('layout/layout-govuk.phtml');

        if ($this->getRequest()->isPost()) {
            $satisfactionRating = $this->getRequest()->getPost(self::SATISFACTION_RATING);
            $motTestNumber = $this->getRequest()->getPost(self::MOT_TEST_NUMBER);

            $surveyData = [
                'mot_test_number'     => $motTestNumber,
                'satisfaction_rating' => $satisfactionRating,
            ];

            $this->surveyService->submitSurveyResult(
                $surveyData
            );

            if (is_null($satisfactionRating)) {
                return $this->redirect()->toUrl('/');
            } else {
                return $this->redirect()->toRoute('survey/thanks');
            }
        }

        $matches = [];
        preg_match('/'.MotTestNumberConstraint::FORMAT_REGEX.'/', $ref, $matches);
        $motTestId = $matches[0];

        return $this->createViewModel(
            'survey-page/index.phtml',
            ['motTestId' => $motTestId]
        );
    }

    /**
     * @return array|ViewModel
     */
    public function thanksAction()
    {
        $ref = $_SERVER['HTTP_REFERER'];
        if (strpos($ref, 'survey') === false) {
            return $this->notFoundAction();
        }

        if (!$this->isFeatureEnabled(FeatureToggle::SURVEY_PAGE)) {
            return $this->notFoundAction();
        }

        $this->layout('layout/layout-govuk.phtml');

        return $this->createViewModel(
            'survey-page/thanks.phtml',
            []
        );
    }

    /**
     * @return ViewModel
     */
    public function reportsAction()
    {
        $this->assertGranted(PermissionInSystem::GENERATE_SATISFACTION_SURVEY_REPORT);
        if (!$this->isFeatureEnabled(FeatureToggle::SURVEY_PAGE)) {
            return $this->notFoundAction();
        }

        $this->layout('layout/layout-govuk.phtml');

        $this->reports = $this->surveyService->getSurveyReports();

        return $this->createViewModel(
            'survey-reports/reports.phtml',
            [
                'reports' => $this->reports,
            ]
        );
    }

    /**
     * @return Response
     */
    public function downloadReportCsvAction()
    {
        $this->assertGranted(PermissionInSystem::GENERATE_SATISFACTION_SURVEY_REPORT);
        if (!$this->isFeatureEnabled(FeatureToggle::SURVEY_PAGE)) {
            return $this->notFoundAction();
        }

        $reportMonth = $this->params()->fromRoute('month');

        $headers = (new Headers())->addHeaders(
            [
                'Content-Type' => 'text/csv; charset=utf-8',
                'Content-Disposition' => 'attachment; filename="'.$reportMonth.'.csv"',
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

    private function getMonthNameFromReportMonth($reportMonth)
    {
        $date = DateTime::createFromFormat('Y-m', $reportMonth);

        return $date->format('F');
    }
}
