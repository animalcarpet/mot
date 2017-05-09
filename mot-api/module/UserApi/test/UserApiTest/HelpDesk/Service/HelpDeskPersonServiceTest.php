<?php

namespace UserApiTest\HelpDesk\Service;

use DvsaAuthorisation\Service\AuthorisationServiceInterface;
use DvsaAuthorisation\Service\UserRoleService;
use DvsaCommon\Dto\Person\PersonHelpDeskProfileDto;
use DvsaCommon\Model\SearchPersonModel;
use DvsaCommonApiTest\Service\AbstractServiceTestCase;
use DvsaCommonTest\Dto\Person\SearchPersonResultDtoTest;
use DvsaEntities\Entity\Person;
use DvsaEntities\Repository\PersonRepository;
use UserApi\HelpDesk\Mapper\PersonHelpDeskProfileMapper;
use UserApi\HelpDesk\Service\HelpDeskPersonService;
use UserApi\HelpDesk\Service\Validator\SearchPersonValidator;

/**
 * Unit tests for HelpDeskPersonService.
 */
class HelpDeskPersonServiceTest extends AbstractServiceTestCase
{
    const PERSON_ID = 3;
    /**
     * @var HelpDeskPersonService
     */
    private $service;

    /**
     * @expectedException \DvsaCommonApi\Service\Exception\TooFewResultsException
     */
    public function testSearchNoRowsMatchedShouldThrowException()
    {
        $this->runTestRepositoryReturnsArray([]);
    }

    public function testSearchOneRowMatchedShouldArrayWithOneDtoObject()
    {
        $this->runTestRepositoryReturnsArray(
            [SearchPersonResultDtoTest::getSearchPersonResultDtoData()]
        );
    }

    public function testGetPersonProfile()
    {
        $this->setupServiceWithMocks();
        $this->assertInstanceOf(PersonHelpDeskProfileDto::class, $this->service->getPersonProfile(self::PERSON_ID));
    }

    private function setupServiceWithMocks($dataRetrievedFromDb = null)
    {
        $personRepo = $this->getMockWithDisabledConstructor(PersonRepository::class);
        $userRoleServiceMock = $this->getMockWithDisabledConstructor(UserRoleService::class);
        $authServiceMock = $this->getMockWithDisabledConstructor(AuthorisationServiceInterface::class);
        $personHelpDeskProfileMapperMock = $this->getMockWithDisabledConstructor(PersonHelpDeskProfileMapper::class);

        $personRepo->expects($this->any())->method('searchAll')->willReturn($dataRetrievedFromDb);
        $personRepo->expects($this->any())->method('get')->with(self::PERSON_ID)->will(
            $this->returnValue((new Person())->setDateOfBirth(new \DateTime()))
        );
        $personHelpDeskProfileMapperMock->expects($this->any())->method('fromPersonEntityToDto')->will(
            $this->returnValue(new PersonHelpDeskProfileDto())
        );

        $this->service = new HelpDeskPersonService(
            $personRepo,
            $authServiceMock,
            $userRoleServiceMock,
            new SearchPersonValidator(),
            $personHelpDeskProfileMapperMock
        );
    }

    private function runTestRepositoryReturnsArray($dataRetrievedFromDb)
    {
        $this->setupServiceWithMocks($dataRetrievedFromDb);
        $this->assertCount(count($dataRetrievedFromDb), $this->service->search(new SearchPersonModel(1, 2, 3, '1981-04-24', 5, 6, null)));
    }
}
