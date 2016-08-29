<?php

namespace Dvsa\Mot\Frontend\SecurityCardModuleTest\CardOrder\Service;

use Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\Service\OrderSecurityCardEventService;
use Dvsa\Mot\Frontend\AuthenticationModule\Model\Identity;
use DvsaCommon\Date\DateTimeHolder;
use DvsaCommon\Enum\EventTypeCode;
use DvsaCommon\HttpRestJson\Client;
use Core\Service\MotFrontendIdentityProvider;
use DvsaCommonTest\TestUtils\XMock;

class OrderSecurityCardEventServiceTest extends \PHPUnit_Framework_TestCase
{
    const USERNAME = 'test-user';
    const ID = 105;
    const ADDRESS = '1 test road, testTown';
    const DATETIME = '2014-01-13 10:00:00.000000';
    const TIME_FORMAT = '10:00am';

    private $jsonClient;

    private $identityProvider;

    private $dateTimeHolder;

    public function setup()
    {
        $this->jsonClient = XMock::of(Client::class);
        $this->identityProvider = XMock::of(MotFrontendIdentityProvider::class);
        $this->dateTimeHolder = XMock::of(DateTimeHolder::class);
    }

    public function testCorrectDataSentToApi()
    {
        $this->withIdentity();

        $this->jsonClient
            ->expects($this->once())
            ->method('post')
            ->with('event/add/person/' . self::ID, $this->getMockPostData());

        $this->dateTimeHolder
            ->expects($this->once())
            ->method('getUserCurrent')
            ->willReturn(new \DateTime(self::DATETIME));

        $actual = $this->createService()->createEvent(self::ID, self::ADDRESS);
        $this->assertTrue($actual);
    }

    private function createService()
    {
        return new OrderSecurityCardEventService(
            $this->jsonClient,
            $this->identityProvider,
            $this->dateTimeHolder
        );
    }

    private function withIdentity()
    {
        $identity = new Identity();
        $identity
            ->setUsername(self::USERNAME)
            ->setUserId(self::ID);

        $this->identityProvider
            ->expects($this->once())
            ->method('getIdentity')
            ->willReturn($identity);
    }

    private function getMockPostData()
    {
        return [
            'eventTypeCode' => EventTypeCode::CREATE_SECURITY_CARD_ORDER,
            'description' => 'Security card ordered by ' . self::USERNAME . ' at ' . self::TIME_FORMAT .' to ' . self::ADDRESS,
        ];
    }

}