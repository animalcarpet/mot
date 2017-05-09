<?php

namespace DvsaMotTestTest\Controller;

use CoreTest\Controller\StubIdentities;
use Dvsa\Mot\Frontend\AuthenticationModule\Model\Identity;
use Dvsa\Mot\Frontend\AuthenticationModule\Model\VehicleTestingStation;
use DvsaCommonTest\Bootstrap;
use DvsaMotTest\Controller\LocationSelectController;
use DvsaMotTest\Data\TesterInProgressTestNumberResource;
use DvsaCommon\HttpRestJson\Client as HttpRestJsonClient;
use DvsaMotTest\Helper\LocationSelectContainerHelper;
use DvsaCommonTest\TestUtils\XMock;

/**
 * Test for LocationSelectController.
 */
class LocationSelectControllerTest extends AbstractDvsaMotTestTestCase
{
    protected $locationSelectContainerHelperMock;
    protected function setUp()
    {
        $this->controller = new LocationSelectController();
        $serviceLocator = Bootstrap::getServiceManager();
        $serviceLocator->setAllowOverride(true);
        $this->locationSelectContainerHelperMock = XMock::of(LocationSelectContainerHelper::class);
        $serviceLocator->setService('LocationSelectContainerHelper', $this->locationSelectContainerHelperMock);
        $this->controller->setServiceLocator(Bootstrap::getServiceManager());
        parent::setUp();
        $this->mockTesterInProgressTestIdResource();
    }

    public function testLocationSelectIndexCanBeAccessedForAuthenticatedRequest()
    {
        $this->mockLoggedInUserManager($this->getTesterWithMultipleVtsData());
        $this->setupLocationSelectRestClientMock($this->getCurrentIdentity(), $this->getTesterWithMultipleVtsData());

        $response = $this->getResponseForAction('index');

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testLocationSelectIndexPostWithInvalidDataEndsWithErrorMessage()
    {
        $this->setupLocationSelectRestClientMock($this->getCurrentIdentity(), $this->getTesterWithMultipleVtsData());
        $this->routeMatch->setParam('action', 'index');
        $this->request->getPost()->set('vtsId', null);
        $this->request->setMethod('post');
        $this->getFlashMessengerMockForAddErrorMessage(LocationSelectController::GARAGE_REQUIRED_ERROR);

        $this->controller->dispatch($this->request);

        $response = $this->controller->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testLocationSelectIndexValidFormChangesCurrentSiteAndRedirectsToHomePage()
    {
        $loggedInUserManagerMock = $this->mockLoggedInUserManager($this->getTesterWithMultipleVtsData());
        $loggedInUserManagerMock->expects($this->once())
            ->method('changeCurrentLocation')
            ->will($this->returnValue(true));
        $this->prepareIndexPostRequest(['vtsId' => 0]);

        $this->controller->dispatch($this->request);

        $response = $this->controller->getResponse();
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertRedirectLocation($response, '/');
    }

    public function testLocationSelectIndexPostWithBackUrlRedirectsBack()
    {
        $this->locationSelectContainerHelperMock->expects($this->once())
            ->method('fetchConfig')
            ->willReturn(['route' => 'vehicle-search', 'params' => []]);
        $this->mockLoggedInUserManager($this->getTesterWithMultipleVtsData());
        $this->prepareIndexPostRequest(
            [
                'vtsId' => 0,
            ]
        );
        $this->controller->dispatch($this->request);

        $response = $this->controller->getResponse();
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertRedirectLocation($response, '/vehicle-search');
    }

    public function testLocationSelectAutomaticallyChoosesSingleVTS()
    {
        $this->locationSelectContainerHelperMock->expects($this->once())
            ->method('fetchConfig')
            ->willReturn(['route' => 'vehicle-search', 'params' => []]);
        $this->mockLoggedInUserManager($this->getTesterWithSingleVtsData(), 'getAllVtsWithSlotBalance');
        $this->prepareIndexGetRequest([]);

        $this->controller->dispatch($this->request);

        $response = $this->controller->getResponse();
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertRedirectLocation($response, '/vehicle-search');
    }

    /**
     * @dataProvider providePublicUrls
     * @expectedException \Zend\Mvc\Router\Exception\RuntimeException
     */
    public function testLocationSelectIndexDoesNotRedirectsOutsideApplication($backUrl)
    {
        $this->locationSelectContainerHelperMock->expects($this->once())
            ->method('fetchConfig')
            ->willReturn(['route' => $backUrl, 'params' => []]);

        $this->mockLoggedInUserManager($this->getTesterWithMultipleVtsData());
        $this->prepareIndexPostRequest(
            [
                'vtsId' => 0,
            ]
        );

        $this->controller->dispatch($this->request);
    }

    public function providePublicUrls()
    {
        return [
            ['http://somewhere.dev.net.dev'],
            ['//somewhere.else.com.example.com'],
        ];
    }

    /**
     * @expectedException \Exception
     */
    public function testLocationSelectDoesNotAllowChangeDuringTestInProgress()
    {
        $this->mockLoggedInUserManager($this->getTesterWithMultipleVtsData());

        $this->getServiceManager()->get('IdentityService')->getIdentity()->setCurrentVts(
            new VehicleTestingStation(
                StubIdentities::stubVtsData()
            )
        );
        $this->mockTesterInProgressTestIdResource(11);
        $this->prepareIndexGetRequest();

        $this->controller->dispatch($this->request);
    }

    private function setupLocationSelectRestClientMock($identity, $testerData)
    {
        if (is_null($identity)) {
            throw new \Exception('Null identity!');
        }

        $userId = $identity->getUserId();
        $restClientMock = \DvsaCommonTest\TestUtils\XMock::of(HttpRestJsonClient::class);
        $restClientMock->expects($this->any())
            ->method('get')
            ->with('tester?userId='.$userId)
            ->will($this->returnValue(['data' => $testerData]));

        $serviceManager = Bootstrap::getServiceManager();
        $serviceManager->setAllowOverride(true);
        $serviceManager->setService(HttpRestJsonClient::class, $restClientMock);
    }

    private function mockLoggedInUserManager($testerData, $method = 'getTesterData')
    {
        $loggedInUserManagerMock = \DvsaCommonTest\TestUtils\XMock::of(\Application\Service\LoggedInUserManager::class);
        $loggedInUserManagerMock->expects($this->any())
            ->method($method)
            ->will($this->returnValue($testerData));
        $serviceManager = $this->getServiceManager();
        $serviceManager->setAllowOverride(true);
        $serviceManager->setService('LoggedInUserManager', $loggedInUserManagerMock);

        return $loggedInUserManagerMock;
    }

    private function mockTesterInProgressTestIdResource($testId = null)
    {
        $resourceMock = \DvsaCommonTest\TestUtils\XMock::of(TesterInProgressTestNumberResource::class);
        $resourceMock->expects($this->any())
            ->method('get')
            ->will($this->returnValue($testId));

        $serviceManager = Bootstrap::getServiceManager();
        $serviceManager->setAllowOverride(true);
        $serviceManager->setService(TesterInProgressTestNumberResource::class, $resourceMock);
    }

    private function getTesterWithSingleVtsData()
    {
        return [
            'username' => 'location_test_user',
            'active' => '1',
            'vtsSites' => [
                [
                    'id' => '1',
                    'slots' => '12',
                    'name' => 'test_name',
                    'address' => 'test_address',
                    'slotsWarning' => '15',
                    'slotsInUse' => '2',
                ],
            ],
        ];
    }

    private function getTesterWithMultipleVtsData()
    {
        $testerData = $this->getTesterWithSingleVtsData();
        $testerData['vtsSites'][] = [
            [
                'id' => '2',
                'slots' => '22',
                'name' => 'test_name2',
                'address' => 'test_address2',
                'slotsWarning' => '152',
                'slotsInUse' => '1',
            ],
        ];

        return $testerData;
    }

    private function prepareIndexPostRequest(array $postParams, array $routeParams = [])
    {
        $this->routeMatch->setParam('action', 'index');
        $this->request->setMethod('post');
        foreach ($routeParams as $name => $value) {
            $this->routeMatch->setParam($name, $value);
        }
        foreach ($postParams as $name => $value) {
            $this->request->getPost()->set($name, $value);
        }
    }

    private function prepareIndexGetRequest(array $getParams = [], array $routeParams = [])
    {
        $this->routeMatch->setParam('action', 'index');
        $this->request->setMethod('get');
        foreach ($routeParams as $name => $value) {
            $this->routeMatch->setParam($name, $value);
        }
        foreach ($getParams as $name => $value) {
            $this->request->getQuery()->set($name, $value);
        }
    }
}
