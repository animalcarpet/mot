<?php

namespace DvsaMotApiTest\Controller;

use DvsaCommon\Constants\Role;
use DvsaMotApi\Controller\TesterController;
use DvsaMotApi\Service\TesterService;
use Zend\Stdlib\Parameters;

/**
 * Class TesterControllerTest.
 */
class TesterControllerTest extends AbstractMotApiControllerTestCase
{
    // Dummy Number for route matches
    const MOT_TEST_NUMBER = 'ABCD1234567980Z';

    protected function setUp()
    {
        parent::setUp();
        $this->mockValidAuthorization(array(Role::VEHICLE_EXAMINER));
    }

    public function testGetListFindTesterDataByCertificateNumber()
    {
        $testerServiceMock = $this
            ->getMockBuilder(TesterService::class)
            ->disableOriginalConstructor()
            ->getMock();
        $testerServiceMock
            ->expects($this->once())
            ->method('findTesterDataByCertificateNumber')
            ->will($this->returnValue([]));

        $this->controller = new TesterController($testerServiceMock);
        $this->setUpController($this->controller);

        $this->request->setQuery(new Parameters(['certificateNumber' => self::MOT_TEST_NUMBER]));

        $this->controller->dispatch($this->request);

        $response = $this->controller->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testGetListFindByUserId()
    {
        $testerServiceMock = $this
            ->getMockBuilder(TesterService::class)
            ->disableOriginalConstructor()
            ->getMock();
        $testerServiceMock
            ->expects($this->once())
            ->method('getTesterDataByUserId')
            ->will($this->returnValue([]));

        $this->controller = new TesterController($testerServiceMock);
        $this->setUpController($this->controller);

        $this->request->setQuery(new Parameters(['userId' => '12345']));

        $this->controller->dispatch($this->request);

        $response = $this->controller->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testGetFindById()
    {
        $testerServiceMock = $this
            ->getMockBuilder(TesterService::class)
            ->disableOriginalConstructor()
            ->getMock();
        $testerServiceMock
            ->expects($this->once())
            ->method('getTesterData')
            ->will($this->returnValue([]));

        $this->controller = new TesterController($testerServiceMock);
        $this->setUpController($this->controller);

        $this->request->setQuery(new Parameters(['id' => '12345']));
        $this->controller->dispatch($this->request);

        $response = $this->controller->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testGetInProgressTestIdAction()
    {
        $personId = 13;
        $testInProgressId = 31;
        $this->routeMatch->setParam('action', 'getInProgressTestId');
        $this->routeMatch->setParam('id', $personId);

        $testerServiceMock = $this
            ->getMockBuilder(TesterService::class)
            ->disableOriginalConstructor()
            ->getMock();
        $testerServiceMock
            ->expects($this->once())
            ->method('findInProgressTestIdForTester')
            ->with($this->equalTo($personId))
            ->will($this->returnValue($testInProgressId));

        $this->controller = new TesterController($testerServiceMock);
        $this->setUpController($this->controller);

        $this->controller->dispatch($this->request);

        $response = $this->controller->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testGetVtsWithSlotBalance()
    {
        $personId = 13;
        $testInProgressId = 31;
        $this->routeMatch->setParam('action', 'getVtsWithSlotBalance');
        $this->routeMatch->setParam('id', $personId);

        $testerServiceMock = $this
            ->getMockBuilder(TesterService::class)
            ->disableOriginalConstructor()
            ->getMock();
        $testerServiceMock
            ->expects($this->once())
            ->method('getTesterData')
            ->with($this->equalTo($personId), true)
            ->will($this->returnValue($testInProgressId));

        $this->controller = new TesterController($testerServiceMock);
        $this->setUpController($this->controller);

        $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();

        $this->assertEquals(self::HTTP_OK_CODE, $response->getStatusCode());
    }

    public function testDataReturnedFromGetVtsWithSlotBalance()
    {
        $personId = 13;
        $testInProgressId = 31;
        $testerServiceMock = $this
            ->getMockBuilder(TesterService::class)
            ->disableOriginalConstructor()
            ->getMock();
        $testerServiceMock
            ->expects($this->once())
            ->method('getTesterData')
            ->with($this->equalTo($personId), true)
            ->will($this->returnValue($testInProgressId));

        $this->controller = new TesterController($testerServiceMock);
        $this->setUpController($this->controller);
        $this->controller->dispatch($this->request);

        $result = $this->getResultForAction('get', 'getVtsWithSlotBalance', ['id' => $personId]);
        $this->assertResponseStatusAndResult(self::HTTP_OK_CODE, ['data' => $testInProgressId], $result);
    }
}
