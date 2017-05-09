<?php

namespace PersonApiTest\Service;

use DvsaCommon\Auth\MotIdentityProviderInterface;
use DvsaCommon\Auth\MotIdentityInterface;
use DvsaCommon\Configuration\MotConfig;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Entity\PasswordDetail;
use DvsaCommonApiTest\Service\AbstractServiceTestCase;
use DvsaEntities\Repository\PasswordDetailRepository;
use PersonApi\Service\PasswordExpiryNotificationService;
use PersonApi\Service\PasswordExpiryService;

class PasswordExpiryServiceTest extends AbstractServiceTestCase
{
    /**
     * @var MotIdentityProviderInterface
     */
    private $identityProvider;

    private $passwordExpiryNotificationService;

    private $config;

    public function setUp()
    {
        /** @var PasswordExpiryNotificationService $passwordExpiryNotificationService */
        $passwordExpiryNotificationService = $this->getMockWithDisabledConstructor(PasswordExpiryNotificationService::class);

        $this->passwordExpiryNotificationService = $passwordExpiryNotificationService;

        /** @var MotConfig $config */
        $config = $this->getMockWithDisabledConstructor(MotConfig::class);
        $config
            ->expects($this->any())
            ->method('get')
            ->willReturnCallback(function ($param) {
                if ($param === 'password_expiry_notification_days') {
                    return [7, 3, 2, 1];
                }
            });
        $this->config = $config;

        $identity = XMock::of(MotIdentityInterface::class);
        $identity
            ->expects($this->any())
            ->method('getUserId')
            ->willReturn(1);

        $identityProvider = XMock::of(MotIdentityProviderInterface::class);
        $identityProvider
            ->expects($this->any())
            ->method('getIdentity')
            ->willReturn($identity);

        $this->identityProvider = $identityProvider;
    }

    /**
     * @dataProvider dataProvider
     */
    public function testNotificationSent($hoursToExpire, $hoursNotificationSentBeforeExpiry, $expectedResult)
    {
        $passwordExpiresOn = new \DateTime();
        $passwordExpiresOn->add(new \DateInterval("PT{$hoursToExpire}H"));
        $passwordNotificationSentOn = clone $passwordExpiresOn;
        $passwordNotificationSentOn->sub(new \DateInterval("PT{$hoursNotificationSentBeforeExpiry}H"));

        $data = [];
        $data['expiry-date'] = $passwordExpiresOn->format(\DateTime::ISO8601);

        $passwordDetail = new PasswordDetail();
        $passwordDetail->setPasswordNotificationSentDate($passwordNotificationSentOn);

        /** @var PasswordDetailRepository $passwordDetailRepository */
        $passwordDetailRepository = $this->getMockWithDisabledConstructor(PasswordDetailRepository::class);
        $passwordDetailRepository
            ->expects($this->any())
            ->method('findPasswordNotificationSentDateByPersonId')
            ->willReturn($passwordNotificationSentOn)
        ;

        if (is_null($expectedResult)) {
            $this
                ->passwordExpiryNotificationService
                ->expects($this->exactly(0))
                ->method('send');
        } else {
            $this
                ->passwordExpiryNotificationService
                ->expects($this->exactly(1))
                ->method('send');
        }

        $passwordExpiryService = $this->createPasswordExpiryService($passwordDetailRepository);
        $passwordExpiryService->notifyUserIfPasswordExpiresSoon($data);
    }

    public function dataProvider()
    {
        return [
            [1,    36,   1],
            [1,    23,   null],
            [1,    25,   1],
            [20,   25,   1],
            [24 * 4, 24 * 6, null],
            [24 * 3, 24 * 6, null],
            [24 * 2, 24 * 6, 1],
            [24 * 1, 24 * 6, 1],
            [24 * 300, 24 * 30, null],
        ];
    }

    private function createPasswordExpiryService($passwordDetailRepository)
    {
        return new PasswordExpiryService(
            $this->passwordExpiryNotificationService,
            $passwordDetailRepository,
            $this->config,
            $this->identityProvider
        );
    }
}
