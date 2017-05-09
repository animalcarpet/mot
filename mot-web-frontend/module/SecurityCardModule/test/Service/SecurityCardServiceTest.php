<?php

namespace Dvsa\Mot\Frontend\SecurityCardTest\Service;

use Dvsa\Mot\ApiClient\Exception\ResourceNotFoundException;
use Dvsa\Mot\ApiClient\Resource\Collection;
use Dvsa\Mot\ApiClient\Resource\Item\SecurityCard;
use Dvsa\Mot\ApiClient\Resource\Item\SecurityCardOrder;
use Dvsa\Mot\ApiClient\Resource\Item\SecurityCardOrderCreate;
use Dvsa\Mot\ApiClient\Service\AuthorisationService;
use Dvsa\Mot\Frontend\SecurityCardModule\Service\SecurityCardService;
use Dvsa\Mot\Frontend\SecurityCardModule\Service\TwoFactorNominationNotificationService;
use DvsaCommonTest\TestUtils\XMock;
use PHPUnit_Framework_TestCase;

class SecurityCardServiceTest extends PHPUnit_Framework_TestCase
{
    /** @var AuthorisationService */
    private $authorisationService;

    /**
     * @var TwoFactorNominationNotificationService
     */
    private $nominationService;

    public function setUp()
    {
        $this->authorisationService = XMock::of(AuthorisationService::class);
        $this->nominationService = XMock::of(TwoFactorNominationNotificationService::class);
    }

    public function testSecurityCardForUserRetrievedFromApiClient()
    {
        $authorisationCardService = XMock::of(AuthorisationService::class);

        $securityCard = new SecurityCard(new \stdClass());

        $authorisationCardService
            ->expects($this->any())
            ->method('getSecurityCardForUser')
            ->with('tester1')
            ->willReturn($securityCard);

        $securityCardService = new SecurityCardService($authorisationCardService, $this->nominationService);
        $actualSecurityCard = $securityCardService->getSecurityCardForUser('tester1');

        $this->assertEquals($securityCard, $actualSecurityCard);
    }

    public function testNullReturnedWhenApiCanNotFindCard()
    {
        $authorisationCardService = XMock::of(AuthorisationService::class);

        $authorisationCardService
            ->expects($this->any())
            ->method('getSecurityCardForUser')
            ->with('tester1')
            ->will($this->throwException(new ResourceNotFoundException()));

        $securityCardService = new SecurityCardService($authorisationCardService, $this->nominationService);
        $actualSecurityCard = $securityCardService->getSecurityCardForUser('tester1');

        $this->assertNull($actualSecurityCard);
    }

    public function testSecurityCardOrdersForUserRetrievedFromApiClient()
    {
        $authorisationCardService = XMock::of(AuthorisationService::class);

        $securityCardOrders = new Collection([new \stdClass()], SecurityCardOrder::class);

        $authorisationCardService
            ->expects($this->any())
            ->method('getSecurityCardOrders')
            ->with('tester1')
            ->willReturn($securityCardOrders);

        $securityCardService = new SecurityCardService($authorisationCardService, $this->nominationService);
        $actualSecurityCardOrders = $securityCardService->getSecurityCardOrdersForUser('tester1');

        $this->assertEquals($securityCardOrders, $actualSecurityCardOrders);
    }

    public function testMostRecentSecurityCardOrderForUserRetrievesMostRecentOrder()
    {
        $this->withCardOrdersSubmittedOn(['2016-01-01 12:00:00', '2016-02-01 12:00:00', '2016-03-01 12:00:00']);

        $securityCardService = new SecurityCardService($this->authorisationService, $this->nominationService);
        $actualCardOrder = $securityCardService->getMostRecentSecurityCardOrderForUser('tester1');

        $this->assertEquals('2016-03-01 12:00:00', $actualCardOrder->getSubmittedOn());
    }

    public function testCardOrderSendsNominationNotificationsIfOrderIsSuccessful()
    {
        $this->withSuccessfulCardOrder();

        $this->nominationService
            ->expects($this->once())
            ->method('sendNotificationsForPendingNominations');

        $securityCardService = new SecurityCardService($this->authorisationService, $this->nominationService);
        $securityCardService->orderNewCard('tester1', 1, $this->getAddressForCardOrder());
    }

    public function testCardOrderSendsNoNominationNotificationsIfOrderIsUnsuccessful()
    {
        $this->withUnsuccessfulCardOrder();

        $this->nominationService
            ->expects($this->never())
            ->method('sendNotificationsForPendingNominations');

        $securityCardService = new SecurityCardService($this->authorisationService, $this->nominationService);
        $securityCardService->orderNewCard('tester1', 1, $this->getAddressForCardOrder());
    }

    private function withSuccessfulCardOrder()
    {
        $orderResponse = new SecurityCardOrderCreate(new \stdClass());

        $this->authorisationService
            ->expects($this->any())
            ->method('orderSecurityCard')
            ->willReturn($orderResponse);

        return $this;
    }

    private function withUnsuccessfulCardOrder()
    {
        $this->authorisationService
            ->expects($this->any())
            ->method('orderSecurityCard')
            ->willReturn(null);

        return $this;
    }

    private function withCardOrdersSubmittedOn(array $dates)
    {
        $orderData = [];

        foreach ($dates as $date) {
            $orderData[] = $this->buildCardOrder('Some Tester', $date);
        }

        $securityCardOrders = new Collection($orderData, SecurityCardOrder::class);

        $this->authorisationService
            ->expects($this->any())
            ->method('getSecurityCardOrders')
            ->willReturn($securityCardOrders);

        return $this;
    }

    private function buildCardOrder($name, $submittedOn)
    {
        $order = new \stdClass();
        $order->fullName = $name;
        $order->submittedOn = $submittedOn;

        return $order;
    }

    private function getAddressForCardOrder()
    {
        return [
            'vtsName' => '',
            'address1' => '',
            'address2' => '',
            'address3' => '',
            'townOrCity' => '',
            'postcode' => '',
        ];
    }
}
