<?php

namespace SiteApi\Service;

use DvsaAuthorisation\Service\AuthorisationServiceInterface;
use DvsaCommon\Auth\PermissionAtSite;
use DvsaCommon\Dto\MotTesting\MotTestInProgressDto;
use DvsaCommonApi\Transaction\TransactionAwareInterface;
use DvsaCommonApi\Transaction\TransactionAwareTrait;
use DvsaEntities\Repository\MotTestRepository;
use SiteApi\Service\Mapper\MotTestInProgressMapper;

/**
 * Class MotTestInProgressService.
 */
class MotTestInProgressService implements TransactionAwareInterface
{
    use TransactionAwareTrait;

    private $motTestRepository;

    private $authorizationService;

    public function __construct(
        MotTestRepository $motTestRepository,
        AuthorisationServiceInterface $authorizationService
    ) {
        $this->motTestRepository = $motTestRepository;
        $this->authorizationService = $authorizationService;
    }

    /**
     * @param $vtsId
     *
     * @return MotTestInProgressDto[]
     */
    public function getAllForSite($vtsId)
    {
        $this->authorizationService->assertGrantedAtSite(PermissionAtSite::VIEW_TESTS_IN_PROGRESS_AT_VTS, $vtsId);

        $mapper = new MotTestInProgressMapper();

        $motTestsInProgress = $this->motTestRepository->findInProgressTestsForVts($vtsId);
        $dto = $mapper->manyToDto($motTestsInProgress);

        return $dto;
    }

    /**
     * @param $vtsId
     *
     * @return int
     */
    public function getCountForSite($vtsId)
    {
        $this->authorizationService->assertGrantedAtSite(PermissionAtSite::VIEW_TESTS_IN_PROGRESS_AT_VTS, $vtsId);

        return $this->motTestRepository->countInProgressTestsForVts($vtsId);
    }
}
