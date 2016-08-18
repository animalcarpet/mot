<?php

namespace Dvsa\Mot\Api\StatisticsApiTest\TesterPerformance\QueryBuilder;

use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\ComponentBreakdown\Common\QueryBuilder\ComponentBreakdownQueryBuilder;

class ComponentBreakdownQueryBuilderTest extends \PHPUnit_Framework_TestCase
{
    /** @var  \Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\ComponentBreakdown\Common\QueryBuilder\ComponentBreakdownQueryBuilder */
    private $sut;

    public function setUp()
    {
        $this->sut = new \Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\ComponentBreakdown\Common\QueryBuilder\ComponentBreakdownQueryBuilder();
    }

    public function testSqlIsReturned()
    {
        $this->assertStringMatchesFormat("SELECT%aFROM%aWHERE%a", $this->sut->getSql());
    }
}
