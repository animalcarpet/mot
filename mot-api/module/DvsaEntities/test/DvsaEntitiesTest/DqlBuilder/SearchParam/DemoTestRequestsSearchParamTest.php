<?php

namespace DvsaEntitiesTest\DqlBuilder\SearchParam;

use DvsaCommon\Constants\SearchParamConst;
use DvsaCommon\Dto\Search\DemoTestRequestsSearchParamsDto;
use DvsaCommon\Dto\Search\SearchParamsDto;
use DvsaCommonApi\Model\SearchParam;
use DvsaCommonApiTest\Service\AbstractServiceTestCase;
use DvsaEntities\DqlBuilder\SearchParam\DemoTestRequestsSearchParam;

/**
 * Class DemoTestRequestsSearchParamTest.
 */
class DemoTestRequestsSearchParamTest extends AbstractServiceTestCase
{
    const USER = 'user';
    const USER_SORTED_PARAMETER = 'person.username';
    const CONTACT = 'contact';
    const CONTACT_SORTED_PARAMETER = 'email.email';
    const GROUP = 'group';
    const GROUP_SORTED_PARAMETER = 'vehicle_class_group.code';
    const VTS = 'vts_postcode';
    const VTS_SORTED_PARAMETER = 'address.postcode';
    const DATE_ADDED = 'date_added';
    const DATE_ADDED_SORTED_PARAMETER = 'qualification_award.createdOn';

    public function testDemoTestRequestsSearchParam()
    {
        $searchParam = new DemoTestRequestsSearchParam();

        $searchParam->fromDto(new DemoTestRequestsSearchParamsDto());

        $this->assertInstanceOf(DemoTestRequestsSearchParam::class, $searchParam->process());
        $this->assertSame(self::USER_SORTED_PARAMETER, $searchParam->getSortColumnNameDatabase());

        $searchParam->setSortColumnId(self::USER);
        $this->assertSame([self::USER_SORTED_PARAMETER], $searchParam->getSortColumnNameDatabase());
        $searchParam->setSortColumnId(self::CONTACT);
        $this->assertSame([self::CONTACT_SORTED_PARAMETER], $searchParam->getSortColumnNameDatabase());
        $searchParam->setSortColumnId(self::GROUP);
        $this->assertSame([self::GROUP_SORTED_PARAMETER], $searchParam->getSortColumnNameDatabase());
        $searchParam->setSortColumnId(self::VTS);
        $this->assertSame([self::VTS_SORTED_PARAMETER], $searchParam->getSortColumnNameDatabase());
        $searchParam->setSortColumnId(self::DATE_ADDED);
        $this->assertSame([self::DATE_ADDED_SORTED_PARAMETER], $searchParam->getSortColumnNameDatabase());
    }

    public function testFromDto()
    {
        $dto = new DemoTestRequestsSearchParamsDto();

        $dto
            ->setSortBy(999)
            ->setSortDirection(SearchParamConst::SORT_DIRECTION_ASC)
            ->setPageNr(777)
            ->setRowsCount(888)
            ->setStart(999)
            ->setFormat(SearchParamConst::FORMAT_DATA_CSV)
            ->setIsApiGetData(true)
            ->setIsApiGetTotalCount(true);

        $obj = new DemoTestRequestsSearchParam();
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

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testDemoTestRequestsSearchParamThrowErrorDto()
    {
        $searchParam = new DemoTestRequestsSearchParam();

        $searchParam->fromDto(new SearchParamsDto());
    }

    public function testToDto()
    {
        $obj = new SearchParam();
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
