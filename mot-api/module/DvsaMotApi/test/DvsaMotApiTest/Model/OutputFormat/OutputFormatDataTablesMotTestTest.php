<?php
/**
 * This file is part of the DVSA MOT API project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace DvsaMotApiTest\Model\OutputFormat;

use DvsaCommon\Constants\OdometerReadingResultType;
use DvsaCommon\Constants\OdometerUnit;
use DvsaCommon\Date\DateUtils;
use DvsaCommon\Enum\MotTestStatusName;
use DvsaCommon\Enum\MotTestTypeCode;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Entity\Colour;
use DvsaEntities\Entity\Make;
use DvsaEntities\Entity\Model;
use DvsaEntities\Entity\ModelDetail;
use DvsaEntities\Entity\MotTest;
use DvsaEntities\Entity\MotTestCancelled;
use DvsaEntities\Entity\MotTestStatus;
use DvsaEntities\Entity\MotTestType;
use DvsaEntities\Entity\Person;
use DvsaEntities\Entity\Site;
use DvsaEntities\Entity\Vehicle;
use DvsaMotApi\Model\OutputFormat\OutputFormatDataTablesMotTest;

/**
 * Class OutputFormatDataTablesMotTestTest.
 */
class OutputFormatDataTablesMotTestTest extends \PHPUnit_Framework_TestCase
{
    const SITE_ID = 9999;
    const COLOUR = 'Black';
    const VIN = '1M8GDM9AXKP042788';
    const REG = 'FNZ6110';
    const MAKE = 'Renault';
    const MODEL = 'Clio';

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
                'motTestNumber' => '1',
                'status' => 'ABORTED',
                'number' => '1234567890005',
                'primaryColour' => self::COLOUR,
                'hasRegistration' => true,
                'odometerType' => OdometerReadingResultType::OK,
                'odometerValue' => 10000,
                'odometerUnit' => 'mi',
                'vin' => self::VIN,
                'registration' => self::REG,
                'make' => self::MAKE,
                'model' => self::MODEL,
                'testType' => 'Normal Test',
                'siteId' => self::SITE_ID,
                'siteNumber' => 'V1234',
                'startedDate' => '2011-01-01T11:11:11Z',
                'completedDate' => '2011-01-01T11:11:11Z',
                'testerUsername' => 'tester1',
                'testDate' => '2011-01-01T11:11:11Z',
                'reasonsForRejection' => null,
            ],
        ];
    }

    protected function getMotTest()
    {
        $modelDetail = new ModelDetail();
        $modelDetail->setModel(
            (new Model())->setName(self::MODEL)->setMake(
                (new Make())->setName(self::MAKE)
            )
        );

        $vehicle = new Vehicle();
        $vehicle->setColour((new Colour())->setName(self::COLOUR));
        $vehicle->setRegistration(self::REG);
        $vehicle->setVin(self::VIN);
        $vehicle->setModelDetail($modelDetail);
        $vehicle->setVersion(1);

        $motTestCancelled = new MotTestCancelled();

        $motTest = new MotTest();
        $motTest
            ->setVehicle($vehicle)
            ->setVehicleVersion(1)
            ->setId(1)
            ->setNumber('1234567890005')
            ->setStatus($this->createMotTestAbortedStatus())
            ->setHasRegistration(1)
            ->setOdometerValue(10000)
            ->setOdometerUnit(OdometerUnit::MILES)
            ->setOdometerResultType(OdometerReadingResultType::OK)
            ->setMotTestType((new MotTestType())->setDescription('Normal Test'))
            ->setVehicleTestingStation(
                (new Site())
                    ->setId(self::SITE_ID)
                    ->setSiteNumber('V1234')
            )
            ->setTester((new Person())->setUsername('tester1'))
            ->setStartedDate(DateUtils::toDateTime('2011-01-01T11:11:11Z'))
            ->setMotTestCancelled($motTestCancelled)
        ;

        return $motTest;
    }

    protected function getMotTestJsonDataTable()
    {
        return [
            '1234567890005' => [
                'status' => 'ABORTED',
                'motTestNumber' => '1234567890005',
                'primaryColour' => self::COLOUR,
                'hasRegistration' => 1,
                'odometer' => '10000 mi',
                'vin' => self::VIN,
                'registration' => self::REG,
                'make' => self::MAKE,
                'model' => self::MODEL,
                'testType' => 'Normal Test',
                'siteId' => self::SITE_ID,
                'siteNumber' => 'V1234',
                'startedDate' => '2011-01-01T11:11:11Z',
                'completedDate' => '2011-01-01T11:11:11Z',
                'testerUsername' => 'tester1',
                'testDate' => '2011-01-01T11:11:11Z',
                'reasonsForRejection' => null,
            ],
        ];
    }

    private function createMotTestAbortedStatus()
    {
        $status = XMock::of(MotTestStatus::class);
        $status
            ->expects($this->any())
            ->method('getName')
            ->willReturn(MotTestStatusName::ABORTED);

        return $status;
    }
}
