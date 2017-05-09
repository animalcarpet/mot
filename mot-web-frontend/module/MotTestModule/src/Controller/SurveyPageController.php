<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Dvsa\Mot\Frontend\MotTestModule\Controller;

use Core\Controller\AbstractAuthActionController;
use Dvsa\Mot\Frontend\MotTestModule\Model\SurveyRating;
use Dvsa\Mot\Frontend\MotTestModule\Service\SurveyService;
use DvsaApplicationLogger\Log\Logger;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\HttpRestJson\Exception\GeneralRestException;
use OutOfBoundsException;
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
     * @var Logger
     */
    private $logger;

    /**
     * SurveyPageController constructor.
     *
     * @param SurveyService $surveyService
     * @param Logger        $logger
     */
    public function __construct(SurveyService $surveyService, Logger $logger)
    {
        $this->surveyService = $surveyService;
        $this->logger = $logger;
    }

    /**
     * @return array|ViewModel
     */
    public function indexAction()
    {
        // Token will be null unless set when event is triggered.
        $token = $this->params()->fromRoute(self::TOKEN_KEY);

        return $this->handleSurveyRequest($token);
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

        $token = $this->params()->fromRoute(self::TOKEN_KEY);

        $this->gtmDataLayer([
            'event' => 'submit-GDSsurvey',
            'token' => $token,
            'title' => 'GDS Survey - Submitted',
        ]);
        $this->layout('layout/layout-govuk.phtml');

        return $this->createViewModel('survey-page/thanks.phtml', []);
    }

    /**
     * @return ViewModel|array
     */
    public function reportsAction()
    {
        $this->assertGranted(PermissionInSystem::GENERATE_SATISFACTION_SURVEY_REPORT);

        $this->layout('layout/layout-govuk.phtml');
        try {
            $reports = $this->surveyService->getSurveyReports();
        } catch (GeneralRestException $e) {
            $this->logger->err(sprintf('[GDS Satisfaction Survey] Failed to get survey reports. GeneralRestException: "%s"',
                $e->getMessage()));
            $reports = [];
        }

        return $this->createViewModel('survey-reports/reports.twig', ['reports' => $reports]);
    }

    /**
     * @return Response|array
     */
    public function downloadReportCsvAction()
    {
        $this->assertGranted(PermissionInSystem::GENERATE_SATISFACTION_SURVEY_REPORT);

        $reportYear = $this->params()->fromRoute('year');
        $reportMonth = $this->params()->fromRoute('month');

        $csvData = $this->getCsvDataForYearAndMonth($reportYear, $reportMonth);
        if (!$csvData) {
            return $this->redirect()->toRoute('survey-reports');
        }

        $headers = (new Headers())->addHeaders([
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="'.$reportYear.'-'.$reportMonth.'.csv"',
            'Accept-Ranges' => 'bytes',
            'Cache-Control' => 'no-cache, no-store, max-age=0, must-revalidate',
            'Pragma' => 'no-cache',
        ]);

        $response = new Response();
        $response->setHeaders($headers);
        $response->sendHeaders();

        $csvHandle = fopen('php://output', 'w');
        fputs($csvHandle, $csvData);
        flush();
        fclose($csvHandle);

        return $response;
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

            $surveyData = ['token' => $token, 'satisfaction_rating' => $satisfactionRating];
            $this->surveyService->submitSurveyResult($surveyData);

            return $this->redirect()->toRoute('survey/thanks', ['token' => $token]);
        }

        if ($token == null) {
            return $this->redirect()->toRoute('login');
        }

        if (!$this->surveyService->isTokenValid($token)) {
            return $this->redirect()->toRoute('login');
        }

        if (true === $this->surveyService->hasBeenPresented($token)) {
            return $this->redirect()->toRoute('login');
        }

        $this->gtmDataLayer([
            'event' => 'view-GDSsurvey',
            'token' => $token,
            'title' => 'GDS Survey - Viewed',
        ]);

        $this->surveyService->markSurveyAsPresented($token);

        return $this->createViewModel(
            'survey-page/index.phtml', [
                'token' => $token,
                'ratings' => new SurveyRating(),
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
     * @param string $year
     * @param string $month
     *
     * @return string|null
     */
    private function getCsvDataForYearAndMonth($year, $month)
    {
        try {
            $reports = $this->surveyService->getSurveyReports();
        } catch (GeneralRestException $e) {
            $this->logger->err(sprintf('[GDS Satisfaction Survey] Failed to get survey reports. GeneralRestException: "%s"',
                $e->getMessage()));

            return null;
        }

        try {
            $report = $reports->getReport($year, $month);

            return $report->getCsvData();
        } catch (OutOfBoundsException $e) {
            return null;
        }
    }
}
