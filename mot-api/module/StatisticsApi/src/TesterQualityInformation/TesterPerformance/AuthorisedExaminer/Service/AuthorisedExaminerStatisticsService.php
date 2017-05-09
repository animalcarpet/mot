<?php

namespace Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\AuthorisedExaminer\Service;

use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\AuthorisedExaminer\Mapper\AuthorisedExaminerSiteMapper;
use DvsaCommon\ApiClient\Statistics\AePerformance\Dto\AuthorisedExaminerSitesPerformanceDto;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Auth\PermissionAtOrganisation;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaCommon\Utility\PaginationCalculator;
use DvsaCommon\Utility\TypeCheck;
use DvsaCommonApi\Service\Exception\NotFoundException;
use DvsaEntities\Entity\Site;
use DvsaEntities\Repository\OrganisationRepository;
use DvsaEntities\Repository\SiteRepository;

class AuthorisedExaminerStatisticsService implements AutoWireableInterface
{
    private $siteRepository;
    private $authorisationService;
    private $authorisedExaminerSiteMapper;
    private $organisationRepository;

    public function __construct(
        SiteRepository $siteRepository,
        OrganisationRepository $organisationRepository,
        MotAuthorisationServiceInterface $authorisationService,
        AuthorisedExaminerSiteMapper $authorisedExaminerSiteMapper
    ) {
        $this->authorisationService = $authorisationService;
        $this->organisationRepository = $organisationRepository;
        $this->authorisedExaminerSiteMapper = $authorisedExaminerSiteMapper;
        $this->siteRepository = $siteRepository;
    }

    /**
     * @param $aeId
     * @param int $page
     * @param int $limit
     *
     * @return AuthorisedExaminerSitesPerformanceDto
     */
    public function getListForPage($aeId, $page, $limit)
    {
        $this->authorisationService->assertGrantedAtOrganisation(PermissionAtOrganisation::AE_VIEW_TEST_QUALITY, $aeId);

        $offset = PaginationCalculator::calculateItemOffsetFromPageNumber($page, $limit);
        $siteCount = $this->organisationRepository->getOrganisationSiteCount($aeId);
        $this->throw404IfPageIsOutOfBounds($offset, $siteCount);
        $sites = $this->siteRepository->getSitesTestQualityForOrganisationId($aeId, $offset, $limit);

        return $this->returnDto($sites, $siteCount);
    }

    /**
     * @param Site[] $sites
     * @param int    $siteCount
     *
     * @return AuthorisedExaminerSitesPerformanceDto
     */
    protected function returnDto($sites, $siteCount)
    {
        $sitesPerformanceDto = new AuthorisedExaminerSitesPerformanceDto();
        $sitesPerformanceDto->setSiteTotalCount($siteCount);

        if (is_array($sites)) {
            $sitesDtos = [];
            TypeCheck::assertCollectionOfClass($sites, Site::class);

            foreach ($sites as $site) {
                $sitesDtos[] = $this->authorisedExaminerSiteMapper->toDto($site);
            }

            $sitesPerformanceDto->setSites($sitesDtos);
        }

        return $sitesPerformanceDto;
    }

    /**
     * @param int $offset
     * @param int $siteCount
     *
     * @throws NotFoundException
     */
    private function throw404IfPageIsOutOfBounds($offset, $siteCount)
    {
        if ($offset == 0 && $siteCount == 0) {
            return;
        } elseif (!PaginationCalculator::offsetExists($offset, $siteCount)) {
            throw new NotFoundException('Sites associated to AE');
        }
    }
}
