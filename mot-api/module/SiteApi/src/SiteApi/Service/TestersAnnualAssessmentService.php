<?php

namespace SiteApi\Service;

use DvsaAuthorisation\Service\AuthorisationServiceInterface;
use DvsaCommon\ApiClient\Site\Dto\TestersAnnualAssessmentDto;
use DvsaCommon\Auth\PermissionAtSite;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaEntities\Repository\SiteBusinessRoleMapRepository;
use SiteApi\Mapper\TestersAnnualAssessmentMapper;

class TestersAnnualAssessmentService implements AutoWireableInterface
{
    private $siteBusinessRoleMapRepository;
    private $testersAnnualAssessmentMapper;
    private $authorisationService;

    public function __construct(
        SiteBusinessRoleMapRepository $siteBusinessRoleMapRepository,
        TestersAnnualAssessmentMapper $testersAnnualAssessmentMapper,
        AuthorisationServiceInterface $authorisationService
    )
    {
        $this->siteBusinessRoleMapRepository = $siteBusinessRoleMapRepository;
        $this->testersAnnualAssessmentMapper = $testersAnnualAssessmentMapper;
        $this->authorisationService = $authorisationService;
    }

    /**
     * @param int $siteId
     * @return TestersAnnualAssessmentDto
     */
    public function getTestersAnnualAssessment($siteId)
    {
        $this->authorisationService->assertGrantedAtSite(PermissionAtSite::TESTERS_ANNUAL_ASSESSMENT_VIEW, $siteId);

        $groupA = $this->siteBusinessRoleMapRepository->getTestersWithTheirAnnualAssessmentsForGroupA($siteId);
        $groupB = $this->siteBusinessRoleMapRepository->getTestersWithTheirAnnualAssessmentsForGroupB($siteId);

        $dto = new TestersAnnualAssessmentDto();
        $dto->setGroupAAssessments($this->testersAnnualAssessmentMapper->mapToDto($groupA));
        $dto->setGroupBAssessments($this->testersAnnualAssessmentMapper->mapToDto($groupB));

        return $dto;
    }
}