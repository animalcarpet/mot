<?php

namespace Organisation\ViewModel\MotTestLog;

use DvsaCommon\Constants\SearchParamConst;
use DvsaCommon\Dto\Organisation\MotTestLogSummaryDto;
use DvsaCommon\Dto\Organisation\OrganisationDto;
use DvsaCommon\UrlBuilder\AuthorisedExaminerUrlBuilderWeb;
use Organisation\ViewModel\MotTestLog\Formatter\VehicleModelSubRow;
use Report\Filter\FilterBuilder;
use Report\Table\Formatter\MotTestLink;
use Report\Table\Formatter\SubRow;
use Report\Table\Table;
use Zend\Stdlib\Parameters;

class MotTestLogViewModel
{
    /** for getDateRange, we want *today* as a timestamp range */
    const RANGE_TODAY = 'today';

    /** for getDateRange, we want *last week* as a timestamp range */
    const RANGE_LAST_WEEK = 'lastWeek';

    /** for getDateRange, we want *last month* as a timestamp range */
    const RANGE_LAST_MONTH = 'lastMonth';

    /** @var MotTestLogSummaryDto */
    private $logSummary;
    /** @var OrganisationDto */
    private $organisation;
    /** @var MotTestLogFormViewModel */
    private $formModel;
    /** @var  Table */
    private $table;
    /** @var  FilterBuilder */
    private $filterBuilder;

    public function __construct(
        OrganisationDto $org,
        MotTestLogSummaryDto $logData
    ) {
        $this->setOrganisation($org);
        $this->setMotTestLogSummary($logData);
        $this->setFormModel(new MotTestLogFormViewModel());

        $this->defineTable();
        $this->defineFilterBuilder();

        $this->setDefaultValues();
    }

    private function setDefaultValues()
    {
        $lastWeek = $this->getDateRange(self::RANGE_LAST_WEEK);

        $defValues = new Parameters(
            [
                // Monday last week
                SearchParamConst::SEARCH_DATE_FROM_QUERY_PARAM => $lastWeek['from'],
                // Sunday last week
                SearchParamConst::SEARCH_DATE_TO_QUERY_PARAM   => $lastWeek['to'],
            ]
        );

        $this->parseData($defValues);
    }

    public function parseData(Parameters $paramData)
    {
        if ($paramData->count() > 0) {
            $this->getFormModel()->parseData($paramData);
            $this->getFilterBuilder()->setQueryParams($paramData);
        }
    }

    public function getDownloadUrl()
    {
        return AuthorisedExaminerUrlBuilderWeb::motTestLogDownloadCsv($this->organisation->getId())
            ->queryParams(
                [
                    SearchParamConst::SEARCH_DATE_FROM_QUERY_PARAM =>
                        $this->formModel->getDateFrom()->getDate()->getTimestamp(),
                    SearchParamConst::SEARCH_DATE_TO_QUERY_PARAM   =>
                        $this->formModel->getDateTo()->getDate()->getTimestamp(),
                ]
            )->toString();
    }

    private function defineTable()
    {
        $this->table = new Table();
        $this->table->setColumns(
            [
                [
                    'title'   => 'Date/time',
                    'sortBy'  => 'testDateTime',
                    'sub'    => [
                        [
                            'field'     => 'testDate',
                        ],
                        [
                            'field'     => 'testTime',
                            'formatter' => SubRow::class,
                        ],
                    ]
                ],
                [
                    'field'  => 'vehicleVRM',
                    'title'  => 'VRM',
                    'sortBy' => 'vehicleVRM',
                ],
                [
                    'title'    => 'Vehicle',
                    'sortBy' => 'makeModel',
                    'sub'    => [
                        [
                            'field'     => 'vehicleMake',
                        ],
                        [
                            'field'     => 'vehicleModel',
                            'formatter' => VehicleModelSubRow::class,
                        ],
                    ],
                ],
                [
                    'title'    => 'User/Site Id',
                    'sortBy' => 'tester',
                    'sub'    => [
                        [
                            'field'     => 'testUsername',
                        ],
                        [
                            'field'     => 'siteNumber',
                            'formatter' => SubRow::class,
                        ],
                    ],
                ],
                [
                    'title'    => 'Status/Type',
                    'sortBy' => 'statusType',
                    'sub'    => [
                        [
                            'field'     => 'status',
                        ],
                        [
                            'field'     => 'testType',
                            'formatter' => SubRow::class,
                        ],
                    ],
                ],
            ]
        );

        return $this;
    }

    private function defineFilterBuilder()
    {
        $this->filterBuilder = new FilterBuilder();
        $this->filterBuilder
            ->setOptions(
                [
                    'today'     => $this->getDateRange(self::RANGE_TODAY),
                    'lastWeek'  => $this->getDateRange(self::RANGE_LAST_WEEK),
                    'lastMonth' => $this->getDateRange(self::RANGE_LAST_MONTH)
                ]
            );

        return $this;
    }

    /**
     * Answers an array with a label, from and to range for the specified range.
     *
     * @param $rangeName
     * @return Array
     */
    private function getDateRange($rangeName)
    {
        switch ($rangeName) {
            case self::RANGE_LAST_WEEK:
                return [
                    'label' => 'Last week (Mon-Sun)',
                    'from' => strtotime('monday last week 00:00:00'),
                    'to' => strtotime('sunday last week 23:59:59')
                ];

            case self::RANGE_LAST_MONTH:
                return [
                    'label' => 'Last Month (' . date('M', strtotime('last month')) . ')',
                    'from' => strtotime('first day of last month midnight'),
                    'to' => strtotime('first day of this month midnight -1 second')
                ];

            case self::RANGE_TODAY:
            default:
                return [
                    'label' => 'Today',
                    'from' => strtotime('today'),
                    'to' => strtotime('tomorrow -1 second')
                ];
        }
    }

    /**
     * @param MotTestLogSummaryDto $logData
     *
     * @return MotTestLogViewModel
     */
    public function setMotTestLogSummary(MotTestLogSummaryDto $logData)
    {
        $this->logSummary = $logData;

        return $this;
    }

    /**
     * @return MotTestLogSummaryDto
     */
    public function getMotTestLogSummary()
    {
        return $this->logSummary;
    }

    /**
     * @param OrganisationDto $org
     *
     * @return MotTestLogViewModel
     */
    public function setOrganisation(OrganisationDto $org)
    {
        $this->organisation = $org;

        return $this;
    }

    /**
     * @return OrganisationDto
     */
    public function getOrganisation()
    {
        return $this->organisation;
    }

    /**
     * @return MotTestLogFormViewModel
     */
    public function getFormModel()
    {
        return $this->formModel;
    }

    /**
     * @param MotTestLogFormViewModel $formModel
     *
     * @return MotTestLogViewModel
     */
    public function setFormModel($formModel)
    {
        $this->formModel = $formModel;

        return $this;
    }

    /**
     * @return $this
     */
    public function setTable(Table $table)
    {
        $this->table = $table;

        return $this;
    }

    /**
     * @return Table
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * @return $this
     */
    public function setFilterBuilder(FilterBuilder $filterBuilder)
    {
        $this->filterBuilder = $filterBuilder;

        return $this;
    }

    /**
     * @return FilterBuilder
     */
    public function getFilterBuilder()
    {
        return $this->filterBuilder;
    }
}
