<?php

namespace Organisation\ViewModel\TestQualityInformation;

use Core\Formatting\RiskScoreAssessmentFormatter;
use Core\Routing\VtsRouteList;
use DvsaCommon\ApiClient\Statistics\AePerformance\Dto\AuthorisedExaminerSitesPerformanceDto;
use DvsaCommon\Dto\Search\SearchParamsDto;
use Organisation\Presenter\StatusPresenter;
use Organisation\Presenter\UrlPresenterData;
use Report\Table\Formatter\Status;
use Report\Table\Formatter\SubRow;
use Report\Table\Formatter\UrlPresenterLinkWithParams;
use Site\Service\RiskAssessmentScoreRagClassifier;
use Zend\Paginator\Paginator;
use Report\Table\Table;

class TestQualityInformationViewModel
{
    const TABLE_PAGINATION_FOOTER = 'table/gds-footer';
    const VEHICLE_TESTING_STATION = 'Vehicle testing station';
    const RISK_ASSESSMENT = 'Risk assessment';
    const TEST_QUALITY_INFORMATION = 'Test quality information';
    const VTS_NAME = 'Vts name';
    const VTS_NUMBER = 'Vts number';
    const VTS_ID = 'Vts ID';
    const VTS_RAG = 'Vts RAG';
    const VTS_STATUS = 'Vts Status';
    const VTS_STATUS_CLASS = 'Vts Status class';
    const VTS_ADDRESS = 'Vts Address';
    const VTS_VIEW_LINK = 'Vts view link';
    const VTS_VIEW_LINK_TEXT = 'View';

    const NUMERIC_CLASS = 'numeric';
    const TABULAR_CLASS = 'tabular';

    private $returnLink;
    /** @var  Table */
    private $table;

    /** @var RiskAssessmentScoreRagClassifier $ragClassifier */
    private $ragClassifier;

    /*
     * @param  AuthorisedExaminerSitesPerformanceDto[]  $authorisedExaminerSitePerformanceDto
     * @param   string                                  $returnLink
     */
    public function __construct(
        AuthorisedExaminerSitesPerformanceDto $authorisedExaminerSitePerformanceDto,
        $returnLink,
        RiskAssessmentScoreRagClassifier $ragClassifier,
        SearchParamsDto $searchParams
    )
    {
        $this->returnLink = $returnLink;
        $this->ragClassifier = $ragClassifier;
        $this->setTable($authorisedExaminerSitePerformanceDto, $searchParams);
    }

    public function getReturnLink()
    {
        return $this->returnLink;
    }

    /*
     * @param  AuthorisedExaminerSitesPerformanceDto  $authorisedExaminerSitePerformanceDto
     */
    private function setTable(AuthorisedExaminerSitesPerformanceDto $authorisedExaminerSitePerformanceDto, SearchParamsDto $searchParams)
    {
        $rows = [];
        foreach ($authorisedExaminerSitePerformanceDto->getSites() as $site) {
            $rag = $site->getRiskAssessmentScore() !== null ? $site->getRiskAssessmentScore() : 0.00;
            $this->ragClassifier->setScore($rag);

            $rows[] =
                [
                    self::VTS_NAME => $site->getName(),
                    self::VTS_ADDRESS => $site->getAddress()->getFullAddressString(),
                    self::VTS_NUMBER => $site->getNumber(),
                    self::VTS_ID => $site->getId(),
                    self::VTS_RAG => RiskScoreAssessmentFormatter::formatRiskScore($rag),
                    self::VTS_STATUS => (new StatusPresenter())->getStatusFields($this->ragClassifier->getRagScore()),
                    self::VTS_VIEW_LINK => new UrlPresenterData(
                        self::VTS_VIEW_LINK_TEXT,
                        VtsRouteList::VTS_TEST_QUALITY,
                        ['id' => $site->getId()],
                        ['query' =>
                            ['returnToAETQI' => true],
                        ]
                    ),
                ];
        }

        $this->table = (new Table())
            ->setRowsTotalCount($authorisedExaminerSitePerformanceDto->getSiteTotalCount())
            ->setSearchParams($searchParams)
            ->setData($rows)
            ->setColumns($this->getTableColumns());
        $this->table
            ->getTableOptions()
            ->setFooterViewScript(self::TABLE_PAGINATION_FOOTER);
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
                'title' => self::VEHICLE_TESTING_STATION,
                'tdClass' => self::TABULAR_CLASS,
                'sub' => [
                    [
                        'field' => self::VTS_NAME,
                    ],
                    [
                        'field' => self::VTS_ADDRESS,
                        'escapeHtml' => false,
                        'formatter' => SubRow::class,
                    ],
                ],
            ],
            [
                'title' => '',
                'thClass' => self::NUMERIC_CLASS,
                'sub' => [
                    [
                        'field' => self::VTS_NUMBER,
                    ],
                ],
            ],
            [
                'title' => self::RISK_ASSESSMENT,
                'thClass' => self::NUMERIC_CLASS,
                'tdClass' => self::NUMERIC_CLASS,
                'sub' => [
                    [
                        'field' => self::VTS_STATUS,
                        'formatter' => Status::class,
                    ],
                    [
                        'field' => self::VTS_RAG,
                        'formatter' => SubRow::class,
                    ],
                ],
            ],
            [
                'title' => self::TEST_QUALITY_INFORMATION,
                'thClass' => self::NUMERIC_CLASS,
                'tdClass' => self::NUMERIC_CLASS,
                'sub' => [
                    [
                        'field' => self::VTS_VIEW_LINK,
                        'formatter' => UrlPresenterLinkWithParams::class,
                    ],
                ]
            ],
        ];
    }
}