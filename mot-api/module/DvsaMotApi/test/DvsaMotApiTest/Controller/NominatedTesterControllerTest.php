<?php

namespace DvsaMotApiTest\Controller;

use DvsaCommon\Constants\Role;
use DvsaMotApi\Controller\TesterController;
use DvsaCommonApi\Service\Exception\NotFoundException;
use Zend\Http\Request;

/**
 * Class NominatedTesterControllerTest.
 */
class NominatedTesterControllerTest extends AbstractMotApiControllerTestCase
{
    public function testGetCanBeAccessed()
    {
        $this->mockValidAuthorization([Role::USER]);

        $username = 'tester1';

        $expectedNominatedTesterData = ['username' => $username, 'vtsSites' => []];
        $expectedData = ['data' => $expectedNominatedTesterData];

        $mockNominatedTesterService = $this->getMockServiceManagerClass(
            'TesterService', \DvsaMotApi\Service\TesterService::class
        );
        $mockNominatedTesterService->expects($this->once())
                                   ->method('getTesterData')
                                   ->with($username)
                                   ->will($this->returnValue($expectedNominatedTesterData));

        $this->controller = new TesterController($mockNominatedTesterService);
        $this->setUpController($this->controller);

        $this->routeMatch->setParam('id', $username);

        $result = $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals($expectedData, $result->getVariables());
    }

    /**
     * @expectedException     \DvsaCommonApi\Service\Exception\NotFoundException
     * @expectedExceptionCode 404
     */
    public function testGetReturnsErrorForInvalidNominatedTester()
    {
        $this->mockValidAuthorization([Role::USER]);

        $username = 'doesnotexist';

        $this->routeMatch->setParam('id', $username);

        $mockNominatedTesterService = $this->getMockServiceManagerClass(
            'TesterService', \DvsaMotApi\Service\TesterService::class
        );
        $mockNominatedTesterService->expects($this->once())
                                   ->method('getTesterData')
                                   ->with($username)
                                   ->will($this->throwException(new NotFoundException('Tester', $username)));

        $this->controller = new TesterController($mockNominatedTesterService);
        $this->setUpController($this->controller);

        $result = $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();

        $this->assertResponseStatusAndResultHasError(
            $response,
            404,
            $result,
            "Tester $username not found",
            NotFoundException::ERROR_CODE_NOT_FOUND
        );
    }
}
