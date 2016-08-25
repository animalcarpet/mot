<?php

namespace OrganisationApiTest\Service;

use Doctrine\ORM\EntityRepository;
use DvsaAuthentication\Identity;
use DvsaAuthorisation\Service\AuthorisationServiceInterface;
use DvsaCommon\Database\Transaction;
use DvsaCommon\Enum\RoleCode;
use DvsaCommon\Exception\UnauthorisedException;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Entity\BusinessRoleStatus;
use DvsaEntities\Entity\Organisation;
use DvsaEntities\Entity\OrganisationBusinessRole;
use DvsaEntities\Entity\OrganisationBusinessRoleMap;
use DvsaEntities\Entity\Person;
use DvsaEntities\Entity\Role;
use DvsaEntities\Repository\OrganisationBusinessRoleMapRepository;
use DvsaEntities\Repository\OrganisationPositionHistoryRepository;
use DvsaEntities\Repository\PersonRepository;
use OrganisationApi\Model\Operation\DirectNominationOperation;
use OrganisationApi\Model\Operation\ConditionalNominationOperation;
use OrganisationApi\Model\Operation\NominateOperationInterface;
use OrganisationApi\Service\NominateRoleService;
use PHPUnit_Framework_TestCase;
use Zend\Authentication\AuthenticationService;

class NominateRoleServiceTest extends PHPUnit_Framework_TestCase
{
    const PERSON_ID_NOMINATOR = 11;

    const PERSON_ID_NOMINEE = 22;

    const ORGANISATION_ID = 33;

    const ROLE_ID_AED = 3;

    const ROLE_CODE_AED = RoleCode::AUTHORISED_EXAMINER_DELEGATE;

    const ROLE_ID_AEDM = 6;

    const ROLE_CODE_AEDM = RoleCode::AUTHORISED_EXAMINER_DESIGNATED_MANAGER;

    const ROLE_ID_TESTER = 9;

    const ROLE_CODE_TESTER = RoleCode::TESTER;

    /** @var AuthorisationServiceInterface */
    private $authorisationService;

    /** @var EntityRepository */
    private $businessRoleStatusRepository;

    /** @var Person */
    private $currentUser;

    /** @var StubNominationOperation */
    private $nominationOperation;

    /** @var Person */
    private $nominator;

    /** @var Person */
    private $nominee;

    /** @var Organisation */
    private $organisation;

    /** @var EntityRepository */
    private $organisationBusinessRoleRepository;

    /** @var OrganisationBusinessRoleMapRepository */
    private $organisationBusinessRoleMapRepository;

    /** @var OrganisationBusinessRole */
    private $organisationBusinessRole;

    public function setUp()
    {
        $this->authorisationService = XMock::of(AuthorisationServiceInterface::class);
        $this->businessRoleStatusRepository = XMock::of(EntityRepository::class);
        $this->currentUser = XMock::of(Person::class);
        $this->nominationOperation = new StubNominationOperation();
        $this->nominee = XMock::of(Person::class);
        $this->nominator = XMock::of(Person::class);
        $this->organisation = XMock::of(Organisation::class);
        $this->organisationBusinessRoleRepository = XMock::of(EntityRepository::class);
        $this->organisationBusinessRoleMapRepository = XMock::of(OrganisationBusinessRoleMapRepository::class);

        $this
            ->withOrganisationBusinessRole(RoleCode::AUTHORISED_EXAMINER_DELEGATE)
            ->withBusinessRoleStatus(new BusinessRoleStatus());
    }

    public function testCannotNominateWithoutPermissionToNominateRoleAtAe()
    {
        $this->withoutNecessaryPermissions();

        $this->setExpectedException(UnauthorisedException::class);

        $this->buildService()->nominateRole();
    }

    public function testNominateRoleDefersToNominateOperation()
    {
        $this->buildService()->nominateRole();

        $nominator = $this->nominationOperation->getNominator();
        /** @var OrganisationBusinessRoleMap $nomination */
        $nomination = $this->nominationOperation->getNomination();

        $this->assertEquals($this->currentUser, $nominator);
        $this->assertEquals($nomination->getOrganisationBusinessRole(), $this->organisationBusinessRole);
        $this->assertEquals($nomination->getOrganisation(), $this->organisation);
        $this->assertEquals($nomination->getPerson(), $this->nominee);
    }

    public function testUpdateRoleNominationNotificationDefersToNominateOperation()
    {
        $nomination = new OrganisationBusinessRoleMap();
        $nomination->setCreatedBy($this->nominator);

        $this->withExistingNomination($nomination);

        $this->buildService()->updateRoleNominationNotification();

        $actualNominator = $this->nominationOperation->getNominator();
        /** @var OrganisationBusinessRoleMap $actualNomination */
        $actualNomination = $this->nominationOperation->getNomination();

        $this->assertEquals($this->nominator, $actualNominator);
        $this->assertEquals($nomination, $actualNomination);
    }

    private function withBusinessRoleStatus(BusinessRoleStatus $status)
    {
        $this->businessRoleStatusRepository
            ->expects($this->any())
            ->method('findOneBy')
            ->willReturn($status);

        return $this;
    }

    private function withOrganisationBusinessRole($roleCode)
    {
        $role = new Role();
        $role->setCode($roleCode);

        $organisationBusinessRole = new OrganisationBusinessRole();
        $organisationBusinessRole->setRole($role);

        $this->organisationBusinessRole = $organisationBusinessRole;

        return $this;
    }

    private function withExistingNomination(OrganisationBusinessRoleMap $nomination)
    {
        $this->organisationBusinessRoleMapRepository
            ->expects($this->any())
            ->method('findOneBy')
            ->willReturn($nomination);

        return $this;
    }

    private function withoutNecessaryPermissions()
    {
        $this->authorisationService
            ->method('assertGrantedAtOrganisation')
            ->will($this->throwException(new UnauthorisedException('[]')));

        return $this;
    }

    private function buildService()
    {
        return new NominateRoleService(
            $this->currentUser,
            $this->nominee,
            $this->organisation,
            $this->organisationBusinessRole,
            $this->businessRoleStatusRepository,
            $this->organisationBusinessRoleMapRepository,
            $this->authorisationService,
            $this->nominationOperation,
            XMock::of(Transaction::class)
        );
    }
}

class StubNominationOperation implements NominateOperationInterface
{
    private $nominator;

    private $nomination;

    public function nominate(Person $nominator, OrganisationBusinessRoleMap $nomination)
    {
        $this->nominator = $nominator;
        $this->nomination = $nomination;
    }

    public function updateNomination(Person $nominator, OrganisationBusinessRoleMap $nomination)
    {
        $this->nominator = $nominator;
        $this->nomination = $nomination;
    }

    public function getNominator()
    {
        return $this->nominator;
    }

    public function getNomination()
    {
        return $this->nomination;
    }
}
