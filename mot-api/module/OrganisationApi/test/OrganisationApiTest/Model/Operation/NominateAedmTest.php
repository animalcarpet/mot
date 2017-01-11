<?php
namespace OrganisationApiTest\Model\Operation;

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
use OrganisationApi\Model\Operation\NominateByRequestOperation;
use OrganisationApi\Model\RoleAvailability;
use OrganisationApi\Model\RoleRestriction\AedmRestriction;
use OrganisationApi\Model\RoleRestriction\AedRestriction;
use OrganisationApi\Model\RoleRestrictionsSet;
use OrganisationApi\Service\OrganisationNominationService;

class NominateAedmTest extends AbstractServiceTestCase
{
    /** @var OrganisationRepository */
    private $positionRepository;

    /** @var NominateByRequestOperation */
    private $nominateOperation;
    private $person;

    /** @var  AuthorisationServiceInterface */
    private $authorizationService;

    public function setUp()
    {
        $this->person               = new Person();
        $this->authorizationService = $this->getMockWithDisabledConstructor(AuthorisationServiceInterface::class);
        $obrRepository              = XMock::of(\Doctrine\ORM\EntityRepository::class);
        $obrRepository->expects($this->any())->method('findAll')->will($this->returnValue([]));

        $roleRestrictionsSet = new RoleRestrictionsSet(
            [
                new AedRestriction($this->authorizationService),
                new AedmRestriction($this->authorizationService)
            ]
        );
        $roleAvailability = new RoleAvailability($roleRestrictionsSet, $this->authorizationService, $obrRepository);

        $nominationVerifier       = new NominationVerifier($roleAvailability);
        $this->positionRepository = $this->getMockWithDisabledConstructor(\Doctrine\ORM\EntityManager::class);
        $nominationServiceMock    = $this->getMockWithDisabledConstructor(OrganisationNominationService::class);

        $this->nominateOperation = new NominateByRequestOperation(
            $this->positionRepository, $nominationVerifier, $nominationServiceMock
        );
    }

    public function test_adding_aedm_to_non_authorised_examiner_should_fail()
    {
        $this->markTestSkipped();
        $this->positionRepository->expects($this->never())->method('persist');
        $notAuthorisedExaminerOrganisation = $this->buildNonAeOrganisation();
        $aedmPosition                      = new OrganisationBusinessRoleMap();

        $error = 'No error';
        try {
            $this->nominateOperation->nominate(new Person(), $aedmPosition);
        } catch (BadRequestException $e) {
            $error = $e->getErrors()[0]["message"];
        }

        $this->assertEquals(AedmRestriction::NOT_AE_ERROR, $error);
    }

    public function test_adding_aedm_to_authorised_examiner_works()
    {
        $this->markTestSkipped();
        $this->positionRepository->expects($this->once())->method('persist');

        $authorisedExaminer = $this->buildAeOrganisation();
        $position           = new OrganisationBusinessRoleMap();

        $this->nominateOperation->nominate(new Person(), $position);

        // No exceptions
    }

    public function test_adding_second_aedm_should_fail()
    {
        $this->markTestSkipped();
        $this->positionRepository->expects($this->never())->method('persist');

        $authorisedExaminer = $this->buildAeOrganisationWithAedm();
        $position           = new OrganisationBusinessRoleMap();

        $error = 'No error';
        try {
            $this->nominateOperation->nominate(new Person(), $position);
        } catch (BadRequestException $e) {
            $error = $e->getErrors()[0]["message"];
        }

        $this->assertEquals(AedmRestriction::SITE_ALREADY_HAS_AEDM, $error);
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
    private function buildAeOrganisationWithAedm()
    {
        $organisation = $this->buildAeOrganisation();

        $aedmPosition = new OrganisationBusinessRoleMap();

        $organisation->getPositions()->add($aedmPosition);

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
