<?php

namespace Dvsa\Mot\Api\StatisticsApiTest\TesterPerformance\QueryBuilder;

use Dvsa\Mot\Api\StatisticsApi\TesterPerformance\QueryBuilder\ComponentBreakdownQueryBuilder;

class ComponentBreakdownQueryBuilderTest extends \PHPUnit_Framework_TestCase
{
    /** @var  ComponentBreakdownQueryBuilder */
    private $sut;

    public function setUp()
    {
        $this->sut = new ComponentBreakdownQueryBuilder();
    }

    public function testSqlIsReturned()
    {
        $this->assertStringMatchesFormat("SELECT%aFROM%aWHERE%a", $this->sut->getSql());
    }
}
