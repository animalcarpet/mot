<?php

namespace PersonApi\Service;

use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Dto\MotTesting\DemoTestRequestsListDto;
use DvsaCommon\Dto\Search\DemoTestRequestsSearchParamsDto;
use DvsaEntities\DqlBuilder\SearchParam\DemoTestRequestsSearchParam;
use DvsaEntities\Repository\QualificationAwardRepository;
use DvsaEntities\Repository\PersonRepository;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use PersonApi\Service\Mapper\DemoTestRequestsMapper;

class DemoTestRequestsService implements AutoWireableInterface
{
    private $motTestingCertificateRepository;
    private $personRepository;
    private $authorisationService;
    private $demoTestRequestsMapper;

    public function __construct(
        QualificationAwardRepository $motTestingCertificateRepository,
        PersonRepository $personRepository,
        MotAuthorisationServiceInterface $authorisationService,
        DemoTestRequestsMapper $demoTestRequestsMapper
    ) {
        $this->motTestingCertificateRepository = $motTestingCertificateRepository;
        $this->personRepository = $personRepository;
        $this->authorisationService = $authorisationService;
        $this->demoTestRequestsMapper = $demoTestRequestsMapper;
    }

    public function findDemoTestRequestsForUsers(DemoTestRequestsSearchParamsDto $params)
    {
        $this->authorisationService->assertGranted(PermissionInSystem::VIEW_USERS_IN_DEMO_TEST_NEEDED_STATE);

        $searchParams = (new DemoTestRequestsSearchParam())
            ->fromDto($params)
            ->process();

        $results = [];
        $resultsCount = $this->motTestingCertificateRepository->findAllDemoTestRequestsUsersCount($searchParams);

        if ($resultsCount != 0) {
            $arrayResults = $this->motTestingCertificateRepository->findAllDemoTestRequestsUsersSorted($searchParams);
            $results = $this->demoTestRequestsMapper->manyToDto($arrayResults);
        }

        return (new DemoTestRequestsListDto())
            ->setTotalResultCount($resultsCount)
            ->setData($results);
    }
}
