<?php

namespace DvsaMotApi\Service;

use DvsaAuthorisation\Service\AuthorisationServiceInterface;
use DvsaCommon\Auth\PermissionAtSite;
use DvsaEntities\Entity\MotTest;
use DvsaEntities\Repository\MotTestRepository;
use DvsaMotApi\Service\Mapper\MotTestMapper;

class MotTestShortSummaryService
{
    /** @var MotTestRepository $motTestRepository */
    protected $motTestRepository;

    protected $authService;

    /** @var MotTestMapper $motTestMapper */
    protected $motTestMapper;

    public function __construct(
        MotTestRepository $motTestRepository,
        AuthorisationServiceInterface $authService,
        MotTestMapper $motTestMapper
    ) {
        $this->motTestRepository = $motTestRepository;
        $this->authService = $authService;
        $this->motTestMapper = $motTestMapper;
    }

    /**
     * @param $motTestNumber
     *
     * @return array
     *
     * @throws \DvsaCommonApi\Service\Exception\ForbiddenException
     */
    public function getMotTestData($motTestNumber)
    {
        $motTest = $this->getMotTest($motTestNumber);
        $vtsId = $motTest->getVehicleTestingStation()->getId();

        $this->authService->assertGrantedAtSite(PermissionAtSite::VIEW_TESTS_IN_PROGRESS_AT_VTS, $vtsId);

        return $this->extractMotTest($motTest);
    }

    private function extractMotTest(MotTest $motTest)
    {
        return $this->motTestMapper->mapMotTest($motTest, true);
    }

    /**
     * @param $motTestNumber
     *
     * @return \DvsaEntities\Entity\MotTest
     *
     * @throws \DvsaCommonApi\Service\Exception\NotFoundException
     */
    public function getMotTest($motTestNumber)
    {
        return $this->motTestRepository->getMotTestByNumber($motTestNumber);
    }
}
