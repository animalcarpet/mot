<?php

namespace DvsaEntitiesTest\DqlBuilder\SearchParam;

use DvsaCommon\Dto\Search\SearchParamsDto;
use DvsaCommon\Dto\Search\SiteSearchParamsDto;
use DvsaCommonApiTest\Service\AbstractServiceTestCase;
use DvsaEntities\DqlBuilder\SearchParam\SiteSearchParam;

/**
 * Class SiteSearchParamTest.
 */
class SiteSearchParamTest extends AbstractServiceTestCase
{
    const SITE_NUMBER = 'number';
    const SITE_NAME = 'name';
    const SITE_TOWN = 'town';
    const SITE_POSTCODE = 'postcode';
    const SITE_VEHICLE_CLASS = 'class';

    public function testSiteSearchParam()
    {
        $searchParam = new SiteSearchParam();

        $searchParam->fromDto($this->getDto());

        $this->assertInstanceOf(SiteSearchParam::class, $searchParam->process());
        $this->assertSame(self::SITE_NUMBER, $searchParam->getSiteNumber());
        $this->assertSame(self::SITE_NAME, $searchParam->getSiteName());
        $this->assertSame(self::SITE_TOWN, $searchParam->getSiteTown());
        $this->assertSame(self::SITE_POSTCODE, $searchParam->getSitePostcode());
        $this->assertSame(self::SITE_VEHICLE_CLASS, $searchParam->getSiteVehicleClass());
        $this->assertSame('site.name', $searchParam->getSortColumnNameDatabase());

        $searchParam->setSortColumnId('siteNumber');
        $this->assertSame('site.site_number', $searchParam->getSortColumnNameDatabase());
    }

    public function getDto()
    {
        $dto = (new SiteSearchParamsDto())
            ->setSiteNumber(self::SITE_NUMBER)
            ->setSiteName(self::SITE_NAME)
            ->setSiteTown(self::SITE_TOWN)
            ->setSitePostcode(self::SITE_POSTCODE)
            ->setSiteVehicleClass(self::SITE_VEHICLE_CLASS);

        return $dto;
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSiteSearchParamThrowErrorDto()
    {
        $searchParam = new SiteSearchParam();

        $searchParam->fromDto(new SearchParamsDto());
    }

    /**
     * @expectedException \UnexpectedValueException
     */
    public function testSiteSearchParamThrowErrorProcess()
    {
        $searchParam = new SiteSearchParam();

        $searchParam->fromDto(new SiteSearchParamsDto());
        $searchParam->process();
    }
}
