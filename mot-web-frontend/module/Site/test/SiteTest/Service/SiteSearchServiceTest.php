<?php

namespace SiteTest\Service;

use DvsaCommon\Dto\Search\SiteSearchParamsDto;
use DvsaCommon\Dto\Site\SiteListDto;
use Report\Table\Table;
use Site\Service\SiteSearchService;

/**
 * Class SiteSearchServiceTest.
 */
class SiteSearchServiceTest extends \PHPUnit_Framework_TestCase
{
    public function testInitTable()
    {
        $service = new SiteSearchService();

        /**
         * @var Table
         */
        $table = $service->initTable($this->getDto());
        $this->assertInstanceOf(Table::class, $table);
        $this->assertSame(2, $table->getRowsTotalCount());
    }

    private function getDto()
    {
        return (new SiteListDto())
            ->setTotalResultCount(2)
            ->setSearched(new SiteSearchParamsDto());
    }
}
