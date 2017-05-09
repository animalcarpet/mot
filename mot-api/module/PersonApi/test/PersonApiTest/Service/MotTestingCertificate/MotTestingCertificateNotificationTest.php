<?php

namespace PersonApiTest\Service\MotTestingCertificate;

use DvsaCommon\Auth\MotIdentityProviderInterface;
use DvsaCommon\Enum\VehicleClassGroupCode;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Entity\QualificationAward;
use DvsaEntities\Entity\VehicleClassGroup;
use NotificationApi\Service\NotificationService;
use PersonApi\Service\MotTestingCertificate\MotTestingCertificateNotification;
use DvsaEntities\Entity\Person;
use DvsaCommon\Auth\MotIdentityInterface;
use NotificationApi\Dto\Notification;
use DvsaCommon\Date\DateTimeDisplayFormat;

class MotTestingCertificateNotificationTest extends \PHPUnit_Framework_TestCase
{
    const RECIPIENT_ID = 1;
    const USERNAME = 'username1';

    /** @var MotIdentityProviderInterface */
    private $motIdentityProvider;
    /** @var NotificationService */
    private $notificationService;

    private $spy;

    public function setUp()
    {
        $this->motIdentityProvider = XMock::of(MotIdentityProviderInterface::class);

        $identity = XMock::of(MotIdentityInterface::class);
        $identity
            ->expects($this->any())
            ->method('getUsername')
            ->willReturn(self::USERNAME);

        $this->motIdentityProvider = XMock::of(MotIdentityProviderInterface::class);
        $this
            ->motIdentityProvider
            ->expects($this->any())
            ->method('getIdentity')
            ->willReturn($identity);

        $this->notificationService = XMock::of(NotificationService::class);

        $this
            ->notificationService
            ->expects($this->spy = $this->any())
            ->method('add')
            ->willReturn(1);
    }

    /**
     * @dataProvider getQualificationAward
     */
    public function testSendRemoveNotification(QualificationAward $qualificationAward)
    {
        $this->createService()->sendRemoveNotification($qualificationAward);

        $this->assertEquals(Notification::TEMPLATE_MOT_TESTING_CERTIFICATE_REMOVAL, $this->getParamFromSpy('template'));
        $this->assertEquals(self::RECIPIENT_ID, $this->getParamFromSpy('recipient'));
        $this->assertEquals($qualificationAward->getVehicleClassGroup()->getCode(), $this->getParamFromSpy('fields')['group']);
        $this->assertEquals($qualificationAward->getCertificateNumber(), $this->getParamFromSpy('fields')['certificateNumber']);
        $this->assertEquals(DateTimeDisplayFormat::date($qualificationAward->getDateOfQualification()), $this->getParamFromSpy('fields')['dateOfQualification']);
    }

    /**
     * @dataProvider getQualificationAward
     */
    public function testSendCreateNotification(QualificationAward $qualificationAward)
    {
        $this->createService()->sendCreateNotification($qualificationAward);

        $this->assertEquals(Notification::TEMPLATE_MOT_TESTING_CERTIFICATE_CREATED, $this->getParamFromSpy('template'));
        $this->assertEquals(self::RECIPIENT_ID, $this->getParamFromSpy('recipient'));
        $this->assertEquals($qualificationAward->getVehicleClassGroup()->getCode(), $this->getParamFromSpy('fields')['group']);
        $this->assertEquals($qualificationAward->getCertificateNumber(), $this->getParamFromSpy('fields')['certificateNumber']);
        $this->assertEquals(DateTimeDisplayFormat::date($qualificationAward->getDateOfQualification()), $this->getParamFromSpy('fields')['dateOfQualification']);
    }

    private function createService()
    {
        return new MotTestingCertificateNotification(
            $this->motIdentityProvider,
            $this->notificationService
        );
    }

    public function getQualificationAward()
    {
        $certificateA = $this->createQualificationAward(VehicleClassGroupCode::BIKES);
        $certificateB = $this->createQualificationAward(VehicleClassGroupCode::CARS_ETC);

        return [
            [
                $certificateA,
            ],
            [
                $certificateB,
            ],
        ];
    }

    private function createQualificationAward($group)
    {
        $certificate = new QualificationAward();
        $certificate
            ->setId(1)
            ->setVehicleClassGroup((new VehicleClassGroup())->setCode($group))
            ->setDateOfQualification(new \DateTime('2012-02-05'))
            ->setPerson((new Person())->setId(self::RECIPIENT_ID))
            ->setCertificateNumber('cert123')
        ;

        return $certificate;
    }

    private function getParamFromSpy($name)
    {
        $spyInvocations = $this->spy->getInvocations();
        $lastInvocation = end($spyInvocations);
        $parameters = $lastInvocation->parameters[0];

        return $parameters[$name];
    }
}
