<?php
namespace DvsaAuthenticationTest\Service;

use Dvsa\Mot\ApiClient\Resource\Collection;
use Dvsa\Mot\ApiClient\Resource\Item\SecurityCardOrder;
use Dvsa\Mot\ApiClient\Service\AuthorisationService as AuthorisationServiceClient;
use DvsaAuthentication\Identity;
use DvsaAuthentication\Service\TwoFactorStatusService;
use DvsaAuthentication\TwoFactorStatus;
use DvsaAuthorisation\Service\AuthorisationService;
use DvsaCommon\Enum\RoleCode;
use DvsaCommon\Model\PersonAuthorization;
use DvsaCommon\Model\TradeRole;
use DvsaEntities\Entity\AuthenticationMethod;
use DvsaEntities\Entity\Person;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Repository\PersonRepository;
use PHPUnit_Framework_TestCase;
use stdClass;

class TwoFactorStatusServiceTest extends PHPUnit_Framework_TestCase
{
    private $authorisationServiceClient;

    private $authorisationService;

    private $personRepository;

    public function setUp()
    {
        $this->authorisationServiceClient = XMock::of(AuthorisationServiceClient::class);
        $this->authorisationService = XMock::of(AuthorisationService::class);
        $this->personRepository = XMock::of(PersonRepository::class);
    }

    public function testStatusIsAwaitingCardOrderIfIdentityNotTwoFaAndHasNoCardOrder()
    {
        $this
            ->withTwoFactorInactivePerson()
            ->withNoExistingNomineeRoles()
            ->withNoCardOrder();
        
        $status = $this->buildService()->getStatusForPerson(new Person());

        $this->assertEquals(TwoFactorStatus::AWAITING_CARD_ORDER, $status);
    }

    public function testStatusIsAwaitingCardOrderIfPersonNotTwoFaAndHasNoCardOrder()
    {
        $this
            ->withNoExistingNomineeRoles()
            ->withNoCardOrder();

        $identity = $this->getTwoFactorInactiveIdentity();

        $status = $this->buildService()->getStatusForIdentity($identity);

        $this->assertEquals(TwoFactorStatus::AWAITING_CARD_ORDER, $status);
    }

    public function testStatusIsAwaitingCardActivationIfIdentityIsNotTwoFaButHasCardOrder()
    {
        $this
            ->withTwoFactorInactivePerson()
            ->withNoExistingNomineeRoles()
            ->withCardOrder();

        $status = $this->buildService()->getStatusForPerson(new Person());

        $this->assertEquals(TwoFactorStatus::AWAITING_CARD_ACTIVATION, $status);
    }

    public function testStatusIsAwaitingCardActivationIfPersonIsNotTwoFaButHasCardOrder()
    {
        $this
            ->withCardOrder()
            ->withNoExistingNomineeRoles();

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

    public function testStatusIsInactiveTradeUserIfPersonAlreadyHasTradeRole()
    {
        $this
            ->withTwoFactorInactivePerson()
            ->withNoCardOrder()
            ->withExistingNomineeTradeRoles();

        $status = $this->buildService()->getStatusForPerson(new Person());

        $this->assertEquals(TwoFactorStatus::INACTIVE_TRADE_USER, $status);
    }

    public function testStatusIsInactiveTradeUserIfIdentityAlreadyHasTradeRole()
    {
        $this
            ->withNoCardOrder()
            ->withExistingNomineeTradeRoles();

        $identity = $this->getTwoFactorInactiveIdentity();

        $status = $this->buildService()->getStatusForIdentity($identity);

        $this->assertEquals(TwoFactorStatus::INACTIVE_TRADE_USER, $status);
    }

    public function testStatusIsAwaitingCardOrderIfIdentityAlreadyHasNonTradeRole()
    {
        $this
            ->withNoCardOrder()
            ->withExistingNomineeNonTradeRoles();

        $identity = $this->getTwoFactorInactiveIdentity();

        $status = $this->buildService()->getStatusForIdentity($identity);

        $this->assertEquals(TwoFactorStatus::AWAITING_CARD_ORDER, $status);
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

    private function withExistingNomineeTradeRoles()
    {
        return $this->withExistingNomineeRoles([
            RoleCode::TESTER
        ]);
    }

    private function withExistingNomineeNonTradeRoles()
    {
        return $this->withExistingNomineeRoles([
            RoleCode::SCHEME_MANAGER
        ]);
    }

    private function withNoExistingNomineeRoles()
    {
        return $this->withExistingNomineeRoles([]);
    }

    private function withExistingNomineeRoles(array $roles)
    {
        $personAuthorization = XMock::of(PersonAuthorization::class);
        $personAuthorization
            ->expects($this->any())
            ->method('getAllRoles')
            ->willReturn($roles);

        $this->authorisationService
            ->expects($this->any())
            ->method('getPersonAuthorization')
            ->willReturn($personAuthorization);

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

        $this->authorisationServiceClient
            ->expects($this->any())
            ->method("getSecurityCardOrders")
            ->willReturn($securityCardOrders);

        return $this;
    }

    private function withNoCardOrder()
    {
        $securityCardOrders = new Collection([], SecurityCardOrder::class);

        $this->authorisationServiceClient
            ->expects($this->any())
            ->method("getSecurityCardOrders")
            ->willReturn($securityCardOrders);

        return $this;
    }

    private function buildService()
    {
        return new TwoFactorStatusService(
            $this->authorisationServiceClient,
            $this->authorisationService,
            $this->personRepository
        );
    }
}
