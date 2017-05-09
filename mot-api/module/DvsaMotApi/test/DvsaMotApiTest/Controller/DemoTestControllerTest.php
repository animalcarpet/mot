<?php

namespace DvsaMotApiTest\Controller;

use DvsaCommon\Enum\SiteBusinessRoleCode;
use DvsaEntities\Entity\MotTest;
use DvsaEntities\Entity\Person;
use DvsaMotApi\Controller\DemoTestController;

/**
 * Tests for DemoTestController.
 */
class DemoTestControllerTest extends AbstractMotApiControllerTestCase
{
    protected function setUp()
    {
        $this->controller = new DemoTestController();
        parent::setUp();
    }

    public function test_Create_with_valid_data()
    {
        $newMotTestNumber = 999;
        $person = (new Person())->setId(5);

        $this->mockValidAuthorization([SiteBusinessRoleCode::TESTER], null, $person);

        $this->request->setMethod('post');
        $this->request->getPost()->set('vehicleId', '1');
        $this->request->getPost()->set('primaryColour', 'Blue');
        $this->request->getPost()->set('hasRegistration', true);

        $mockMotTestService = $this->getMockMotTestService();
        $mockMotTestService->expects($this->once())
            ->method('createMotTest')
            ->will($this->returnValue((new MotTest())->setNumber($newMotTestNumber)->setTester($person)));

        $result = $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertInstanceOf(\Zend\View\Model\JsonModel::class, $result);
        $this->assertEquals(['data' => ['motTestNumber' => $newMotTestNumber]], $result->getVariables());
    }
}
