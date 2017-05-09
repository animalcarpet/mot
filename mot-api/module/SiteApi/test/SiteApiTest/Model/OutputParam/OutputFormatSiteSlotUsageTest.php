<?php

namespace SiteApiTest\Model\OutputParam;

use DvsaEntities\Entity\MotTest;
use DvsaEntities\Entity\Person;
use DvsaEntities\Entity\Vehicle;
use SiteApi\Model\OutputFormat\OutputFormatSiteSlotUsage;

/**
 * Class OutputFormatSiteSlotUsageTest.
 */
class OutputFormatSiteSlotUsageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var OutputFormatSiteSlotUsage
     */
    private $outputParam;

    public function setUp()
    {
        $this->outputParam = new OutputFormatSiteSlotUsage();
    }

    public function testExtractItem()
    {
        $testId = 1;

        $person = new Person();

        $test = new MotTest();
        $test
            ->setId($testId)
            ->setVehicle(new Vehicle())
            ->setTester($person)
            ->setCompletedDate(new \DateTime());

        $results = [];

        $this->outputParam->extractItem($results, 0, $test);

        $this->assertTrue(count($results) > 0);
        $this->assertArrayHasKey($testId, $results);

        $result = $results[$testId];

        $this->assertArrayHasKey('id', $result);
        $this->assertArrayHasKey('date', $result);
        $this->assertArrayHasKey('tester', $result);
    }
}
