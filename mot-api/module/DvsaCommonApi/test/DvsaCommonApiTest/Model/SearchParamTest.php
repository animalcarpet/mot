<?php

namespace DvsaCommonApiTest\Model;

use DvsaCommon\Constants\SearchParamConst;
use DvsaCommon\Dto\Search\SearchParamsDto;
use DvsaCommon\Dto\Vehicle\VehicleDto;
use DvsaCommonApi\Model\SearchParam;
use DvsaCommonApiTest\Service\AbstractServiceTestCase;

class SearchParamTest extends AbstractServiceTestCase
{
    private $mockEntityManager;

    public function setUp()
    {
        $this->mockEntityManager = $this->getMockEntityManager();
    }

    /**
     * @dataProvider dataProviderTestData
     */
    public function testGetters($testData, $expect)
    {
        $obj = new SearchParam();
        $obj
            ->setSortColumnId($testData['sortColumnId'])
            ->setSortDirection($testData['sortDirection'])
            ->setPageNr($testData['pageNr'])
            ->setRowCount($testData['rowCount'])
            ->setStart($testData['start'])
            ->setFormat($testData['format'])
            ->setIsApiGetData($testData['isApiGetData'])
            ->setIsApiGetTotalCount($testData['isApiGetTotalCount']);

        $this->assertEquals($expect['sortColumnId'], $obj->getSortColumnId());
        $this->assertEquals($expect['sortDirection'], $obj->getSortDirection());
        $this->assertEquals($expect['pageNr'], $obj->getPageNr());
        $this->assertEquals($expect['rowCount'], $obj->getRowCount());
        $this->assertEquals($expect['start'], $obj->getStart());
        $this->assertEquals($expect['format'], $obj->getFormat());
        $this->assertEquals($expect['isApiGetData'], $obj->isApiGetData());
        $this->assertEquals($expect['isApiGetTotalCount'], $obj->isApiGetTotalCount());
    }

    public function dataProviderTestData()
    {
        return [
            [
                'testData' => [
                    'sortColumnId' => 999,
                    'sortDirection' => 'direction',
                    'pageNr' => -1,
                    'rowCount' => 9999,
                    'start' => 8888,
                    'format' => 'expected format',
                    'isApiGetData' => true,
                    'isApiGetTotalCount' => true,
                ],
                'expect' => [
                    'sortColumnId' => 999,
                    'sortDirection' => null,
                    'pageNr' => 0,
                    'rowCount' => 9999,
                    'start' => 8888,
                    'format' => 'expected format',
                    'isApiGetData' => true,
                    'isApiGetTotalCount' => true,
                ],
            ],
            [
                'testData' => [
                    'sortColumnId' => 'startedDate',
                    'sortDirection' => SearchParamConst::SORT_DIRECTION_DESC,
                    'pageNr' => 'A',
                    'rowCount' => 'A',
                    'start' => 'B',
                    'format' => 'formatAbc',
                    'isApiGetData' => null,
                    'isApiGetTotalCount' => 'a',
                ],
                'expect' => [
                    'sortColumnId' => 'startedDate',
                    'sortDirection' => SearchParamConst::SORT_DIRECTION_DESC,
                    'pageNr' => 0,
                    'rowCount' => 0,
                    'start' => 0,
                    'format' => 'formatAbc',
                    'isApiGetData' => false,
                    'isApiGetTotalCount' => true,
                ],
            ],
            [
                'testData' => [
                    'sortColumnId' => '123',
                    'sortDirection' => SearchParamConst::SORT_DIRECTION_ASC,
                    'pageNr' => '3',
                    'rowCount' => '222',
                    'start' => null,
                    'format' => 'formatAbc2',
                    'isApiGetData' => 'a',
                    'isApiGetTotalCount' => null,
                ],
                'expect' => [
                    'sortColumnId' => '123',
                    'sortDirection' => SearchParamConst::SORT_DIRECTION_ASC,
                    'pageNr' => 3,
                    'rowCount' => 222,
                    'start' => 444,
                    'format' => 'formatAbc2',
                    'isApiGetData' => true,
                    'isApiGetTotalCount' => false,

                ],
            ],
        ];
    }

    public function testFromDto()
    {
        $dto = new SearchParamsDto();

        $dto
            ->setSortBy(999)
            ->setSortDirection(SearchParamConst::SORT_DIRECTION_ASC)
            ->setPageNr(777)
            ->setRowsCount(888)
            ->setStart(999)
            ->setFormat(SearchParamConst::FORMAT_DATA_CSV)
            ->setIsApiGetData(true)
            ->setIsApiGetTotalCount(true);

        $obj = new SearchParam($this->mockEntityManager);
        $obj->fromDto($dto);

        $this->assertEquals($dto->getSortBy(), $obj->getSortColumnId());
        $this->assertEquals($dto->getSortDirection(), $obj->getSortDirection());
        $this->assertEquals($dto->getPageNr(), $obj->getPageNr());
        $this->assertEquals($dto->getRowsCount(), $obj->getRowCount());
        $this->assertEquals($dto->getStart(), $obj->getStart());
        $this->assertEquals($dto->getFormat(), $obj->getFormat());
        $this->assertEquals($dto->isApiGetData(), $obj->isApiGetData());
        $this->assertEquals($dto->isApiGetTotalCount(), $obj->isApiGetTotalCount());
    }

    public function testFromDtoErr()
    {
        $this->setExpectedException(
            \InvalidArgumentException::class,
            'fromDto Expects instance of SearchParamsDto, you passed '.VehicleDto::class
        );

        $obj = new SearchParam($this->mockEntityManager);
        $obj->fromDto(new VehicleDto());
    }

    public function testToDto()
    {
        $obj = new SearchParam($this->mockEntityManager);
        $obj
            ->setSortColumnId(7777)
            ->setSortDirection(SearchParamConst::SORT_DIRECTION_DESC)
            ->setRowCount(6666)
            ->setRowCount(88888)
            ->setStart(9999)
            ->setFormat(SearchParamConst::FORMAT_DATA_OBJECT)
            ->setIsApiGetData(true)
            ->setIsApiGetTotalCount(false);

        $dto = $obj->toDto($dto);

        $this->assertEquals($dto->getSortBy(), $obj->getSortColumnId());
        $this->assertEquals($dto->getSortDirection(), $obj->getSortDirection());
        $this->assertEquals($dto->getPageNr(), $obj->getPageNr());
        $this->assertEquals($dto->getRowsCount(), $obj->getRowCount());
        $this->assertEquals($dto->getStart(), $obj->getStart());
        $this->assertEquals($dto->getFormat(), $obj->getFormat());
        $this->assertEquals($dto->isApiGetData(), $obj->isApiGetData());
        $this->assertEquals($dto->isApiGetTotalCount(), $obj->isApiGetTotalCount());
    }
}
