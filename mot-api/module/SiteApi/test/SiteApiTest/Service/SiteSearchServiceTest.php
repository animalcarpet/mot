<?php

namespace SiteApiTest\Service;

use Doctrine\ORM\EntityManager;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Dto\Search\SiteSearchParamsDto;
use DvsaCommon\Dto\Site\SiteListDto;
use DvsaCommonApiTest\Service\AbstractServiceTestCase;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\DqlBuilder\SearchParam\SiteSearchParam;
use DvsaEntities\Repository\SiteRepository;
use PHPUnit_Framework_MockObject_MockObject as MockObj;
use SiteApi\Service\Mapper\VtsMapper;
use SiteApi\Service\SiteSearchService;

/**
 * Class SiteSearchServiceTest.
 */
class SiteSearchServiceTest extends AbstractServiceTestCase
{
    const SITE_NAME = 'name';
    protected $siteMapper;

    /** @var EntityManager|MockObj $entityManager */
    private $entityManager;
    /** @var SiteRepository|MockObj $repository */
    private $repository;
    /** @var MotAuthorisationServiceInterface|MockObj $auth */
    private $auth;
    /** @var SiteSearchService $service */
    private $service;

    public function setup()
    {
        $this->entityManager = XMock::of(EntityManager::class);
        $this->repository = XMock::of(SiteRepository::class);
        $this->auth = XMock::of(MotAuthorisationServiceInterface::class);
        $this->siteMapper = XMock::of(VtsMapper::class);

        $this->service = new SiteSearchService(
            $this->entityManager,
            $this->repository,
            $this->auth,
            $this->siteMapper
        );
    }

    public function testFindSites()
    {
        $this->auth->expects($this->once())
            ->method('assertGranted')
            ->with(PermissionInSystem::DVSA_SITE_SEARCH)
            ->willReturn(true);

        $this->repository->expects($this->once())
            ->method('findSitesCount')
            ->with($this->getSearchParams())
            ->willReturn(2);

        $this->repository->expects($this->once())
            ->method('findSites')
            ->with($this->getSearchParams())
            ->willReturn([]);

        $this->assertInstanceOf(SiteListDto::class, $this->service->findSites($this->getSiteSearchParamsDto()));
    }

    private function getSiteSearchParamsDto()
    {
        $dto = (new SiteSearchParamsDto())
            ->setSiteName(self::SITE_NAME);

        return $dto;
    }

    private function getSearchParams()
    {
        $searchParam = (new SiteSearchParam())
            ->fromDto($this->getSiteSearchParamsDto());

        return $searchParam;
    }
}
