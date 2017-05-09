<?php

namespace DvsaMotApiTest\Model\OutputFormat;

use DvsaMotApi\Model\OutputFormat\OutputFormatDataCsvMotTestLog;
use DvsaMotApi\Model\OutputFormat\OutputFormatDataTablesMotTestLog;

/**
 * Class OutputFormatDataCsvMotTestLogTest.
 */
class OutputFormatDataTablesMotTestLogTest extends \PHPUnit_Framework_TestCase
{
    const MOT_TEST_NR = 9999999;

    /* @var OutputFormatDataCsvMotTestLog */
    protected $outputFormat;

    public function setUp()
    {
        $this->outputFormat = new OutputFormatDataTablesMotTestLog();
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
                    'number' => self::MOT_TEST_NR,
                    'siteNumber' => 888,
                    'testDate' => '2014-09-12 12:13:14',
                    'registration' => 'test_VRM',
                    'makeName' => 'test_Make',
                    'modelName' => 'test_Model',
                    'userName' => 'userName_test',
                    'testTypeName' => 'NORMAL TEST',
                    'status' => 'PASSED',
                    'emLogId' => null,
                ],
                'expectTestData' => [
                    self::MOT_TEST_NR => [
                        'motTestNumber' => self::MOT_TEST_NR,
                        'siteNumber' => 888,
                        'testDate' => '12 September 2014',
                        'testTime' => '1:13pm',
                        'vehicleVRM' => 'test_VRM',
                        'vehicleMake' => 'test_Make',
                        'vehicleModel' => 'test_Model',
                        'testUsername' => 'userName_test',
                        'testType' => 'NORMAL TEST',
                        'status' => 'PASSED',
                    ],
                ],
            ],
            [
                'testData' => [
                    'number' => self::MOT_TEST_NR,
                    'siteNumber' => 888,
                    'testDate' => '2013-12-11 10:11:12',
                    'registration' => 'test_VRM',
                    'makeName' => 'test_Make',
                    'modelName' => 'test_Model',
                    'userName' => 'userName_test',
                    'testTypeName' => 'NORMAL TEST',
                    'status' => 'PASSED',
                    'emLogId' => 123465,
                ],
                'expectTestData' => [
                    self::MOT_TEST_NR => [
                        'motTestNumber' => self::MOT_TEST_NR,
                        'siteNumber' => 888,
                        'testDate' => '11 December 2013',
                        'testTime' => '',
                        'vehicleVRM' => 'test_VRM',
                        'vehicleMake' => 'test_Make',
                        'vehicleModel' => 'test_Model',
                        'testUsername' => 'userName_test',
                        'testType' => 'NORMAL TEST',
                        'status' => 'PASSED',
                    ],
                ],
            ],
        ];
    }
}
