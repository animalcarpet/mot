<?php

namespace OrganisationApi\Service;

use DvsaAuthorisation\Service\AuthorisationServiceInterface;
use DvsaCommon\Auth\PermissionAtOrganisation;
use DvsaEntities\Repository\MotTestRepository;
use OrganisationApi\Service\Mapper\MotTestLogSummaryMapper;

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
     * This function is responsible to get the mot tests logs for
     * an authorised examiner.
     *
     * @param int $organisationId
     *
     * @return \DvsaCommon\Dto\Organisation\MotTestLogSummaryDto
     */
    public function getMotTestLogSummaryForOrganisation($organisationId)
    {
        $this->authSrv->assertGrantedAtOrganisation(PermissionAtOrganisation::MOT_TEST_LIST_AT_AE, $organisationId);

        $countOfTestsSummary = $this->motTestRepository->getCountOfMotTestsSummary($organisationId);

        return $this->mapper->toDto($countOfTestsSummary);
    }
}
