<?php
namespace Dvsa\Mot\Api\StatisticsApiTest\Notification;

use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\Notification\Service\SiteManagerTqiNotificationService;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\Notification\Service\TqiNotificationService;
use DvsaAuthentication\Identity;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use DvsaCommon\Date\DateTimeHolderInterface;
use DvsaCommonTest\Date\TestDateTimeHolder;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Repository\PersonRepository;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use NotificationApi\Dto\Notification;
use DvsaCommon\Database\Transaction;
use DvsaEntities\Repository\NotificationRepository;
use DvsaEntities\Repository\TqiRfrCountRepository;
use Zend\ServiceManager\ServiceManager;

class SiteManagerTqiNotificationServiceTest extends \PHPUnit_Framework_TestCase
{
    const SITE_REF = 'AEREF1234';
    const SITE_NAME = '"VTS 1"';
    const ADDRESS_1 = "a1";
    const ADDRESS_2 = "a2";
    const ADDRESS_3 = "a3";
    const ADDRESS_4 = "a4";
    const TOWN = "town";
    const POSTCODE = "postcode";
    const CREATOR_ID = 66;
    const PERSON_ID = 105;

    /** @var string */
    private $address;
    /** @var SiteManagerTqiNotificationService */
    private $sut;
    /** @var PersonRepository | \PHPUnit_Framework_MockObject_MockObject */
    private $personRepository;
    /** @var MotAuthorisationServiceInterface | \PHPUnit_Framework_MockObject_MockObject */
    private $authorisationService;
    /** @var DateTimeHolderInterface */
    private $dateTimeHolder;
    /** @var Transaction | \PHPUnit_Framework_MockObject_MockObject */
    private $transaction;
    /** @var NotificationRepository | \PHPUnit_Framework_MockObject_MockObject */
    private $notificationRepository;
    /** @var TqiRfrCountRepository | \PHPUnit_Framework_MockObject_MockObject */
    private $tqiRfrCountRepository;
    /** @var MotIdentityProviderInterface | \PHPUnit_Framework_MockObject_MockObject */
    private $identityProvider;

    protected function setUp()
    {
        $this->personRepository = XMock::of(PersonRepository::class);
        $this->authorisationService = XMock::of(MotAuthorisationServiceInterface::class);
        $this->dateTimeHolder = new TestDateTimeHolder(new \DateTime());
        $this->transaction = XMock::of(Transaction::class);
        $this->notificationRepository = XMock::of(NotificationRepository::class);
        $this->tqiRfrCountRepository = XMock::of(TqiRfrCountRepository::class);
        $this->identityProvider = XMock::of(MotIdentityProviderInterface::class);

        /** @var Identity | \PHPUnit_Framework_MockObject_MockObject */
        $identity = XMock::of(Identity::class);
        $identity->method("getUserId")->willReturn(self::CREATOR_ID);

        $this
            ->identityProvider
            ->method("getIdentity")
            ->willReturn($identity);

        $this->sut = new SiteManagerTqiNotificationService(
            $this->personRepository,
            $this->authorisationService,
            $this->dateTimeHolder,
            $this->transaction,
            $this->notificationRepository,
            $this->tqiRfrCountRepository,
            $this->identityProvider
        );

        $this->address = implode(', ', [self::ADDRESS_1, self::ADDRESS_2, self::ADDRESS_3, self::ADDRESS_4, self::TOWN, self::POSTCODE]);
    }

    public function test_notificationAreNotSent_whenTqiCacheDoesNotExist()
    {
        $this->expectException(\LogicException::class);

        $this
            ->tqiRfrCountRepository
            ->method("checkIfThereAreDataForPeriod")
            ->willReturn(0);

        $this
            ->notificationRepository
            ->method("checkIfNotificationsHasBeenSent")
            ->willReturn(0);

        $this->sut->execute();
    }

    public function test_sendNotification_whenNotificationsHaveNotBeenSent()
    {
        $this
            ->tqiRfrCountRepository
            ->method("checkIfThereAreDataForPeriod")
            ->willReturn(1);

        $this
            ->notificationRepository
            ->method("checkIfNotificationsHasBeenSent")
            ->willReturn(0);

        $this
            ->personRepository
            ->method("findAllSiteManagers")
            ->willReturn([
                [
                    TqiNotificationService::FIELD_ID => 1,
                    TqiNotificationService::FIELD_PERSON_ID => self::PERSON_ID,
                    "siteName" => self::SITE_NAME,
                    "siteRef" => self::SITE_REF
                ]
            ]);

        $this->notificationRepository->expects($this->atLeastOnce())->method("saveNotificationWithFields");

        $this->sut->execute();
    }

    public function test_sendNotification_willFormatNotificationFields()
    {
        $data = [
            [
                TqiNotificationService::FIELD_ID => 1,
                TqiNotificationService::FIELD_PERSON_ID => self::PERSON_ID,
                "siteName" => self::SITE_NAME,
                "siteRef" => self::SITE_REF,
                "addressLine1" => self::ADDRESS_1,
                "addressLine2" => self::ADDRESS_2,
                "addressLine3" => self::ADDRESS_3,
                "addressLine4" => self::ADDRESS_4,
                "town" => self::TOWN,
                "postcode" => self::POSTCODE
            ]
        ];

        $this
            ->tqiRfrCountRepository
            ->method("checkIfThereAreDataForPeriod")
            ->willReturn(1);
        $this
            ->notificationRepository
            ->method("checkIfNotificationsHasBeenSent")
            ->willReturn(0);
        $this
            ->personRepository
            ->method("findAllSiteManagers")
            ->willReturn($data);

        $this->notificationRepository->expects($this->once())
            ->method("saveNotificationWithFields")
            ->with(
                $this->equalTo(36, $delta = 0.0, $maxDepth = 10, $canonicalize = true),
                self::PERSON_ID,
                self::CREATOR_ID,
                $this->equalTo($this->getNotificationArray($data[0]), $delta = 0.0, $maxDepth = 10, $canonicalize = true)
            );

        $this->sut->execute();
    }

    private function getNotificationArray(array $data)
    {
        $id = $data[TqiNotificationService::FIELD_ID];

        unset($data[TqiNotificationService::FIELD_ID]);
        unset($data[TqiNotificationService::FIELD_PERSON_ID]);

        return [
            '/vehicle-testing-station/' . $id . '/test-quality',
            $id,
            self::SITE_NAME,
            self::SITE_REF,
            $this->address
        ];
    }
}
