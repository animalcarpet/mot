<?php

namespace DvsaEntitiesTest\DqlBuilder\SearchParam;

use Doctrine\ORM\EntityManager;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\DqlBuilder\SearchParam\MotTestLogSearchParam;

class MotTestLogSearchParamTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MotTestLogSearchParam
     */
    private $searchParam;

    public function setUp()
    {
        $this->searchParam = new MotTestLogSearchParam(XMock::of(EntityManager::class));

        parent::setUp();
    }

    /**
     * @dataProvider dataProviderTestGetSortColumnNameDatabase
     */
    public function testGetSortColumnNameDatabase($sortBy, $expect)
    {
        $this->searchParam->setSortColumnId($sortBy);

        $actual = $this->searchParam->getSortColumnNameDatabase();

        $this->assertSame($expect, $actual);
    }

    public function dataProviderTestGetSortColumnNameDatabase()
    {
        return [
            [
                'sortBy' => 'noSortField',
                'expect' => MotTestLogSearchParam::DEFAULT_SORT_COLUMN,
            ],
            [
                'sortBy' => 'makeModel',
                'expect' => ['makeName', 'modelName'],
            ],
        ];
    }
}
