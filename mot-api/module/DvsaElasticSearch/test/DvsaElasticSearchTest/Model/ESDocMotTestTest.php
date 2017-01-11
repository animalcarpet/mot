<?php

namespace DvsaElasticSearchTest\Model;

use Doctrine\Common\Collections\ArrayCollection;
use DvsaCommon\Constants\OdometerReadingResultType;
use DvsaCommon\Constants\SearchParamConst;
use DvsaCommon\Date\DateTimeApiFormat;
use DvsaCommon\Date\DateTimeDisplayFormat;
use DvsaCommon\Date\DateUtils;
use DvsaCommon\Enum\MotTestStatusName;
use DvsaCommonApi\Service\Exception\BadRequestException;
use DvsaCommonTest\TestUtils\XMock;
use DvsaElasticSearch\Model\ESDocMotTest;
use DvsaEntities\Entity\Colour;
use DvsaEntities\Entity\Language;
use DvsaEntities\Entity\Make;
use DvsaEntities\Entity\Model;
use DvsaEntities\Entity\MotTest;
use DvsaEntities\Entity\MotTestReasonForRejection;
use DvsaEntities\Entity\MotTestStatus;
use DvsaEntities\Entity\MotTestType;
use DvsaEntities\Entity\OdometerReading;
use DvsaEntities\Entity\Person;
use DvsaEntities\Entity\ReasonForRejection;
use DvsaEntities\Entity\Site;
use DvsaEntities\Entity\Vehicle;
use PHPUnit_Framework_TestCase;

/**
 * Class ESDocMotTestTest
 *
 * @package DvsaElasticSearchTest\Connection
 */
class ESDocMotTestTest extends \PHPUnit_Framework_TestCase
{
    /** @var ESDocMotTest */
    protected $docMotTest;

    public function setUp()
    {
        $this->docMotTest = new ESDocMotTest();
    }

    public function testEsDocMotTestAsEsDataReturnValue()
    {
        $this->assertSame($this->getMotTestData(), $this->docMotTest->asEsData($this->getMotEntity()));
    }

    public function testEsDocMotTestAsEsDataReturnValueWithStatusFailed()
    {
        $motTest = $this->getMotEntity();
        $motTest->setStatus($this->createMotTestFailedStatus());
        $motTestEs = $this->getMotTestData();
        $motTestEs['status'] = 'FAILED';
        $motTestEs['status_display'] = 'FAIL';

        $this->assertSame($motTestEs, $this->docMotTest->asEsData($motTest));
    }

    public function testEsDocMotTestAsEsDataReturnValueWithStatusPassed()
    {
        $motTest = $this->getMotEntity();
        $motTest->setStatus($this->createMotTestPassedStatus());
        $motTestEs = $this->getMotTestData();
        $motTestEs['status'] = 'PASSED';
        $motTestEs['status_display'] = 'PASS';

        $this->assertSame($motTestEs, $this->docMotTest->asEsData($motTest));
    }

    public function testEsDocMotTestAsEsDataReturnValueWithStatusAborted()
    {
        $motTest = $this->getMotEntity();
        $motTest->setStatus($this->createMotTestAbortedStatus());
        $motTestEs = $this->getMotTestData();
        $motTestEs['status'] = 'ABORTED';
        $motTestEs['status_display'] = 'ABORTED';

        $this->assertSame($motTestEs, $this->docMotTest->asEsData($motTest));
    }

    public function testEsDocMotTestAsEsDataReturnValueWithRfr()
    {
        $rfr = new ReasonForRejection();
        $rfr->setDescriptions(
            new ArrayCollection(
                [
                    (new \DvsaEntities\Entity\ReasonForRejectionDescription())
                        ->setLanguage((new Language())->setCode('EN'))
                        ->setName('MotTestReasonForRejection')
                ]
            )
        );

        $motTestRfr = new MotTestReasonForRejection();
        $motTestRfr->setId(1);
        $motTestRfr->setType(1);
        $motTestRfr->setReasonForRejection($rfr);

        $motTest = $this->getMotEntity();
        $motTest->addMotTestReasonForRejection($motTestRfr);
        $motTestEs = $this->getMotTestData();
        $motTestEs['reasonsForRejection'] = [
            1 => [
                [
                    'id'   => 1,
                    'name' => 'MotTestReasonForRejection'
                ]
            ]
        ];

        $this->assertSame($motTestEs, $this->docMotTest->asEsData($motTest));
    }

    public function testEsDocMotTestAsEsDataReturnValueWithCompletedDate()
    {
        $date = new \DateTime();
        $motTest = $this->getMotEntity();
        $motTestEs = $this->getMotTestData();

        $motTest->setCompletedDate($date);
        $motTestEs['testDate'] = DateTimeApiFormat::dateTime($date);
        $motTestEs['testDate_display'] = DateTimeDisplayFormat::dateTimeShort($date);
        $motTestEs['completedDate'] = DateTimeApiFormat::dateTime($date);
        $motTestEs['completedDate_display'] = DateTimeDisplayFormat::dateTimeShort($date);
        $motTestEs['completedDate_timestamp'] = strtotime($date->format('d M Y h:i'))
            + DateUtils::toUserTz($date)->getOffset();

        $this->assertEquals($motTestEs, $this->docMotTest->asEsData($motTest));
    }

    public function testEsDocMotTestAsEsDataReturnValueWithStartedDate()
    {
        $date = new \DateTime();
        $motTest = $this->getMotEntity();
        $motTestEs = $this->getMotTestData();

        $motTest->setStartedDate($date);
        $motTestEs['testDate'] = DateTimeApiFormat::dateTime($date);
        $motTestEs['testDate_display'] = DateTimeDisplayFormat::dateTimeShort($date);
        $motTestEs['startedDate'] = DateTimeApiFormat::dateTime($date);
        $motTestEs['startedDate_display'] = DateTimeDisplayFormat::dateTimeShort($date);
        $motTestEs['startedDate_timestamp'] = strtotime($date->format('d M Y h:i')) + DateUtils::toUserTz($date)->getOffset();

        $this->assertEquals($motTestEs, $this->docMotTest->asEsData($motTest));
    }

    public function testEsDocMotTestAsEsDataReturnValueWithOdometerValue()
    {
        $motTest = $this->getMotEntity();
        $motTestEs = $this->getMotTestData();

        $odometer = new OdometerReading();
        $odometer->setValue(1000);
        $odometer->setUnit('mi');

        $motTest->setOdometerReading($odometer);

        $motTestEs['odometerValue'] = 1000;
        $motTestEs['odometerUnit'] = 'mi';

        $this->assertSame($motTestEs, $this->docMotTest->asEsData($motTest));
    }

    public function testEsDocMotTestAsJsonReturnValueDataTable()
    {
        $motFromEs = $this->getMotEs();
        $motFromEs['format'] = SearchParamConst::FORMAT_DATA_TABLES;

        $this->assertEquals($this->getMotTestJsonDataTable(), $this->docMotTest->asJson($motFromEs));
    }

    public function testEsDocMotTestAsJsonReturnException()
    {
        $motFromEs = $this->getMotEs();
        $motFromEs['format'] = 'INVALID_FORMAT';
        $this->setExpectedException(BadRequestException::class);
        $this->docMotTest->asJson($motFromEs);
    }

    protected function getMotTestJsonDataTable()
    {
        return [
            '123456789012' => [
                'status'              => 'ABORTED',
                'motTestNumber'       => '123456789012',
                'primaryColour'       => 'Black',
                'hasRegistration'     => true,
                'odometer'            => '10000 mi',
                'vin'                 => '1M8GDM9AXKP042788',
                'registration'        => 'FNZ6110',
                'make'                => 'Renault',
                'model'               => 'Clio',
                'testType'            => 'Normal Test',
                'siteNumber'          => 'V1234',
                'testDate'            => '2011-02-02T11:11:11Z',
                'startedDate'         => null,
                'completedDate'       => null,
                'testerUsername'      => 'tester1',
                'reasonsForRejection' => [],
            ],
        ];
    }

    protected function getMotEs()
    {
        return [
            'data' => [
                [
                    '_source' => [
                        'motTestNumber'       => '123456789012',
                        'status'              => 'ABORTED',
                        'number'              => '123456789012',
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
                        'siteNumber'          => 'V1234',
                        'testDate'            => '2011-02-02T11:11:11Z',
                        'startedDate'         => null,
                        'completedDate'       => null,
                        'testerUsername'      => 'tester1',
                        'reasonsForRejection' => [],
                    ]
                ]
            ]
        ];
    }

    protected function getMotTestData()
    {
        return [
            'motTestNumber'           => '123456789012',
            'status'                  => 'ACTIVE',
            'status_display'          => 'IN PROGRESS',
            'number'                  => '123456789012',
            'primaryColour'           => 'Blue',
            'hasRegistration'         => 1,
            'odometerValue'           => null,
            'odometerUnit'            => null,
            'vehicleId'               => 1,
            'vin'                     => 'hdh7htref0gr5greh',
            'registration'            => 'FNZ 6JZ',
            'make'                    => 'Porshe',
            'model'                   => '911 Turbo',
            'testType'                => 'Normal Test',
            'siteNumber'              => 'V1234',
            'testDate'                => null,
            'testDate_display'        =>  null,
            'startedDate'             => null,
            'completedDate'           => null,
            'startedDate_display'     => null,
            'completedDate_display'   => null,
            'startedDate_timestamp'   => null,
            'completedDate_timestamp' => null,
            'testerId'                => 1,
            'testerUsername'          => 'ft-catb',
            'reasonsForRejection'     => [],
        ];

    }

    protected function getMotEntity()
    {
        $motTest = new MotTest();
        $motTest->setStatus($this->createMotTestActiveStatus());

        $make = new Make();
        $make->setName('Porshe');

        $model = new Model();
        $model->setName('911 Turbo');

        $vehicle = new Vehicle();
        $vehicle->setId(1);

        $tester = new Person();
        $tester->setId(1);
        $tester->setUsername('ft-catb');

        $colour = new Colour();
        $colour->setName('Blue');

        $site = new Site();
        $site->setSiteNumber('V1234');

        $type = new MotTestType();
        $type->setDescription("Normal Test");
        $type->setCode('NT');

        $motTest->setId(1)
            ->setRegistration('FNZ 6JZ')
            ->setMake($make)
            ->setModel($model)
            ->setVin('hdh7htref0gr5greh')
            ->setVehicle($vehicle)
            ->setMotTestType($type)
            ->setTester($tester)
            ->setHasRegistration(true)
            ->setStartedDate(null)
            ->setPrimaryColour($colour)
            ->setVehicleTestingStation($site)
            ->setNumber('123456789012')
            ->setCompletedDate(null)
            ->setStartedDate(null);

        return $motTest;
    }

    private function createMotTestActiveStatus()
    {
        return $this->createMotTestStatus(MotTestStatusName::ACTIVE);
    }

    private function createMotTestFailedStatus()
    {
        return $this->createMotTestStatus(MotTestStatusName::FAILED);
    }

    private function createMotTestPassedStatus()
    {
        return $this->createMotTestStatus(MotTestStatusName::PASSED);
    }

    private function createMotTestAbortedStatus()
    {
        return $this->createMotTestStatus(MotTestStatusName::ABORTED);
    }

    private function createMotTestStatus($name)
    {
        $status = XMock::of(MotTestStatus::class);
        $status
            ->expects($this->any())
            ->method("getName")
            ->willReturn($name);

        return $status;
    }
}
