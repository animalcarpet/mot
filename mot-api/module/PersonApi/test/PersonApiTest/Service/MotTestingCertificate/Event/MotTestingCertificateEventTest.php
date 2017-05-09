<?php

namespace PersonApiTest\Service\MotTestingCertificate\Event;

use DvsaCommon\Enum\EventTypeCode;
use DvsaCommon\Enum\VehicleClassGroupCode;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Entity\QualificationAward;
use DvsaEntities\Entity\VehicleClassGroup;
use DvsaEntities\Repository\EventPersonMapRepository;
use DvsaEventApi\Service\EventService;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use DvsaCommon\Date\DateTimeHolder;
use DvsaCommon\Constants\EventDescription;
use DvsaCommon\Date\DateTimeDisplayFormat;
use DvsaCommon\Auth\MotIdentityInterface;
use PersonApi\Service\MotTestingCertificate\Event\MotTestingCertificateEvent;

class MotTestingCertificateEventTest extends \PHPUnit_Framework_TestCase
{
    /** @var EventService */
    private $eventService;
    /** @var MotIdentityProviderInterface */
    private $motIdentityProvider;

    private $eventServiceSpy;

    public function setUp()
    {
        $this->eventService = XMock::of(EventService::class);

        $identity = XMock::of(MotIdentityInterface::class);
        $identity
            ->expects($this->any())
            ->method('getUsername')
            ->willReturn('username1');

        $this->motIdentityProvider = XMock::of(MotIdentityProviderInterface::class);
        $this
            ->motIdentityProvider
            ->expects($this->any())
            ->method('getIdentity')
            ->willReturn($identity);

        $this
            ->eventService
            ->expects($this->eventServiceSpy = $this->once())
            ->method('addEvent');
    }

    /**
     * @dataProvider getCertificates
     */
    public function testSendCreateMotTestingCertificateEvent(QualificationAward $motTestingCertificate)
    {
        $this->createMotTestingCertificateEvent()->sendCreateEvent($motTestingCertificate);

        if ($motTestingCertificate->getVehicleClassGroup()->getCode() === VehicleClassGroupCode::BIKES) {
            $expectedType = EventTypeCode::GROUP_A_TESTER_QUALIFICATION;
        } else {
            $expectedType = EventTypeCode::GROUP_B_TESTER_QUALIFICATION;
        }

        $this->assert(EventDescription::MOT_TESTING_QUALIFICATION_CERTIFICATE_RECORD, $expectedType, $motTestingCertificate);
    }

    /**
     * @dataProvider getCertificates
     */
    public function testSendUpdateMotTestingCertificateEvent(QualificationAward $motTestingCertificate)
    {
        $this->createMotTestingCertificateEvent()->sendUpdateEvent($motTestingCertificate);

        if ($motTestingCertificate->getVehicleClassGroup()->getCode() === VehicleClassGroupCode::BIKES) {
            $expectedType = EventTypeCode::GROUP_A_TESTER_QUALIFICATION;
        } else {
            $expectedType = EventTypeCode::GROUP_B_TESTER_QUALIFICATION;
        }

        $this->assert(EventDescription::MOT_TESTING_QUALIFICATION_CERTIFICATE_EDIT, $expectedType, $motTestingCertificate);
    }

    /**
     * @dataProvider getCertificates
     */
    public function testSendRemoveMotTestingCertificateEvent(QualificationAward $motTestingCertificate)
    {
        $this->createMotTestingCertificateEvent()->sendRemoveEvent($motTestingCertificate);

        if ($motTestingCertificate->getVehicleClassGroup()->getCode() === VehicleClassGroupCode::BIKES) {
            $expectedType = EventTypeCode::REMOVAL_OF_GROUP_A_CERTIFICATE;
        } else {
            $expectedType = EventTypeCode::REMOVAL_OF_GROUP_B_CERTIFICATE;
        }

        $this->assert(EventDescription::MOT_TESTING_QUALIFICATION_CERTIFICATE_REMOVE, $expectedType, $motTestingCertificate);
    }

    private function assert($description, $expectedEventType, QualificationAward $motTestingCertificate)
    {
        $eventType = $this->getParamFromSpy(0);
        $eventDescription = $this->getParamFromSpy(1);

        $expectedDescription = sprintf(
            $description,
            $motTestingCertificate->getVehicleClassGroup()->getCode(),
            $this->motIdentityProvider->getIdentity()->getUsername(),
            $motTestingCertificate->getCertificateNumber(),
            DateTimeDisplayFormat::date($motTestingCertificate->getDateOfQualification())
        );

        $this->assertEquals($expectedDescription, $eventDescription);
        $this->assertEquals($expectedEventType, $eventType);
    }

    private function getParamFromSpy($paramIndex)
    {
        $spyInvocations = $this->eventServiceSpy->getInvocations();
        $lastInvocation = end($spyInvocations);

        return $lastInvocation->parameters[$paramIndex];
    }

    private function createMotTestingCertificateEvent()
    {
        return new MotTestingCertificateEvent(
            $this->motIdentityProvider,
            $this->eventService,
            XMock::of(EventPersonMapRepository::class),
            new DateTimeHolder()
        );
    }

    public function getCertificates()
    {
        $motTestingCertificateA = new QualificationAward();
        $motTestingCertificateA
            ->setVehicleClassGroup((new VehicleClassGroup())->setCode(VehicleClassGroupCode::BIKES))
            ->setCertificateNumber('certNum123')
            ->setDateOfQualification(new \DateTime('2012-09-09'))
            ;

        $motTestingCertificateB = new QualificationAward();
        $motTestingCertificateB
            ->setVehicleClassGroup((new VehicleClassGroup())->setCode(VehicleClassGroupCode::CARS_ETC))
            ->setCertificateNumber('certNum123')
            ->setDateOfQualification(new \DateTime('2012-09-09'))
        ;

        return [
            [
                $motTestingCertificateA,
            ],
            [
                $motTestingCertificateB,
            ],
        ];
    }
}
