<?php

namespace DvsaMotApiTest\Controller;

use Doctrine\ORM\EntityManager;
use DvsaCommon\Constants\Role;
use DvsaCommonTest\TestUtils\XMock;
use DvsaElasticSearch\Service\ElasticSearchService;
use DvsaMotApi\Controller\MotTestSearchController;

/**
 * Class MotTestSearchControllerTest.
 */
class MotTestSearchControllerTest extends AbstractMotApiControllerTestCase
{
    protected function setUp()
    {
        $this->controller = new MotTestSearchController();
        parent::setUp();
    }

    public function testGetListCanBeAccessed()
    {
        $this->mockValidAuthorization([Role::VEHICLE_EXAMINER]);

        $this->getMockService()
            ->expects($this->once())
            ->method('findTests')
            ->will($this->returnValue([]));

        $this->request->getQuery()->set('organisationId', 888);

        $this->getResultForAction('get', 'getTests');
        $this->assertResponseStatus(self::HTTP_OK_CODE);
    }

    public function testGetListThrowAnError()
    {
        $this->mockValidAuthorization([Role::VEHICLE_EXAMINER]);

        $this->getResultForAction('get', 'getTests');
        $this->assertResponseStatus(self::HTTP_ERR_400);
    }

    private function getMockService()
    {
        $mockService = $this->getMock(
            ElasticSearchService::class,
            ['findTests'],
            [],
            'ElasticSearchServiceTestsMock',
            false
        );
        $this->serviceManager->setService('ElasticSearchService', $mockService);
        $this->serviceManager->setService(EntityManager::class, XMock::of(EntityManager::class));

        return $mockService;
    }
}

class ElasticSearchServiceTestsMock
{
}
