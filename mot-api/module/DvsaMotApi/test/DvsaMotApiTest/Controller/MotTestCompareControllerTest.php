<?php

namespace DvsaMotApiTest\Controller;

use DvsaCommon\Constants\Role;
use DvsaCommonTest\TestUtils\XMock;
use DvsaMotApi\Controller\MotTestCompareController;
use DvsaMotApi\Service\MotTestCompareService;
use Zend\Http\Request;

/**
 * Class MotTestCompareControllerTest.
 */
class MotTestCompareControllerTest extends AbstractMotApiControllerTestCase
{
    const MOT_TEST_VE_NUMBER = '1234567892031';

    const MOT_TEST_TESTER_NUMBER = '1234567892021';

    protected function setUp()
    {
        $this->setController(new MotTestCompareController());
        parent::setUp();
    }

    public function testGetCanBeAccessed()
    {
        $this->mockValidAuthorization([Role::VEHICLE_EXAMINER]);

        $expectedMotTestData = ['motTestNumber' => self::MOT_TEST_VE_NUMBER];
        $expectedData = ['data' => $expectedMotTestData];

        $this->routeMatch->setParam('motTestNumber', self::MOT_TEST_VE_NUMBER);

        $mockCompareService = $this->getMockMotTestCompareService();
        $mockCompareService
            ->expects($this->once())
            ->method('getMotTestCompareData')
            ->with(self::MOT_TEST_VE_NUMBER)
            ->will($this->returnValue($expectedMotTestData));

        $result = $this->controller->dispatch($this->request);

        $this->assertResponseStatusAndResult(self::HTTP_OK_CODE, $expectedData, $result);
    }

    public function testCompareMotTestCanBeAccessed()
    {
        $this->mockValidAuthorization([Role::VEHICLE_EXAMINER]);

        $this->routeMatch->setParam('action', 'compareMotTest');

        $this->request->setMethod('get');
        $this->request->getQuery()->set('motTestNumber', self::MOT_TEST_VE_NUMBER);
        $this->request->getQuery()->set('motTestNumberToCompare', self::MOT_TEST_TESTER_NUMBER);

        $expectedMotTestData = ['number' => self::MOT_TEST_VE_NUMBER];
        $expectedData = ['data' => $expectedMotTestData];

        $mockCompareService = $this->getMockMotTestCompareService();
        $mockCompareService
            ->expects($this->once())
            ->method('getMotTestCompareDataFromTwoTest')
            ->with(self::MOT_TEST_VE_NUMBER, self::MOT_TEST_TESTER_NUMBER)
            ->will($this->returnValue($expectedMotTestData));

        $result = $this->controller->dispatch($this->request);

        $this->assertResponseStatusAndResult(self::HTTP_OK_CODE, $expectedData, $result);
    }

    public function testGetService()
    {
        $this->assertEquals(
            $this->getMockMotTestCompareService(),
            XMock::invokeMethod($this->getController(), 'getService')
        );
    }

    private function getMockMotTestCompareService()
    {
        return $this->getMockServiceManagerClass(
            'MotTestCompareService', MotTestCompareService::class
        );
    }
}
