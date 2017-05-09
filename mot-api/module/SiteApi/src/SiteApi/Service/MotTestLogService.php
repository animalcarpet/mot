<?php

namespace SiteApi\Service;

use DvsaAuthorisation\Service\AuthorisationServiceInterface;
use DvsaCommon\Auth\PermissionAtSite;
use DvsaEntities\Repository\MotTestRepository;
use SiteApi\Service\Mapper\MotTestLogSummaryMapper;

/**
 * Class MotTestLogService.
 */
class MotTestLogService
{
    /** @var AuthorisationServiceInterface */
    protected $authSrv;
    /** @var MotTestRepository */
    private $motTestRepository;
    /** @var MotTestLogSummaryMapper */
    private $mapper;

    public function __construct(
        AuthorisationServiceInterface $authSrv,
        MotTestRepository $motTestRepository,
        MotTestLogSummaryMapper $mapper
    ) {
        $this->authSrv = $authSrv;
        $this->motTestRepository = $motTestRepository;
        $this->mapper = $mapper;
    }

    /**
     * Responsible for getting the mot test logs for:
     *   - AED
     *   - AEDM
     *   - Site manager
     *   - DVSA Area Office 1
     *   - DVSA Area Office 2
     *   - Vehicle examiners.
     *
     * @param int $siteId
     *
     * @return \DvsaCommon\Dto\Site\MotTestLogSummaryDto
     */
    public function getMotTestLogSummaryForSite($siteId)
    {
        $this->authSrv->assertGrantedAtSite(
            PermissionAtSite::VTS_TEST_LOGS,
            $siteId
        );

        $countOfTestsSummary = $this->motTestRepository->getCountOfSiteMotTestsSummary($siteId);

        return $this->mapper->toDto($countOfTestsSummary);
    }
}
