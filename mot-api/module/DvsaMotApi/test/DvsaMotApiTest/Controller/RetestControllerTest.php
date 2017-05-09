<?php

namespace DvsaMotApiTest\Controller;

use DvsaCommon\Enum\SiteBusinessRoleCode;
use DvsaCommon\Enum\VehicleClassCode;
use DvsaCommonApi\Service\Exception\ForbiddenException;
use DvsaEntities\Entity\MotTest;
use DvsaEntities\Entity\Person;
use DvsaMotApi\Controller\RetestController;
use Zend\Http\Request;

/**
 * Class RetestControllerTest.
 */
class RetestControllerTest extends AbstractMotApiControllerTestCase
{
    protected function setUp()
    {
        $this->controller = new RetestController();
        parent::setUp();
    }

    /**
     * @expectedException     \DvsaCommonApi\Service\Exception\ForbiddenException
     * @expectedExceptionCode 403
     */
    public function testCreateWithInvalidReturnsBadRequestResponse()
    {
        $forbiddenMessage = 'You are not authorised to test a class 4 vehicle';
        $this->mockValidAuthorization([SiteBusinessRoleCode::TESTER, 'NOT-THE-RIGHT-CLASS']);

        $this->request->setMethod('post');
        $this->request->getPost()->set('vehicleTestingStationId', '1');
        $this->request->getPost()->set('vehicleId', '1');
        $this->request->getPost()->set('primaryColour', 'Blue');
        $this->request->getPost()->set('fuelTypeId', 4);
        $this->request->getPost()->set('vehicleClassCode', VehicleClassCode::CLASS_5);
        $this->request->getPost()->set('hasRegistration', 'true');

        $mockMotTestService = $this->getMockMotTestService();
        $mockMotTestService->expects($this->once())
                           ->method('createMotTest')
                           ->will($this->throwException(new ForbiddenException($forbiddenMessage)));

        $result = $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();

        $this->assertResponseStatusAndResultHasError(
            $response,
            403,
            $result,
            $forbiddenMessage,
            ForbiddenException::ERROR_CODE_FORBIDDEN
        );
    }

    public function testCreateMotRetestWithValid()
    {
        $person = new Person();
        $person->setId(5);

        $this->mockValidAuthorization([SiteBusinessRoleCode::TESTER, 'TESTER-CLASS-4'], null, $person);

        $this->request->setMethod('post');
        $this->request->getPost()->set('vehicleTestingStationId', '1');
        $this->request->getPost()->set('vehicleId', '1');
        $this->request->getPost()->set('primaryColour', 'Blue');
        $this->request->getPost()->set('secondaryColour', 'Red');
        $this->request->getPost()->set('fuelTypeId', 4);
        $this->request->getPost()->set('vehicleClassCode', VehicleClassCode::CLASS_5);
        $this->request->getPost()->set('hasRegistration', true);

        $motTest = new MotTest();
        $motTest->setTester($person);

        $expectedData = ['data' => ['motTestNumber' => null]];

        $mockMotTestService = $this->getMockMotTestService();
        $mockMotTestService->expects($this->once())
            ->method('createMotTest')
            ->will($this->returnValue($motTest));

        $result = $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertInstanceOf(\Zend\View\Model\JsonModel::class, $result);
        $this->assertEquals($expectedData, $result->getVariables());
    }

    public function testCreateMotRetestWithValidAndContingency()
    {
        $person = new Person();
        $person->setId(5);

        $this->mockValidAuthorization([SiteBusinessRoleCode::TESTER, 'TESTER-CLASS-4'], null, $person);

        $this->request->setMethod('post');
        $this->request->getPost()->set('vehicleTestingStationId', '1');
        $this->request->getPost()->set('vehicleId', '1');
        $this->request->getPost()->set('primaryColour', 'Blue');
        $this->request->getPost()->set('secondaryColour', 'Red');
        $this->request->getPost()->set('fuelTypeId', 4);
        $this->request->getPost()->set('vehicleClassCode', VehicleClassCode::CLASS_5);
        $this->request->getPost()->set('hasRegistration', true);
        $this->request->getPost()->set('contingencyId', 3);
        $this->request->getPost()->set('contingencyDto', [
            '_class' => 'DvsaCommon\\Dto\\MotTesting\\ContingencyTestDto',
        ]);

        $motTest = new MotTest();
        $motTest->setTester($person);

        $expectedData = ['data' => ['motTestNumber' => null]];

        $mockMotTestService = $this->getMockMotTestService();
        $mockMotTestService->expects($this->once())
            ->method('createMotTest')
            ->will($this->returnValue($motTest));

        $result = $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertInstanceOf(\Zend\View\Model\JsonModel::class, $result);
        $this->assertEquals($expectedData, $result->getVariables());
    }
}
