<?php

namespace DataCatalogApiTest\Service;

use DvsaCommonTest\TestUtils\MockHandler;
use DataCatalogApi\Service\DataCatalogService;
use DvsaEntities\Entity\EnforcementDecision;
use DvsaEntities\Entity\EnforcementDecisionCategory;
use DvsaEntities\Entity\EnforcementDecisionOutcome;
use DvsaEntities\Entity\EnforcementDecisionScore;
use DvsaCommonApiTest\Service\AbstractServiceTestCase;

/**
 * Class DataCatalogServiceTest.
 */
class DataCatalogServiceTest extends AbstractServiceTestCase
{
    /**
     * Test DataCatalogService->getEnforcementDecisionData.
     */
    public function testGetEnforcementDecisionData()
    {
        $expectedData = $this->getExpectedEnforcementDecisionData();
        $mockHydrator = $this->getMockHydrator();
        $mockRepository = $this->getMockRepository();
        $mockEntityManager = $this->getMockEntityManager();
        $mockAuthService = $this->getMockAuthorizationService(true);

        $mockRepositoryHandler = new MockHandler($mockRepository, $this);
        $mockRepositoryHandler
            ->next('findBy')
            ->with([], ['position' => 'ASC'])
            ->will($this->returnValue($expectedData['decisions']));
        $mockRepositoryHandler
            ->next('findBy')
            ->with([], ['position' => 'ASC'])
            ->will($this->returnValue($expectedData['categories']));
        $mockRepositoryHandler
            ->next('findBy')
            ->with([], ['position' => 'ASC'])
            ->will($this->returnValue($expectedData['outcomes']));
        $mockRepositoryHandler
            ->next('findBy')
            ->with([], ['position' => 'ASC'])
            ->will($this->returnValue($expectedData['scores']));

        $mockEntityManagerHandler = new MockHandler($mockEntityManager, $this);
        $mockEntityManagerHandler
            ->next('getRepository')
            ->with(\DvsaEntities\Entity\EnforcementDecision::class)
            ->will($this->returnValue($mockRepository));
        $mockEntityManagerHandler
            ->next('getRepository')
            ->with(\DvsaEntities\Entity\EnforcementDecisionCategory::class)
            ->will($this->returnValue($mockRepository));
        $mockEntityManagerHandler
            ->next('getRepository')
            ->with(\DvsaEntities\Entity\EnforcementDecisionOutcome::class)
            ->will($this->returnValue($mockRepository));
        $mockEntityManagerHandler
            ->next('getRepository')
            ->with(\DvsaEntities\Entity\EnforcementDecisionScore::class)
            ->will($this->returnValue($mockRepository));

        $dataCatalogService = new DataCatalogService($mockEntityManager, $mockHydrator, $mockAuthService);
        $dataCatalogService->getEnforcementDecisionData();
        $dataCatalogService->getEnforcementDecisionCategoryData();
        $dataCatalogService->getEnforcementDecisionOutcomeData();
        $dataCatalogService->getEnforcementDecisionScoreData();
    }

    /**
     * @return array
     */
    public function getExpectedEnforcementDecisionData()
    {
        $expectedData = [
            'decisions' => [
                new EnforcementDecision(),
                new EnforcementDecision(),
                new EnforcementDecision(),
            ],
            'categories' => [
                new EnforcementDecisionCategory(),
                new EnforcementDecisionCategory(),
                new EnforcementDecisionCategory(),
                new EnforcementDecisionCategory(),
            ],
            'outcomes' => [
                new EnforcementDecisionOutcome(),
                new EnforcementDecisionOutcome(),
                new EnforcementDecisionOutcome(),
                new EnforcementDecisionOutcome(),
            ],
            'scores' => [
                new EnforcementDecisionScore(),
                new EnforcementDecisionScore(),
                new EnforcementDecisionScore(),
                new EnforcementDecisionScore(),
            ],
        ];
        $expectedData['decisions'][0]->setId(1)->setDecision('Not applicable')->setPosition(1);
        $expectedData['decisions'][1]->setId(2)->setDecision('Defect missed')->setPosition(2);
        $expectedData['decisions'][2]->setId(3)->setDecision('Incorrect decision')->setPosition(3);
        $expectedData['categories'][0]->setId(1)->setCategory('Not applicable')->setPosition(1);

        return $expectedData;
    }

    public static function enforcementDecisionExceptionTestDataProvider()
    {
        return [
            ['getEnforcementDecisionData'],
            ['getEnforcementDecisionCategoryData'],
            ['getEnforcementDecisionOutcomeData'],
            ['getEnforcementDecisionScoreData'],
            ['getReasonForSiteVisitData'],
        ];
    }

    /**
     * @expectedException \Exception
     * @dataProvider enforcementDecisionExceptionTestDataProvider
     */
    public function testGetEnforcementDecisionDataThrowsException($invokedMethod)
    {
        $mockHydrator = $this->getMockHydrator();
        $mockEntityManager = $this->getMockEntityManager();
        $mockAuthService = $this->getMockAuthorizationService(true);

        $this->setupAuthServiceToThrowException($mockAuthService);

        $dataCatalogService = new DataCatalogService(
            $mockEntityManager,
            $mockHydrator,
            $mockAuthService
        );

        $dataCatalogService->$invokedMethod();
    }

    protected function setupAuthServiceToThrowException($mock)
    {
        $mock->expects($this->once())
            ->method('assertGranted')
            ->will($this->throwException(new \Exception('Auth not granted')));
    }
}
