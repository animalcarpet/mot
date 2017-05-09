<?php

namespace PersonApi\Service;

use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Dto\MotTesting\DemoTestRequestsListDto;
use DvsaCommon\Dto\Search\DemoTestRequestsSearchParamsDto;
use DvsaCommonApiTest\Service\AbstractServiceTestCase;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\DqlBuilder\SearchParam\DemoTestRequestsSearchParam;
use DvsaEntities\Repository\PersonRepository;
use DvsaEntities\Repository\QualificationAwardRepository;
use PersonApi\Service\Mapper\DemoTestRequestsMapper;
use PHPUnit_Framework_MockObject_MockObject as MockObj;

/**
 * Tests for DemoTestRequestsService.
 */
class DemoTestRequestsServiceTest extends AbstractServiceTestCase
{
    const PERSON_ID = 1234;

    /* @var QualificationAwardRepository $motTestingCertificateRepository */
    private $motTestingCertificateRepository;

    /* @var PersonRepository $personRepository */
    private $personRepository;

    /* @var MotAuthorisationServiceInterface|MockObj $authorisationService */
    private $authorisationService;

    /* @var DemoTestRequestsMapper $authorisationService */
    private $demoTestRequestsMapper;

    /* @var DemoTestRequestsService $service */
    private $service;

    public function setup()
    {
        $this->motTestingCertificateRepository = XMock::of(QualificationAwardRepository::class);
        $this->personRepository = XMock::of(PersonRepository::class);
        $this->authorisationService = XMock::of(MotAuthorisationServiceInterface::class);
        $this->demoTestRequestsMapper = XMock::of(DemoTestRequestsMapper::class);

        $this->service = new DemoTestRequestsService(
            $this->motTestingCertificateRepository,
            $this->personRepository,
            $this->authorisationService,
            $this->demoTestRequestsMapper
        );
    }

    public function testFindAllDemoTestRequestsUsersSorted()
    {
        $this->authorisationService->expects($this->once())
            ->method('assertGranted')
            ->with(PermissionInSystem::VIEW_USERS_IN_DEMO_TEST_NEEDED_STATE)
            ->willReturn(true);

        $this->motTestingCertificateRepository->expects($this->once())
            ->method('findAllDemoTestRequestsUsersCount')
            ->with($this->getDemoTestRequestsSearchParams())
            ->willReturn(2);

        $this->motTestingCertificateRepository->expects($this->once())
            ->method('findAllDemoTestRequestsUsersSorted')
            ->with($this->getDemoTestRequestsSearchParams())
            ->willReturn([]);

        $this->assertInstanceOf(DemoTestRequestsListDto::class, $this->service->findDemoTestRequestsForUsers($this->getDemoTestRequestsSearchParamsDto()));
    }

    private function getDemoTestRequestsSearchParamsDto()
    {
        $dto = (new DemoTestRequestsSearchParamsDto())
            ->setSortBy('user');

        return $dto;
    }

    private function getDemoTestRequestsSearchParams()
    {
        $searchParam = (new DemoTestRequestsSearchParam())
            ->fromDto($this->getDemoTestRequestsSearchParamsDto());

        return $searchParam;
    }
}
