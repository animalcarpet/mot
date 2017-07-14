<?php

namespace Organisation\ViewModel\TestQualityInformation;

use Core\BackLink\BackLinkQueryParam;
use Core\Formatting\RiskScoreAssessmentFormatter;
use Core\Routing\VtsRouteList;
use DvsaCommon\ApiClient\Statistics\AePerformance\Dto\AuthorisedExaminerSitesPerformanceDto;
use DvsaCommon\ApiClient\Statistics\AePerformance\Dto\RiskAssessmentDto;
use DvsaCommon\Date\DateTimeDisplayFormat;
use DvsaCommon\Dto\Search\SearchParamsDto;
use Organisation\Presenter\StatusPresenter;
use Organisation\Presenter\UrlPresenterData;
use Report\Table\Formatter\MultiRow;
use Report\Table\Formatter\Status;
use Report\Table\Formatter\SubRow;
use Report\Table\Formatter\UrlPresenterLinkWithParams;
use Site\Service\RiskAssessmentScoreRagClassifier;
use Report\Table\Table;

class TestQualityInformationViewModel
{
    const TABLE_PAGINATION_FOOTER = 'table/gds-footer';
    const VEHICLE_TESTING_STATION = 'Vehicle testing station';
    const SITE_ID = ' Site ID';
    const SITE_ASSESSMENT = 'Site assessment';
    const TEST_QUALITY_INFORMATION = 'Test quality information';
    const VTS_NAME = 'Vts name';
    const VTS_NUMBER = 'Vts number';
    const VTS_ID = 'Vts ID';
    const VTS_PREV_ASSESSMENT = 'Previous assessment';
    const VTS_PREV_STATUS = 'Vts previous Status';
    const VTS_CURRENT_ASSESSMENT = 'Current assessment';
    const VTS_CURRENT_STATUS = 'Vts current Status';
    const VTS_STATUS_CLASS = 'Vts Status class';
    const VTS_ADDRESS = 'Vts Address';
    const VTS_TEST_QUALITY_INFORMATION_LINK = 'Vts test quality information link';
    const VTS_TEST_QUALITY_INFORMATION_LINK_TEXT = 'Test quality information';
    const VTS_TEST_LOGS_LINK = 'Test logs link';
    const VTS_TEST_LOGS_LINK_TEXT = 'Test logs';
    const VTS_TESTERS_ANNUAL_ASSESSMENTS_LINK = 'Testers annual assessments link';
    const VTS_TESTERS_ANNUAL_ASSESSMENTS_LINK_TEXT = 'Testers annual assessments';

    const NUMERIC_CLASS = 'numeric';
    const TABULAR_CLASS = 'tabular';
    const TABLE_CLASS = 'result-table _result-table--controls';

    const SCORE = "Score: %d";
    const DATE = "Date: %s";
    const TEST_QUALITY_INFORMATION_LINK_ID = "TQI_%d";
    const TEST_TEST_LOGS_LINK_ID = "test_logs_%d";

    const BACK_TO_SERVICE_REPORT_QUERY_PARAM = "serviceReports";
    const TESTERS_ANNUAL_ASSESSMENTS_LINK_ID = "TAA_%d";

    private $returnLink;
    /** @var Table */
    private $table;

    /** @var RiskAssessmentScoreRagClassifier $ragClassifier */
    private $ragClassifier;
    private $siteCount;

    /**
     * @param AuthorisedExaminerSitesPerformanceDto $authorisedExaminerSitePerformanceDto
     * @param   string $returnLink
     * @param RiskAssessmentScoreRagClassifier $ragClassifier
     * @param SearchParamsDto $searchParams
     * @param $siteCount
     */
    public function __construct(
        AuthorisedExaminerSitesPerformanceDto $authorisedExaminerSitePerformanceDto,
        $returnLink,
        RiskAssessmentScoreRagClassifier $ragClassifier,
        SearchParamsDto $searchParams,
        $siteCount
    ) {
        $this->returnLink = $returnLink;
        $this->ragClassifier = $ragClassifier;
        $this->setTable($authorisedExaminerSitePerformanceDto, $searchParams);
        $this->siteCount = (int) $siteCount;
    }

    public function getReturnLink()
    {
        return $this->returnLink;
    }

    /**
     * @param RiskAssessmentDto|null $riskAssessmentDto
     * @return float
     */
    private function getCurrentRagScore($riskAssessmentDto) {
        return $riskAssessmentDto !== null ? $riskAssessmentDto->getScore() : 0.00;
    }

    /**
     * @param RiskAssessmentDto|null $riskAssessmentDto
     * @return \DateTime|null
     */
    private function getRagDate($riskAssessmentDto) {
        return $riskAssessmentDto !== null ? $riskAssessmentDto->getDate() : null;
    }

    /**
     * @param RiskAssessmentDto|null $previousRiskAssessmentDto
     * @param RiskAssessmentDto|null $currentRiskAssessmentDto
     * @return float|null
     */
    private function getPreviousRagScore($previousRiskAssessmentDto, $currentRiskAssessmentDto) {
        if ($previousRiskAssessmentDto !== null) {
            return $previousRiskAssessmentDto->getScore();
        } else if ($currentRiskAssessmentDto !== null) {
            return 0.00;
        }
        return null;
    }

    /**
     * @param $ragScore
     * @param \DateTime|null $ragDate
     * @return array
     */
    private function getAssessmentDescription($ragScore, $ragDate) {
        $description = [];
        if (isset($ragScore)) {
            $description[] = sprintf(self::SCORE, (int)RiskScoreAssessmentFormatter::formatRiskScore($ragScore));
        }
        if (isset($ragDate)) {
            $description[] = sprintf(self::DATE, $ragDate->format(DateTimeDisplayFormat::FORMAT_DATE_SHORT));
        }
        return $description;
    }

    /**
     * @param  AuthorisedExaminerSitesPerformanceDto  $authorisedExaminerSitePerformanceDto
     */
    private function setTable(AuthorisedExaminerSitesPerformanceDto $authorisedExaminerSitePerformanceDto, SearchParamsDto $searchParams)
    {
        $rows = [];
        foreach ($authorisedExaminerSitePerformanceDto->getSites() as $site) {
            $currentRagScore = $this->getCurrentRagScore($site->getCurrentRiskAssessment());
            $previousRagScore = $this->getPreviousRagScore($site->getPreviousRiskAssessment() ,$site->getCurrentRiskAssessment());

            $tqiUrl = new UrlPresenterData(
                self::VTS_TEST_QUALITY_INFORMATION_LINK_TEXT,
                VtsRouteList::VTS_TEST_QUALITY,
                ['id' => $site->getId()],
                ['query' => ['returnToAETQI' => true],
                ],
                sprintf(self::TEST_QUALITY_INFORMATION_LINK_ID, $site->getId())
            );

            $testLogsUrl = new UrlPresenterData(
                self::VTS_TEST_LOGS_LINK_TEXT,
                VtsRouteList::VTS_TEST_LOGS,
                ['id' => $site->getId()],
                ['query' => ['backTo' => "serviceReports"],
                ],
                sprintf(self::TEST_TEST_LOGS_LINK_ID, $site->getId())
            );

            $annualAssessmentsUrl = new UrlPresenterData(
                self::VTS_TESTERS_ANNUAL_ASSESSMENTS_LINK_TEXT,
                VtsRouteList::VTS_TESTERS_ANNUAL_ASSESSMENT,
                ['id' => $site->getId()],
                ['query' => ['backTo' => BackLinkQueryParam::SERVICE_REPORTS]],
                sprintf(self::TESTERS_ANNUAL_ASSESSMENTS_LINK_ID, $site->getId())
            );

            $rows[] =
                [
                    self::VTS_NAME => $site->getName(),
                    self::VTS_ADDRESS => $site->getAddress()->getFullAddressString(),
                    self::VTS_NUMBER => $site->getNumber(),
                    self::VTS_ID => $site->getId(),
                    self::VTS_CURRENT_ASSESSMENT => $this->getAssessmentDescription($currentRagScore, $this->getRagDate($site->getCurrentRiskAssessment())),
                    self::VTS_CURRENT_STATUS => (new StatusPresenter())->getStatusFields($this->ragClassifier->setScore($currentRagScore)->getRagScore()),
                    self::VTS_PREV_ASSESSMENT => $this->getAssessmentDescription($previousRagScore, $this->getRagDate($site->getPreviousRiskAssessment())),
                    self::VTS_PREV_STATUS => isset($previousRagScore) ? (new StatusPresenter())->getStatusFields($this->ragClassifier->setScore($previousRagScore)->getRagScore()) : null,
                    self::VTS_TEST_QUALITY_INFORMATION_LINK => [$testLogsUrl, $tqiUrl, $annualAssessmentsUrl]
                ];
        }

        $this->table = (new Table())
            ->setRowsTotalCount($authorisedExaminerSitePerformanceDto->getSiteTotalCount())
            ->setSearchParams($searchParams)
            ->setData($rows)
            ->setColumns($this->getTableColumns());
        $this->table
            ->getTableOptions()
            ->setHasMetaTitle(true)
            ->setFooterViewScript(self::TABLE_PAGINATION_FOOTER)
            ->setTableClass(self::TABLE_CLASS);
    }

    public function getTable()
    {
        return $this->table;
    }

    /**
     * @return array
     */
    private function getTableColumns()
    {
        return [
            [
                'title' => self::SITE_ID,
                'tdClass' => self::TABULAR_CLASS,
                'thSubTitleColspan' => '2',
                'sub' => [
                    [
                        'field' => self::VTS_NUMBER,
                    ],
                ],
            ],
            [
                'title' => self::VEHICLE_TESTING_STATION,
                'tdClass' => self::TABULAR_CLASS,
                'thColspan' => '1',
                'sub' => [
                    [
                        'field' => self::VTS_NAME,
                    ],
                    [
                        'field' => self::VTS_ADDRESS,
                        'escapeHtml' => false,
                        'formatter' => SubRow::class,
                    ],
                    [
                        'field' => self::VTS_TEST_LOGS_LINK,
                        'formatter' => UrlPresenterLinkWithParams::class,
                        'fieldClass' => 'inline-link',
                    ],
                    [
                        'field' => self::VTS_TEST_QUALITY_INFORMATION_LINK,
                        'formatter' => UrlPresenterLinkWithParams::class,
                        'fieldClass' => 'inline-link',
                    ],
                    [
                        'field' => self::VTS_TESTERS_ANNUAL_ASSESSMENTS_LINK,
                        'formatter' => UrlPresenterLinkWithParams::class,
                        'fieldClass' => 'inline-link',
                    ]
                ],
            ],
            [
                'title' => self::SITE_ASSESSMENT,
                'tdClass' => '',
                'thColspan' => '2',
                'subTitle' => 'Previous',
                'sub' => [
                    [
                        'field' => self::VTS_PREV_STATUS,
                        'formatter' => Status::class,
                    ],
                    [
                        'field' => self::VTS_PREV_ASSESSMENT,
                        'formatter' => MultiRow::class,
                        'fieldClass' => 'result-table__meta result-table__meta--group'
                    ],
                ],
            ],
            [
                'tdClass' => '',
                'subTitle' => 'Current',
                'sub' => [
                    [
                        'field' => self::VTS_CURRENT_STATUS,
                        'formatter' => Status::class,
                    ],
                    [
                        'field' => self::VTS_CURRENT_ASSESSMENT,
                        'formatter' => MultiRow::class,
                        'fieldClass' => 'result-table__meta result-table__meta--group'
                    ],
                ],
            ],
        ];
    }

    public function aeHasAssociatedSites()
    {
        return $this->siteCount > 0;
    }
}
