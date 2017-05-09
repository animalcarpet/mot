<?php

namespace DvsaMotApiTest\Model\OutputFormat;

use DvsaEntities\Entity\Person;
use DvsaMotApi\Model\OutputFormat\OutputFormatTypeAheadTester;

/**
 * Class OutputFormatDataTablesTesterTest.
 */
class OutputFormatDataTablesTesterTest extends \PHPUnit_Framework_TestCase
{
    /* @var \DvsaMotApi\Model\OutputFormat\OutputFormatTypeAheadTester */
    protected $outputFormat;

    public function setUp()
    {
        $this->outputFormat = new OutputFormatTypeAheadTester();
    }

    public function testOutputFormatDataTablesVehicleExtractItem()
    {
        $result = [];
        $this->outputFormat->extractItem($result, 1, $this->getTester());
        $this->assertSame($this->getTesterJsonTypeAhead(), $result);
    }

    protected function getTester()
    {
        $tester = new Person();

        $tester
            ->setId(1)
            ->setUsername('username')
            ->setFirstName('firstname')
            ->setMiddleName('middlename')
            ->setFamilyName('familyname');

        return $tester;
    }

    protected function getTesterJsonTypeAhead()
    {
        return [
            '1' => 'username, firstname middlename familyname',
        ];
    }
}
