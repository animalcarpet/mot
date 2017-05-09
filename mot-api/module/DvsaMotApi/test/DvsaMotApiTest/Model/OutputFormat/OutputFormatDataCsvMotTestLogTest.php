<?php

namespace DvsaMotApiTest\Model\OutputFormat;

use DvsaMotApi\Model\OutputFormat\OutputFormatDataCsvMotTestLog;

/**
 * Class OutputFormatDataCsvMotTestLogTest.
 */
class OutputFormatDataCsvMotTestLogTest extends \PHPUnit_Framework_TestCase
{
    /* @var OutputFormatDataCsvMotTestLog */
    protected $outputFormat;

    public function setUp()
    {
        $this->outputFormat = new OutputFormatDataCsvMotTestLog();
        $this->outputFormat->setSourceType(OutputFormatDataCsvMotTestLog::SOURCE_TYPE_NATIVE);
    }

    /**
     * @dataProvider dataProviderTestOutputFormatDataCsvMotTestLogExtractItem
     */
    public function testOutputFormatDataCsvMotTestLogExtractItem(array $testData, array $expected)
    {
        $result = [];
        $this->outputFormat->extractItem($result, null, $testData);

        $this->assertEquals($expected, $result);
    }

    public function dataProviderTestOutputFormatDataCsvMotTestLogExtractItem()
    {
        return [
            [
                'testData' => [
                    'number' => 1,
                    'siteNumber' => 1,
                    'testDate' => '2014-09-12 12:00:00',
                    'number' => 1,
                    'registration' => 1,
                    'vin' => 'VIN1234',
                    'makeName' => 1,
                    'modelName' => 1,
                    'userName' => 'userName_test',
                    'testTypeName' => 1,
                    'status' => 1,
                    'testDuration' => 1,
                    'emRecTester' => 'emRecUserName',
                    'emRecDateTime' => '2013-12-11 12:11:10',
                    'emReason' => 'emReason_test',
                    'emCode' => 'em9999',
                    'client_ip' => '1.2.3.4',
                    'vehicle_class' => 4,
                    ],
                'expectTestData' => [
                    1 => [
                        'siteNumber' => 1,
                        'testDateTime' => '2014-09-12 12:00:00',
                        'testNumber' => 1,
                        'vehicleVRM' => 1,
                        'vehicleVIN' => 'VIN1234',
                        'vehicleMake' => 1,
                        'vehicleModel' => 1,
                        'testUsername' => 'userName_test',
                        'testType' => 1,
                        'status' => 1,
                        'testDuration' => '00:00:01',
                        'emRecTester' => 'emRecUserName',
                        'emRecDateTime' => '2013-12-11 12:11:10',
                        'emReason' => 'emReason_test',
                        'emCode' => 'em9999',
                        'clientIp' => '1.2.3.4',
                        'vehicleClass' => 4,
                    ],
                ],
            ],
        ];
    }
}
