<?php
namespace PersonApiTest\Service\MotTestingAnnualCertificate\Event;

use DvsaCommon\Auth\MotIdentityInterface;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use DvsaCommon\Constants\EventDescription;
use DvsaCommon\Date\DateTimeDisplayFormat;
use DvsaCommon\Date\DateTimeHolder;
use DvsaCommon\Enum\EventTypeCode;
use DvsaCommon\Enum\VehicleClassGroupCode;
use DvsaCommonTest\Date\TestDateTimeHolder;
use DvsaCommonTest\TestUtils\MethodSpy;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Entity\QualificationAnnualCertificate;
use DvsaEntities\Entity\VehicleClassGroup;
use DvsaEntities\Repository\EventPersonMapRepository;
use DvsaEventApi\Service\EventService;
use PersonApi\Service\MotTestingAnnualCertificate\MotTestingAnnualCertificateEventService;

class MotTestingAnnualCertificateEventServiceTest extends \PHPUnit_Framework_TestCase
{
    const CERTIFICATE_NUMBER = "CERTIFICATE NUMBER";
    const SCORE = 100;
    const USERNAME = "USERNAME";
    const DATE_AWARDED = "2010-10-10";
    const DATE_CURRENT = "2016-06-06";
    const GROUP_A_EXPECTED_DESCRIPTION = "Tester annual exam details for group A recorded by USERNAME. Certificate number CERTIFICATE NUMBER and Certificate date 10 October 2010";
    const GROUP_B_EXPECTED_DESCRIPTION = "Tester annual exam details for group B recorded by USERNAME. Certificate number CERTIFICATE NUMBER and Certificate date 10 October 2010";

    /** @var MotIdentityProviderInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $identityProvider;
    /** @var  EventService|\PHPUnit_Framework_MockObject_MockObject */
    private $eventService;
    /** @var EventPersonMapRepository|\PHPUnit_Framework_MockObject_MockObject */
    private $eventPersonMapRepository;
    /** @var TestDateTimeHolder */
    private $dateTimeHolder;
    /** @var MotTestingAnnualCertificateEventService|\PHPUnit_Framework_MockObject_MockObject */
    private $sut;

    public function setUp()
    {
        $this->identityProvider = XMock::of(MotIdentityProviderInterface::class);
        $this->identityProvider->expects($this->any())
            ->method("getIdentity")
            ->willReturn($this->createIdentity());

        $this->eventService = XMock::of(EventService::class);
        $this->eventPersonMapRepository = XMock::of(EventPersonMapRepository::class);
        $this->dateTimeHolder = new TestDateTimeHolder(new \DateTime(self::DATE_CURRENT));

        $this->sut = new MotTestingAnnualCertificateEventService(
            $this->identityProvider,
            $this->eventService,
            $this->eventPersonMapRepository,
            $this->dateTimeHolder
        );
    }

    /**
     * @dataProvider dataProviderTestSendCreateEvent
     */
    public function testSendCreateEvent($groupCode, $eventTypeCode, $expectedDescription)
    {
        $addEventSpy = new MethodSpy($this->eventService, "addEvent");
        $eventPersonMapSpy = new MethodSpy($this->eventPersonMapRepository, "save");

        $this->sut->sendCreateEvent($this->createCertificate($groupCode));

        $addEventParams = $addEventSpy->getInvocations()[0]->parameters;
        $this->assertEquals($eventTypeCode, $addEventParams[0]);
        $this->assertEquals($expectedDescription, $addEventParams[1]);
        $this->assertEquals($this->dateTimeHolder->getCurrent(), $addEventParams[2]);

        $this->assertCount(1, $eventPersonMapSpy->getInvocations());
    }

    public function dataProviderTestSendCreateEvent()
    {
        return [
            [
                VehicleClassGroupCode::BIKES,
                EventTypeCode::GROUP_A_TESTER_ANNUAL_EXAM,
                self::GROUP_A_EXPECTED_DESCRIPTION
            ],
            [
                VehicleClassGroupCode::CARS_ETC,
                EventTypeCode::GROUP_B_TESTER_ANNUAL_EXAM,
                self::GROUP_B_EXPECTED_DESCRIPTION
            ]
        ];
    }

    /**
     * @dataProvider dataProviderTestSendUpdateEvent
     */
    public function testSendUpdateEvent($groupCode, $eventTypeCode)
    {
        $addEventSpy = new MethodSpy($this->eventService, "addEvent");
        $eventPersonMapSpy = new MethodSpy($this->eventPersonMapRepository, "save");

        $oldCertificate = $this->createCertificate($groupCode);
        $newCertificate = $this->createCertificate($groupCode);
        $newCertificate
            ->setCertificateNumber("NEW-CERT-NUMB")
            ->setScore(1)
            ->setDateAwarded(new \DateTime("2016-07-24"));


        $this->sut->sendUpdateEvent($oldCertificate, $newCertificate);

        $expectedDescription = sprintf(
            EventDescription::MOT_TESTING_ANNUAL_CERTIFICATE_EDIT,
            $newCertificate->getVehicleClassGroup()->getCode(),
            $this->identityProvider->getIdentity()->getUsername(),
            $oldCertificate->getCertificateNumber(),
            $newCertificate->getCertificateNumber(),
            DateTimeDisplayFormat::date($oldCertificate->getDateAwarded()),
            DateTimeDisplayFormat::date($newCertificate->getDateAwarded()),
            $oldCertificate->getScore(),
            $newCertificate->getScore()
        );

        $addEventParams = $addEventSpy->getInvocations()[0]->parameters;
        $this->assertEquals($eventTypeCode, $addEventParams[0]);
        $this->assertEquals($expectedDescription, $addEventParams[1]);
        $this->assertEquals($this->dateTimeHolder->getCurrent(), $addEventParams[2]);

        $this->assertCount(1, $eventPersonMapSpy->getInvocations());
    }

    public function dataProviderTestSendUpdateEvent()
    {
        return [
            [
                VehicleClassGroupCode::BIKES,
                EventTypeCode::CHANGE_GROUP_A_TESTER_ANNUAL_EXAM,
            ],
            [
                VehicleClassGroupCode::CARS_ETC,
                EventTypeCode::CHANGE_GROUP_B_TESTER_ANNUAL_EXAM,
            ]
        ];
    }

    /**
     * @dataProvider dataProviderTestSendRemoveEvent
     */
    public function testSendRemoveEvent($groupCode, $eventTypeCode, $expectedDescription)
    {
        $certificate = $this->createCertificate($groupCode);

        $this->eventService
            ->expects($this->once())
            ->method("addEvent")
            ->with($eventTypeCode, $expectedDescription, $this->dateTimeHolder->getCurrent());

        $this->eventPersonMapRepository
            ->expects($this->once())
            ->method("save");

        $this->sut->sendRemoveEvent($certificate);
    }

    public function dataProviderTestSendRemoveEvent()
    {
        return [
            [
                VehicleClassGroupCode::BIKES,
                EventTypeCode::REMOVE_GROUP_A_TESTER_ANNUAL_EXAM,
                'Tester annual exam removed for group A by USERNAME. Certificate number CERTIFICATE NUMBER, Exam date 10 October 2010 and score achieved 100%',
            ],
            [
                VehicleClassGroupCode::CARS_ETC,
                EventTypeCode::REMOVE_GROUP_B_TESTER_ANNUAL_EXAM,
                'Tester annual exam removed for group B by USERNAME. Certificate number CERTIFICATE NUMBER, Exam date 10 October 2010 and score achieved 100%',
            ]
        ];
    }

    private function createCertificate($groupCode)
    {
        $certificate = new QualificationAnnualCertificate();
        $certificate->setCertificateNumber(self::CERTIFICATE_NUMBER)
            ->setScore(self::SCORE)
            ->setVehicleClassGroup(
                (new VehicleClassGroup())
                    ->setCode($groupCode)
            )
            ->setDateAwarded(new \DateTime(self::DATE_AWARDED));

        return $certificate;
    }

    private function createIdentity()
    {
        $identity = XMock::of(MotIdentityInterface::class);
        $identity->expects($this->any())
            ->method("getUsername")
            ->willReturn(self::USERNAME);

        return $identity;
    }
}