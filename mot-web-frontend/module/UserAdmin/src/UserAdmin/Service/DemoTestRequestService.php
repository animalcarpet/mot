<?php

namespace UserAdmin\Service;

use DateTime;
use DvsaCommon\Constants\SearchParamConst;
use DvsaCommon\Date\DateTimeDisplayFormat;
use DvsaCommon\Dto\MotTesting\DemoTestRequestsListDto;
use DvsaCommon\Dto\Search\DemoTestRequestsSearchParamsDto;
use DvsaCommon\Dto\Search\SearchParamsDto;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaReport\Service\Csv\CsvService;
use Report\Table\Formatter\SubRow;
use Report\Table\Table;
use Zend\Stdlib\Parameters;

class DemoTestRequestService implements AutoWireableInterface
{
    const DEFAULT_SORT_BY = DemoTestRequestsSearchParamsDto::SORT_BY_DATE_ADDED;
    const DEFAULT_SORT_DIRECTION = SearchParamConst::SORT_DIRECTION_DESC;
    const DEFAULT_ROW_COUNT = 50;
    const DEFAULT_PAGE_NUMBER = 1;
    const TABLE_FOOTER = 'table/gds-footer';

    const FIELD_USERNAME = 'User ID';
    const FIELD_DISPLAY_NAME = 'User name';
    const FIELD_EMAIL = 'Email';
    const FIELD_PHONE = 'Telephone number';
    const FIELD_GROUP = 'Group';
    const FIELD_VTS_ID = 'VTS ID';
    const FIELD_VTS_POSTCODE = 'VTS postcode';
    const FIELD_DATE_ADDED = 'Date added';
    const EMPTY_TEXT = 'N/A';

    public static $allowedSortableFields = [
        DemoTestRequestsSearchParamsDto::SORT_BY_USERNAME,
        DemoTestRequestsSearchParamsDto::SORT_BY_CONTACT,
        DemoTestRequestsSearchParamsDto::SORT_BY_GROUP,
        DemoTestRequestsSearchParamsDto::SORT_BY_VTS_POSTCODE,
        DemoTestRequestsSearchParamsDto::SORT_BY_DATE_ADDED,
    ];

    private $csvService;

    public static $allowedSortDirections = [
        SearchParamConst::SORT_DIRECTION_ASC,
        SearchParamConst::SORT_DIRECTION_DESC,
    ];

    public function __construct(CsvService $csvService)
    {
        $this->csvService = $csvService;
    }

    /**
     * @param DemoTestRequestsListDto $requestsListDto
     * @param SearchParamsDto         $searchParams
     *
     * @return Table
     */
    public function getGdsTable(DemoTestRequestsListDto $requestsListDto, SearchParamsDto $searchParams)
    {
        $requests = $this->prepareRequests($requestsListDto);
        $table = (new Table())
            ->setRowsTotalCount($requestsListDto->getTotalResultCount())
            ->setData($requests)
            ->setSearchParams($searchParams)
            ->setColumns($this->getTableColumns());

        $table->getTableOptions()
            ->setItemsText('requests')
            ->setItemsPerPageOptions([static::DEFAULT_ROW_COUNT])
            ->setFooterViewScript(self::TABLE_FOOTER);

        return $table;
    }

    /**
     * @param DemoTestRequestsListDto $requestsListDto
     * @param $response
     *
     * @return string
     */
    public function getCsvResponse(DemoTestRequestsListDto $requestsListDto, $response)
    {
        $this->csvService->setResponse($response);
        $this->csvService->setData($this->prepareRequests($requestsListDto));

        return $this->csvService->generateDocument('Demo-test-requests.csv');
    }

    /**
     * @param Parameters $requestData
     *
     * @return SearchParamsDto
     */
    public function getSortParams(Parameters $requestData)
    {
        return $this->getSearchParamsDto($requestData);
    }

    /**
     * @param Parameters $requestData
     *
     * @return SearchParamsDto
     */
    public function getSortParamsForCsv(Parameters $requestData)
    {
        return $this->getSearchParamsDto($requestData)->setRowsCount(null)->setPageNr(null);
    }

    /**
     * @param Parameters $requestData
     *
     * @return SearchParamsDto
     */
    protected function getSearchParamsDto(Parameters $requestData)
    {
        $pageNumber = (int) $requestData->get(SearchParamConst::PAGE_NR);
        $sortBy = $requestData->get(SearchParamConst::SORT_BY, static::DEFAULT_SORT_BY);
        $sortDirection = $requestData->get(SearchParamConst::SORT_DIRECTION, static::DEFAULT_SORT_DIRECTION);

        return (new DemoTestRequestsSearchParamsDto())
            ->setPageNr($pageNumber > 0 ? $pageNumber : static::DEFAULT_PAGE_NUMBER)
            ->setRowsCount(static::DEFAULT_ROW_COUNT)
            ->setSortBy(in_array($sortBy, static::$allowedSortableFields) ? $sortBy : static::DEFAULT_SORT_BY)
            ->setSortDirection(in_array($sortDirection, static::$allowedSortDirections) ? $sortDirection : static::DEFAULT_SORT_DIRECTION);
    }

    /**
     * @param DemoTestRequestsListDto $requestsListDto
     *
     * @return array
     */
    private function prepareRequests(DemoTestRequestsListDto $requestsListDto)
    {
        $result = [];
        foreach ($requestsListDto->getData() as $demoTestRequestDto) {
            $result[] = [
                self::FIELD_USERNAME => $this->getStringOrDefault($demoTestRequestDto->getUsername()),
                self::FIELD_DISPLAY_NAME => $this->getStringOrDefault($demoTestRequestDto->getDisplayName()),
                self::FIELD_EMAIL => $this->getStringOrDefault($demoTestRequestDto->getUserEmail()),
                self::FIELD_PHONE => $this->getStringOrDefault($demoTestRequestDto->getUserTelephoneNumber()),
                self::FIELD_GROUP => $this->getStringOrDefault($demoTestRequestDto->getCertificateGroupCode()),
                self::FIELD_VTS_ID => $this->getStringOrDefault($demoTestRequestDto->getVtsNumber()),
                self::FIELD_VTS_POSTCODE => $this->getStringOrDefault($demoTestRequestDto->getVtsPostcode()),
                self::FIELD_DATE_ADDED => DateTimeDisplayFormat::dateShort(new DateTime($demoTestRequestDto->getCertificateDateAdded())),
            ];
        }

        return $result;
    }

    /**
     * @param string $string
     * @param string $default
     *
     * @return string
     */
    protected function getStringOrDefault($string, $default = self::EMPTY_TEXT)
    {
        return empty($string) ? $default : $string;
    }

    /**
     * @return array
     */
    private function getTableColumns()
    {
        return [
            [
                'title' => 'User',
                'sortBy' => DemoTestRequestsSearchParamsDto::SORT_BY_USERNAME,
                'sub' => [
                    [
                        'field' => self::FIELD_USERNAME,
                    ],
                    [
                        'field' => self::FIELD_DISPLAY_NAME,
                        'formatter' => SubRow::class,
                    ],
                ],
            ],
            [
                'title' => 'Contact',
                'sortBy' => DemoTestRequestsSearchParamsDto::SORT_BY_CONTACT,
                'sub' => [
                    [
                        'field' => self::FIELD_EMAIL,
                    ],
                    [
                        'field' => self::FIELD_PHONE,
                        'formatter' => SubRow::class,
                    ],
                ],
            ],
            [
                'title' => 'Group',
                'sortBy' => DemoTestRequestsSearchParamsDto::SORT_BY_GROUP,
                'sub' => [
                    [
                        'field' => self::FIELD_GROUP,
                    ],
                ],
            ],
            [
                'title' => 'VTS postcode',
                'sortBy' => DemoTestRequestsSearchParamsDto::SORT_BY_VTS_POSTCODE,
                'sub' => [
                    [
                        'field' => self::FIELD_VTS_POSTCODE,
                    ],
                    [
                        'field' => self::FIELD_VTS_ID,
                        'formatter' => SubRow::class,
                    ],
                ],
            ],
            [
                'title' => 'Date added',
                'sortBy' => DemoTestRequestsSearchParamsDto::SORT_BY_DATE_ADDED,
                'sub' => [
                    [
                        'field' => self::FIELD_DATE_ADDED,
                    ],
                ],
            ],
        ];
    }
}
