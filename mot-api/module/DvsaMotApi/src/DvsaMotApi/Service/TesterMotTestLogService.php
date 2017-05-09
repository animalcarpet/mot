<?php

namespace DvsaMotApi\Service;

use DvsaAuthorisation\Service\AuthorisationServiceInterface;
use DvsaEntities\Repository\MotTestRepository;
use OrganisationApi\Service\Mapper\MotTestLogSummaryMapper;

class TesterMotTestLogService
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
     * an tester.
     *
     * @return \DvsaCommon\Dto\Organisation\MotTestLogSummaryDto
     */
    public function getMotTestLogSummaryForTester($testerId)
    {
        $countOfTestsSummary = $this->motTestRepository->getCountOfTesterMotTestsSummary($testerId);

        return $this->mapper->toDto($countOfTestsSummary);
    }
}
