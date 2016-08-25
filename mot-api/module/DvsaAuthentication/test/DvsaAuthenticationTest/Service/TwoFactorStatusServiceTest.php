<?php
namespace DvsaAuthenticationTest\Service;

use Dvsa\Mot\ApiClient\Resource\Collection;
use Dvsa\Mot\ApiClient\Resource\Item\SecurityCardOrder;
use Dvsa\Mot\ApiClient\Service\AuthorisationService;
use DvsaAuthentication\Identity;
use DvsaAuthentication\Service\TwoFactorStatusService;
use DvsaAuthentication\TwoFactorStatus;
use DvsaEntities\Entity\AuthenticationMethod;
use DvsaEntities\Entity\Person;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Repository\PersonRepository;
use PHPUnit_Framework_TestCase;
use stdClass;

class TwoFactorStatusServiceTest extends PHPUnit_Framework_TestCase
{
    private $authorisationService;

    private $personRepository;

    public function setUp()
    {
        $this->authorisationService = XMock::of(AuthorisationService::class);
        $this->personRepository = XMock::of(PersonRepository::class);
    }

    public function testStatusIsAwaitingCardOrderIfIdentityNotTwoFaAndHasNoCardOrder()
    {
        $this
            ->withTwoFactorInactivePerson()
            ->withNoCardOrder();
        
        $status = $this->buildService()->getStatusForPerson(new Person());

        $this->assertEquals(TwoFactorStatus::AWAITING_CARD_ORDER, $status);
    }

    public function testStatusIsAwaitingCardOrderIfPersonNotTwoFaAndHasNoCardOrder()
    {
        $this->withNoCardOrder();

        $identity = $this->getTwoFactorInactiveIdentity();

        $status = $this->buildService()->getStatusForIdentity($identity);

        $this->assertEquals(TwoFactorStatus::AWAITING_CARD_ORDER, $status);
    }

    public function testStatusIsAwaitingCardActivationIfIdentityIsNotTwoFaButHasCardOrder()
    {
        $this
            ->withTwoFactorInactivePerson()
            ->withCardOrder();

        $status = $this->buildService()->getStatusForPerson(new Person());

        $this->assertEquals(TwoFactorStatus::AWAITING_CARD_ACTIVATION, $status);
    }

    public function testStatusIsAwaitingCardActivationIfPersonIsNotTwoFaButHasCardOrder()
    {
        $this->withCardOrder();

        $identity = $this->getTwoFactorInactiveIdentity();

        $status = $this->buildService()->getStatusForIdentity($identity);

        $this->assertEquals(TwoFactorStatus::AWAITING_CARD_ACTIVATION, $status);
    }

    public function testStatusIsActiveIfIdentityIsTwoFa()
    {
        $this->withTwoFactorActivePerson();

        $status = $this->buildService()->getStatusForPerson(new Person());

        $this->assertEquals(TwoFactorStatus::ACTIVE, $status);
    }

    public function testStatusIsActiveIfPersonIsTwoFa()
    {
        $this->withCardOrder();

        $identity = $this->getTwoFactorActiveIdentity();

        $status = $this->buildService()->getStatusForIdentity($identity);

        $this->assertEquals(TwoFactorStatus::ACTIVE, $status);
    }

    private function withTwoFactorActivePerson()
    {
        $this->personRepository
            ->expects($this->any())
            ->method('findIdentity')
            ->willReturn($this->getTwoFactorActivePerson());

        return $this;
    }

    private function withTwoFactorInactivePerson()
    {
        $this->personRepository
            ->expects($this->any())
            ->method('findIdentity')
            ->willReturn($this->getTwoFactorInactivePerson());

        return $this;
    }

    private function getTwoFactorActivePerson()
    {
        $person = new Person();
        $person->setAuthenticationMethod(
            (new AuthenticationMethod())->setCode(AuthenticationMethod::CARD_CODE)
        );

        return $person;
    }

    private function getTwoFactorInactivePerson()
    {
        $person = new Person();
        $person->setAuthenticationMethod(
            (new AuthenticationMethod())->setCode(AuthenticationMethod::PIN_CODE)
        );

        return $person;
    }
    
    private function getTwoFactorActiveIdentity()
    {
        return new Identity($this->getTwoFactorActivePerson());
    }

    private function getTwoFactorInactiveIdentity()
    {
        return new Identity($this->getTwoFactorInactivePerson());
    }

    private function withCardOrder()
    {
        $order = new stdClass();
        $order->fullName = 'Some Tester';
        $order->submittedOn = '2016-06-06';

        $securityCardOrders = new Collection([$order], SecurityCardOrder::class);

        $this->authorisationService
            ->expects($this->any())
            ->method("getSecurityCardOrders")
            ->willReturn($securityCardOrders);

        return $this;
    }

    private function withNoCardOrder()
    {
        $securityCardOrders = new Collection([], SecurityCardOrder::class);

        $this->authorisationService
            ->expects($this->any())
            ->method("getSecurityCardOrders")
            ->willReturn($securityCardOrders);

        return $this;
    }

    private function buildService()
    {
        return new TwoFactorStatusService(
            $this->authorisationService,
            $this->personRepository
        );
    }
}
