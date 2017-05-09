<?php

namespace OrganisationApiTest\Service;

use Doctrine\ORM\EntityManager;
use DvsaCommonApiTest\Service\AbstractServiceTestCase;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Entity\Organisation;
use DvsaEntities\Repository\OrganisationRepository;
use OrganisationApi\Service\Mapper\OrganisationMapper;
use OrganisationApi\Service\OrganisationService;
use PHPUnit_Framework_MockObject_MockObject as MockObj;

/**
 * Class OrganisationServiceTest.
 */
class OrganisationServiceTest extends AbstractServiceTestCase
{
    const ORG_ID = 9;

    /** @var OrganisationRepository|MockObj */
    private $mockOrganisationRepo;
    /** @var EntityManager|MockObj */
    private $entityManager;
    /** @var OrganisationService */
    private $service;
    /** @var Organisation */
    private $organisation;

    public function setUp()
    {
        $this->entityManager = XMock::of(EntityManager::class);
        $this->mockOrganisationRepo = XMock::of(OrganisationRepository::class);

        $this->service = new OrganisationService(
            $this->entityManager,
            $this->mockOrganisationRepo,
            new OrganisationMapper()
        );

        $this->organisation = (new Organisation())
            ->setId(self::ORG_ID);
    }

    public function testIncrementSlotBalance()
    {
        $this->mockOrganisationRepo->expects($this->once())
            ->method('updateSlotBalance')
            ->with(self::ORG_ID, 1);

        $this->service->incrementSlotBalance($this->organisation);
    }

    public function testDecrementSlotBalance()
    {
        $this->mockOrganisationRepo->expects($this->once())
            ->method('updateSlotBalance')
            ->with(self::ORG_ID, -1);

        $this->service->decrementSlotBalance($this->organisation);
    }

    public function testFindOrganisationNameBySiteId_organisationFound()
    {
        $siteId = 4;
        $this->mockOrganisationRepo->expects($this->once())->method('findOrganisationNameBySiteId')
            ->with($siteId)->willReturn((new Organisation())->setName('orgName')->setId(5));

        $result = $this->service->findOrganisationNameBySiteId($siteId);
        $this->assertEquals(['id' => 5, 'name' => 'orgName'], $result);
    }

    public function testFindOrganisationNameBySiteId_organisationNotFound()
    {
        $siteId = 5;
        $this->mockOrganisationRepo->expects($this->once())->method('findOrganisationNameBySiteId')
            ->with($siteId)->willReturn(null);

        $result = $this->service->findOrganisationNameBySiteId($siteId);
        $this->assertEquals([], $result);
    }
}
