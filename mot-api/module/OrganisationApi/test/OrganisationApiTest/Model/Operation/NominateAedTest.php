<?php
namespace OrganisationApiTest\Model\Operation;

use Doctrine\ORM\EntityManager;
use DvsaAuthorisation\Service\AuthorisationServiceInterface;
use DvsaCommonApi\Service\Exception\BadRequestException;
use DvsaCommonApiTest\Service\AbstractServiceTestCase;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Entity\AuthorisationForAuthorisedExaminer;
use DvsaEntities\Entity\Organisation;
use DvsaEntities\Entity\OrganisationBusinessRoleMap;
use DvsaEntities\Entity\Person;
use DvsaEntities\Repository\OrganisationRepository;
use OrganisationApi\Model\NominationVerifier;
use OrganisationApi\Model\Operation\ConditionalNominationOperation;
use OrganisationApi\Model\RoleAvailability;
use OrganisationApi\Model\RoleRestriction\AedRestriction;
use OrganisationApi\Model\RoleRestrictionsSet;
use OrganisationApi\Service\OrganisationNominationNotificationService;
use NotificationApi\Service\NotificationService;

/**
 * Class NominateAedTest
 *
 * @package OrganisationApiTest\Model\Operation
 */
class NominateAedTest extends AbstractServiceTestCase
{
    /** @var OrganisationRepository */
    private $positionRepository;

    /** @var ConditionalNominationOperation */
    private $nominateOperation;
    private $person;

    /** @var  AuthorisationServiceInterface */
    private $authorizationService;

    private $notificationService;

    public function setUp()
    {
        $this->person               = new Person();
        $this->authorizationService = $this->getMockWithDisabledConstructor(AuthorisationServiceInterface::class);
        $obrRepository              = XMock::of(\Doctrine\ORM\EntityRepository::class);
        $obrRepository->expects($this->any())->method('findAll')->will($this->returnValue([]));

        $roleRestrictionsSet        = new RoleRestrictionsSet([new AedRestriction($this->authorizationService)]);
        $roleAvailability = new RoleAvailability($roleRestrictionsSet, $this->authorizationService, $obrRepository);

        $nominationVerifier       = new NominationVerifier($roleAvailability);
        $this->positionRepository = $this->getMockWithDisabledConstructor(\Doctrine\ORM\EntityManager::class);
        $nominationServiceMock    = $this->getMockWithDisabledConstructor(OrganisationNominationNotificationService::class);

        $this->notificationService = XMock::of(NotificationService::class);

        $this->nominateOperation = new ConditionalNominationOperation(
            $this->positionRepository, $nominationVerifier, $nominationServiceMock, $this->notificationService
        );
    }

    public function test_adding_aed_to_non_authorised_examiner_should_fail()
    {
        $this->markTestSkipped();
        $this->positionRepository->expects($this->never())->method('persist');
        $notAuthorisedExaminerOrganisation = $this->buildNonAeOrganisation();
        $aedPosition                       = new OrganisationBusinessRoleMap();

        $error = 'No error';
        try {
            $this->nominateOperation->nominate(new Person(), $aedPosition);
        } catch (BadRequestException $e) {
            $error = $e->getErrors()[0]["message"];
        }

        $this->assertEquals(AedRestriction::NOT_AE_ERROR, $error);
    }

    public function test_adding_aed_to_authorised_examiner_works()
    {
        $this->markTestSkipped();
        $this->positionRepository->expects($this->once())->method('persist');

        $authorisedExaminer = $this->buildAeOrganisation();
        $position           = new OrganisationBusinessRoleMap();

        $this->nominateOperation->nominate(new Person(), $position);

        // No exceptions
    }

    /**
     * @return Organisation
     */
    private function buildAeOrganisation()
    {
        $organisation = new Organisation();
        $organisation->setAuthorisedExaminer(new AuthorisationForAuthorisedExaminer());

        return $organisation;
    }

    /**
     * @return Organisation
     */
    private function buildNonAeOrganisation()
    {
        $organisation = new Organisation();

        return $organisation;
    }
}
