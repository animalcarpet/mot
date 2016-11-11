<?php

namespace DvsaMotApiTest\Model\OutputFormat;

use DvsaCommon\Constants\OdometerReadingResultType;
use DvsaCommon\Date\DateUtils;
use DvsaCommon\Enum\MotTestStatusName;
use DvsaCommon\Enum\MotTestTypeCode;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Entity\Colour;
use DvsaEntities\Entity\Make;
use DvsaEntities\Entity\Model;
use DvsaEntities\Entity\MotTest;
use DvsaEntities\Entity\MotTestReasonForCancel;
use DvsaEntities\Entity\MotTestStatus;
use DvsaEntities\Entity\MotTestType;
use DvsaEntities\Entity\OdometerReading;
use DvsaEntities\Entity\Person;
use DvsaEntities\Entity\Site;
use DvsaMotApi\Model\OutputFormat\OutputFormatDataTablesMotTest;
use PHPUnit_Framework_TestCase;

/**
 * Class OutputFormatDataTablesMotTestTest
 *
 * @package DvsaMotApiTest\Model\OutputFormat
 */
class OutputFormatDataTablesMotTestTest extends \PHPUnit_Framework_TestCase
{
    const SITE_ID = 9999;

    /* @var \DvsaMotApi\Model\OutputFormat\OutputFormatDataTablesMotTest */
    protected $outputFormat;
    /* @var \DateTime */
    protected $date;

    public function setUp()
    {
        $this->outputFormat = new OutputFormatDataTablesMotTest();
        $this->date = new \DateTime();
    }

    public function testOutputFormatDataTablesMotTestExtractItemFromES()
    {
        $result = [];
        $this->outputFormat->extractItem($result, 1, $this->getMotTestEs());
        $this->assertEquals($this->getMotTestJsonDataTable(), $result);
    }

    public function testOutputFormatDataTablesMotTestExtractItemFromDoctrine()
    {
        $result = [];
        $this->outputFormat->extractItem($result, 1, $this->getMotTest());
        $this->assertEquals($this->getMotTestJsonDataTable(), $result);
    }

    public function testExtractedSiteValuesAreSetWhenNonMotTest()
    {
        $result = [];
        $motTest = $this->getMotTest();
        $motTest->setMotTestType((new MotTestType())->setCode(MotTestTypeCode::NON_MOT_TEST));

        $this->outputFormat->extractItem($result, 1, $motTest);

        $this->assertEquals(self::SITE_ID, $result[$motTest->getNumber()]['siteId']);
        $this->assertEquals('V1234', $result[$motTest->getNumber()]['siteNumber']);
    }

    public function testExtractedSiteValuesAreNullWhenDemoTest()
    {
        $result = [];
        $motTest = $this->getMotTest();
        $motTest->setMotTestType((new MotTestType())->setCode(MotTestTypeCode::DEMONSTRATION_TEST_FOLLOWING_TRAINING));

        $this->outputFormat->extractItem($result, 1, $motTest);

        $this->assertNull($result[$motTest->getNumber()]['siteId']);
        $this->assertNull($result[$motTest->getNumber()]['siteNumber']);
    }

    protected function getMotTestEs()
    {
        return [
            '_source' => [
                'motTestNumber'       => '1',
                'status'              => 'ABORTED',
                'number'              => '1234567890005',
                'primaryColour'       => 'Black',
                'hasRegistration'     => true,
                'odometerType'        => OdometerReadingResultType::OK,
                'odometerValue'       => 10000,
                'odometerUnit'        => 'mi',
                'vin'                 => '1M8GDM9AXKP042788',
                'registration'        => 'FNZ6110',
                'make'                => 'Renault',
                'model'               => 'Clio',
                'testType'            => 'Normal Test',
                'siteId'              => self::SITE_ID,
                'siteNumber'          => 'V1234',
                'startedDate'         => '2011-01-01T11:11:11Z',
                'completedDate'       => '2011-01-01T11:11:11Z',
                'testerUsername'      => 'tester1',
                'testDate'            => '2011-01-01T11:11:11Z',
                'reasonsForRejection' => [],
            ]
        ];
    }

    protected function getMotTest()
    {
        $motTest = new MotTest();
        $motTest
            ->setId(1)
            ->setNumber('1234567890005')
            ->setStatus($this->createMotTestAbortedStatus())
            ->setPrimaryColour((new Colour())->setName('Black'))
            ->setHasRegistration(1)
            ->setOdometerReading(
                (new OdometerReading())
                    ->setResultType(OdometerReadingResultType::OK)
                    ->setValue(10000)
                    ->setUnit('mi')
            )
            ->setVin('1M8GDM9AXKP042788')
            ->setRegistration('FNZ6110')
            ->setMake((new Make())->setName('Renault'))
            ->setModel((new Model())->setName('Clio'))
            ->setMotTestType((new MotTestType())->setDescription('Normal Test'))
            ->setVehicleTestingStation(
                (new Site())
                    ->setId(self::SITE_ID)
                    ->setSiteNumber('V1234')
            )
            ->setTester((new Person())->setUsername('tester1'))
            ->setStartedDate(DateUtils::toDateTime('2011-01-01T11:11:11Z'))
            ->setMotTestReasonForCancel((new MotTestReasonForCancel())->setReason([]))
        ;

        return $motTest;
    }

    protected function getMotTestJsonDataTable()
    {
        return [
            '1234567890005' => [
                'status'              => 'ABORTED',
                'motTestNumber'       => '1234567890005',
                'primaryColour'       => 'Black',
                'hasRegistration'     => 1,
                'odometer'            => '10000 mi',
                'vin'                 => '1M8GDM9AXKP042788',
                'registration'        => 'FNZ6110',
                'make'                => 'Renault',
                'model'               => 'Clio',
                'testType'            => 'Normal Test',
                'siteId'              => self::SITE_ID,
                'siteNumber'          => 'V1234',
                'startedDate'         => '2011-01-01T11:11:11Z',
                'completedDate'       => '2011-01-01T11:11:11Z',
                'testerUsername'      => 'tester1',
                'testDate'            => '2011-01-01T11:11:11Z',
                'reasonsForRejection' => [],
            ]
        ];
    }

    private function createMotTestAbortedStatus()
    {
        $status = XMock::of(MotTestStatus::class);
        $status
            ->expects($this->any())
            ->method("getName")
            ->willReturn(MotTestStatusName::ABORTED);

        return $status;
    }
}
