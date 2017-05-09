<?php

namespace OrganisationApiTest\Service;

use Doctrine\ORM\EntityRepository;
use DvsaAuthentication\Identity;
use DvsaAuthentication\Service\TwoFactorStatusService;
use DvsaAuthentication\TwoFactorStatus;
use DvsaAuthorisation\Service\AuthorisationServiceInterface;
use DvsaCommon\Constants\FeatureToggle;
use DvsaCommon\Database\Transaction;
use DvsaCommon\Enum\RoleCode;
use DvsaCommonApi\Service\Exception\NotFoundException;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Entity\Organisation;
use DvsaEntities\Entity\OrganisationBusinessRole;
use DvsaEntities\Entity\Person;
use DvsaEntities\Entity\Role;
use DvsaEntities\Repository\OrganisationBusinessRoleMapRepository;
use DvsaEntities\Repository\OrganisationBusinessRoleRepository;
use DvsaEntities\Repository\OrganisationRepository;
use DvsaEntities\Repository\PersonRepository;
use DvsaFeature\FeatureToggles;
use OrganisationApi\Model\Operation\DirectNominationOperation;
use OrganisationApi\Model\Operation\ConditionalNominationOperation;
use OrganisationApi\Service\NominateRoleServiceBuilder;
use PHPUnit_Framework_TestCase;
use Zend\Authentication\AuthenticationService;

class NominateRoleServiceBuilderTest extends PHPUnit_Framework_TestCase
{
    private $authenticationService;

    private $organisationRepository;

    private $organisationBusinessRoleRepository;

    private $conditionalNominationOperation;

    private $directNominationOperation;

    private $twoFactorStatusService;

    private $featureToggles;

    public function setUp()
    {
        $this->organisationRepository = XMock::of(OrganisationRepository::class);
        $this->organisationBusinessRoleRepository = XMock::of(OrganisationBusinessRoleRepository::class);

        $this->conditionalNominationOperation = XMock::of(ConditionalNominationOperation::class);
        $this->directNominationOperation = XMock::of(DirectNominationOperation::class);

        $this->authenticationService = XMock::of(AuthenticationService::class);

        $this->twoFactorStatusService = XMock::of(TwoFactorStatusService::class);

        $this
            ->withOrganisation()
            ->withNominator()
            ->withTwoFaFeatureToggle(true);
    }

    public function testGivenAedmRoleAndActiveTwoFactorNominee_BuilderWillInjectDirectNominationOperation()
    {
        $this
            ->withOrganisationBusinessRole(RoleCode::AUTHORISED_EXAMINER_DESIGNATED_MANAGER)
            ->withNomineeTwoFactorStatus(TwoFactorStatus::ACTIVE);

        $builder = $this->buildServiceBuilder();
        $service = $builder
            ->buildForNominationCreation(StubNominationPersonRepository::PERSON_ID_NOMINEE, 1, 2);

        $this->assertAttributeEquals($this->directNominationOperation, 'nominateOperation', $service);
    }

    public function testGivenAedmRoleAndAwaitingCardOrderNominee_BuilderWillInjectNominateByRequestOperation()
    {
        $this
            ->withOrganisationBusinessRole(RoleCode::AUTHORISED_EXAMINER_DESIGNATED_MANAGER)
            ->withNomineeTwoFactorStatus(TwoFactorStatus::AWAITING_CARD_ORDER);

        $builder = $this->buildServiceBuilder();
        $service = $builder
            ->buildForNominationCreation(StubNominationPersonRepository::PERSON_ID_NOMINEE, 1, 2);

        $this->assertAttributeEquals($this->conditionalNominationOperation, 'nominateOperation', $service);
    }

    public function testGivenAedmRoleAndAwaitingCardActivationNominee_BuilderWillInjectNominateByRequestOperation()
    {
        $this
            ->withOrganisationBusinessRole(RoleCode::AUTHORISED_EXAMINER_DESIGNATED_MANAGER)
            ->withNomineeTwoFactorStatus(TwoFactorStatus::AWAITING_CARD_ACTIVATION);

        $builder = $this->buildServiceBuilder();
        $service = $builder
            ->buildForNominationCreation(StubNominationPersonRepository::PERSON_ID_NOMINEE, 1, 2);

        $this->assertAttributeEquals($this->conditionalNominationOperation, 'nominateOperation', $service);
    }

    public function testGivenAedmRoleForNon2faTradeNominee_BuilderWillInjectDirectNominationOperation()
    {
        $this
            ->withOrganisationBusinessRole(RoleCode::AUTHORISED_EXAMINER_DESIGNATED_MANAGER)
            ->withNomineeTwoFactorStatus(TwoFactorStatus::INACTIVE_TRADE_USER);

        $builder = $this->buildServiceBuilder();
        $service = $builder
            ->buildForNominationCreation(StubNominationPersonRepository::PERSON_ID_NOMINEE, 1, 2);

        $this->assertAttributeEquals($this->directNominationOperation, 'nominateOperation', $service);
    }

    public function testGivenAedmRoleAndTwoFactorFeatureToggleDisabled_BuilderWillInjectDirectNominationOperation()
    {
        $this
            ->withOrganisationBusinessRole(RoleCode::AUTHORISED_EXAMINER_DESIGNATED_MANAGER)
            ->withTwoFaFeatureToggle(false);

        $builder = $this->buildServiceBuilder();
        $service = $builder
            ->buildForNominationCreation(StubNominationPersonRepository::PERSON_ID_NOMINEE, 1, 2);

        $this->assertAttributeEquals($this->directNominationOperation, 'nominateOperation', $service);
    }

    public function testGivenNonAedmRole_BuilderWillInjectNominateByRequestOperation()
    {
        $this
            ->withOrganisationBusinessRole(RoleCode::AUTHORISED_EXAMINER_DELEGATE)
            ->withNomineeTwoFactorStatus(TwoFactorStatus::AWAITING_CARD_ORDER);

        $builder = $this->buildServiceBuilder();
        $service = $builder
            ->buildForNominationCreation(StubNominationPersonRepository::PERSON_ID_NOMINEE, 1, 2);

        $this->assertAttributeEquals($this->conditionalNominationOperation, 'nominateOperation', $service);
    }

    public function testGivenRoleId_BuilderWillGetFromRepositoryById()
    {
        $this->organisationBusinessRoleRepository
            ->expects($this->once())
            ->method('get')
            ->willReturn($this->buildOrganisationBusinessRole(RoleCode::TESTER));

        $builder = $this->buildServiceBuilder();
        $builder
            ->buildForNominationCreation(StubNominationPersonRepository::PERSON_ID_NOMINEE, 1, 2);
    }

    public function testGivenRoleCode_BuilderWillGetFromRepositoryByCode()
    {
        $this->organisationBusinessRoleRepository
            ->expects($this->once())
            ->method('getByCode')
            ->willReturn($this->buildOrganisationBusinessRole(RoleCode::TESTER));

        $builder = $this->buildServiceBuilder();
        $builder
            ->buildForNominationUpdate(StubNominationPersonRepository::PERSON_ID_NOMINEE, 1, 'TESTER');
    }

    public function testGivenNeitherRoleIdOrCode_BuilderWillThrow()
    {
        $this->setExpectedException(NotFoundException::class);

        $builder = $this->buildServiceBuilder();

        $builder
            ->buildForNominationCreation(StubNominationPersonRepository::PERSON_ID_NOMINEE, 1, 2);
    }

    private function withNomineeTwoFactorStatus($status)
    {
        $this->twoFactorStatusService
            ->expects($this->any())
            ->method('getStatusForPerson')
            ->willReturn($status);

        return $this;
    }

    private function withNominator()
    {
        $person = new Person();
        $person->setId(StubNominationPersonRepository::PERSON_ID_NOMINATOR);

        $identity = new Identity($person);

        $this->authenticationService
            ->expects($this->any())
            ->method('getIdentity')
            ->willReturn($identity);

        return $this;
    }

    private function withOrganisation()
    {
        $this->organisationRepository
            ->expects($this->any())
            ->method('get')
            ->willReturn(new Organisation());

        return $this;
    }

    private function withOrganisationBusinessRole($roleCode)
    {
        $orgRole = $this->buildOrganisationBusinessRole($roleCode);

        $this->organisationBusinessRoleRepository
            ->expects($this->any())
            ->method('get')
            ->willReturn($orgRole);

        $this->organisationBusinessRoleRepository
            ->expects($this->any())
            ->method('getByCode')
            ->willReturn($orgRole);

        return $this;
    }

    private function withTwoFaFeatureToggle($enabled)
    {
        $this->featureToggles = XMock::of(FeatureToggles::class);

        $this->featureToggles
            ->expects($this->any())
            ->method('isEnabled')
            ->with(FeatureToggle::TWO_FA)
            ->willReturn($enabled);

        return $this;
    }

    private function buildOrganisationBusinessRole($roleCode)
    {
        $role = new Role();
        $role->setCode($roleCode);

        $orgRole = new OrganisationBusinessRole();
        $orgRole->setRole($role);

        return $orgRole;
    }

    private function buildServiceBuilder()
    {
        return new NominateRoleServiceBuilder(
            $this->organisationRepository,
            new StubNominationPersonRepository(),
            $this->organisationBusinessRoleRepository,
            XMock::of(EntityRepository::class),
            XMock::of(OrganisationBusinessRoleMapRepository::class),
            XMock::of(AuthorisationServiceInterface::class),
            $this->conditionalNominationOperation,
            $this->directNominationOperation,
            XMock::of(Transaction::class),
            $this->authenticationService,
            $this->featureToggles,
            $this->twoFactorStatusService,
            XMock::of(TwoFactorStatusService::class)
        );
    }
}

class StubNominationPersonRepository extends PersonRepository
{
    const PERSON_ID_NOMINATOR = 11;
    const PERSON_ID_NOMINEE = 22;

    public function __construct()
    {
    }

    public function get($id)
    {
        $nominee = new Person();
        $nominee->setId(self::PERSON_ID_NOMINEE);

        $nominator = new Person();
        $nominator->setId(self::PERSON_ID_NOMINATOR);

        $people = [
            self::PERSON_ID_NOMINATOR => $nominator,
            self::PERSON_ID_NOMINEE => $nominee,
        ];

        return isset($people[$id]) ? $people[$id] : null;
    }
}
