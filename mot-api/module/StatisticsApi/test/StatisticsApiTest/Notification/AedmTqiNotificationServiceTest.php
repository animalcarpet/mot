<?php
namespace Dvsa\Mot\Api\StatisticsApiTest\Notification;

use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\Notification\Service\AedmTqiNotificationService;
use DvsaAuthentication\Identity;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use DvsaCommon\Date\DateTimeHolderInterface;
use DvsaCommonTest\Date\TestDateTimeHolder;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Repository\PersonRepository;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Database\Transaction;
use DvsaEntities\Repository\NotificationRepository;
use DvsaEntities\Repository\TqiRfrCountRepository;
use Zend\ServiceManager\ServiceManager;

class AedmTqiNotificationServiceTest extends \PHPUnit_Framework_TestCase
{
    /** @var AedmTqiNotificationService */
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
        $identity->method("getUserId")->willReturn(66);

        $this
            ->identityProvider
            ->method("getIdentity")
            ->willReturn($identity);

        $this->sut = new AedmTqiNotificationService(
            $this->personRepository,
            $this->authorisationService,
            $this->dateTimeHolder,
            $this->transaction,
            $this->notificationRepository,
            $this->tqiRfrCountRepository,
            $this->identityProvider
        );
    }

    public function test_notificationAreNotSent_whenTqiCacheDoesNotExist()
    {
        $this->expectException(\LogicException::class);

        $this
            ->tqiRfrCountRepository
            ->method("checkIfThereAreDataForPeriod")
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
            ->method("findAllAEDandAEDMS")
            ->willReturn([
                [
                    AedmTqiNotificationService::FIELD_ID => 1,
                    AedmTqiNotificationService::FIELD_PERSON_ID => 105,
                    "orgNmae" => "Hot Wheels",
                    "orgRef" => "AEREF 1234"
                ]
            ])
        ;

        $this->notificationRepository->expects($this->atLeastOnce())->method("saveNotificationWithFields");

        $this->sut->execute();
    }
}
