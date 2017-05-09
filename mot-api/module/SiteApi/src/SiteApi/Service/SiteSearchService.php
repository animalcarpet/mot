<?php

namespace SiteApi\Service;

use Doctrine\ORM\EntityManager;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Dto\Search\SiteSearchParamsDto;
use DvsaCommon\Dto\Site\SiteListDto;
use DvsaCommon\Dto\Site\VehicleTestingStationDto;
use DvsaCommonApi\Service\AbstractService;
use DvsaCommonApi\Service\Exception\NotFoundException;
use DvsaEntities\DqlBuilder\SearchParam\SiteSearchParam;
use DvsaEntities\Entity\Site;
use DvsaEntities\Repository\SiteRepository;
use SiteApi\Service\Mapper\VtsMapper;

/**
 * Service which creates/edits new VTS.
 */
class SiteSearchService extends AbstractService
{
    /** @var MotAuthorisationServiceInterface */
    protected $authService;
    protected $vtsMapper;
    /** @var SiteRepository */
    private $siteRepository;

    public function __construct(
        EntityManager $entityManager,
        SiteRepository $siteRepository,
        MotAuthorisationServiceInterface $authService,
        VtsMapper $vtsMapper
    ) {
        parent::__construct($entityManager);
        $this->siteRepository = $siteRepository;
        $this->authService = $authService;
        $this->vtsMapper = $vtsMapper;
    }

    /**
     * Assert the permission to search for site and return the result.
     *
     * @param SiteSearchParamsDto $params
     *
     * @return \DvsaCommon\Dto\Site\SiteListDto
     */
    public function findSites(SiteSearchParamsDto $params)
    {
        $this->authService->assertGranted(PermissionInSystem::DVSA_SITE_SEARCH);

        $searchParams = (new SiteSearchParam())
            ->fromDto($params)
            ->process();

        $sites = [];
        $sitesCount = $this->siteRepository->findSitesCount($searchParams);
        if ($sitesCount != 0) {
            $sites = $this->siteRepository->findSites($searchParams);
        }

        return (new SiteListDto())
            ->setTotalResultCount($sitesCount)
            ->setData($sites);
    }

    /**
     * @param string $siteNumber
     *
     * @return VehicleTestingStationDto
     *
     * @throws NotFoundException
     */
    public function findSiteByNumber($siteNumber)
    {
        $site = $this->siteRepository->getBySiteNumber(strtoupper(trim($siteNumber)));

        return $this->vtsMapper->toDto($site);
    }
}
