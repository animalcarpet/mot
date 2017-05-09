<?php

namespace CoreTest\Controller;

use Application\Service\LoggedInUserManager;
use CoreTest\Service\StubCatalogService;
use Core\Controller\AbstractDvsaActionController;
use Core\Service\LazyMotFrontendAuthorisationService;
use Core\Service\MotFrontendIdentityProvider;
use DvsaCommonTest\Bootstrap;
use DvsaCommonTest\TestUtils\TestCaseTrait;
use DvsaCommonTest\TestUtils\XMock;
use DvsaCommon\Auth\MotIdentityProvider;
use DvsaCommon\HttpRestJson\Client as HttpRestJsonClient;
use DvsaCommon\HttpRestJson\Exception\NotFoundException;
use DvsaCommon\HttpRestJson\Exception\ValidationException;
use DvsaCommon\Model\ListOfRolesAndPermissions;
use DvsaCommon\Model\PersonAuthorization;
use DvsaFeature\FeatureToggles;
use Dvsa\Mot\ApiClient\HttpClient\Factory;
use Dvsa\Mot\Frontend\AuthenticationModule\Model\Identity;
use Dvsa\Mot\Frontend\Test\StubIdentityAdapter;
use Dvsa\OpenAM\OpenAMClient;
use Dvsa\OpenAM\OpenAMClientInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response as GuzzleHttpResponse;
use PHPUnit_Framework_TestCase;
use Zend\Authentication\AuthenticationService;
use Zend\Authentication\Result;
use Zend\Authentication\Storage\NonPersistent;
use Zend\Http\Header\Location;
use Zend\Http\Request;
use Zend\Http\Response;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Router\Http\TreeRouteStack as HttpRouter;
use Zend\Mvc\Router\RouteMatch;
use Zend\ServiceManager\ServiceManager;
use Zend\Stdlib\Parameters;

/**
 * Class AbstractFrontendControllerTestCase.
 *
 * use CoreTest\Controller\AbstractFrontendControllerTestCase;
 */
abstract class AbstractFrontendControllerTestCase extends PHPUnit_Framework_TestCase
{
    use TestCaseTrait;

    const NAME_DEFAULT_API_SERVICE = HttpRestJsonClient::class;
    const CURRENT_VTS_ID = 1;

    const HTTP_OK_CODE = 200;
    const HTTP_REDIRECT_CODE = 302;
    const HTTP_ERR_404 = 404;

    /**
     * @var ServiceManager
     */
    protected $serviceManager;

    /**
     * @var \Zend\Mvc\Controller\AbstractActionController
     */
    protected $controller;

    /**
     * @var \Zend\Http\Request
     */
    protected $request;

    /**
     * @var \Zend\Http\Response
     */
    protected $response;

    /**
     * @var \Zend\Mvc\Router\RouteMatch
     */
    protected $routeMatch;

    /**
     * @var \Zend\Mvc\MvcEvent
     */
    protected $event;

    protected $restClientServiceName;

    protected $restController;

    /**
     * Standard set up: ASSUMES that the service manager has already been set.
     *
     * Using configuration from the SM, we create a routing infrastructure such that the
     * default action is "index" for the given $this->controller, again this MUST have been
     * set before calling this function or null-pointer/non-object messages will arise.
     */
    protected function setUp()
    {
        $this->createHttpRequestForController('index');

        $this->event->setRouter($this->createRouter());

        $serviceManager = $this->getServiceManager();
        $this->controller->setServiceLocator($serviceManager);

        $serviceManager->setAllowOverride(true);
        // Pretty much every controller can use the CatalogService, so override it for all of them
        $serviceManager->setService('CatalogService', new StubCatalogService());

        $serviceManager->setService(HttpRestJsonClient::class, XMock::of(HttpRestJsonClient::class));

        /*
         * Reset identity/roles to an empty, but logged-in user.
         */
        $this->setupAuthenticationServiceForIdentity(StubIdentityAdapter::asAnonymous());
    }

    protected function tearDown()
    {
        unset(
            $this->serviceManager,
            $this->restClientServiceName,
            $this->restController,
            $this->controller,

            $this->request,
            $this->response,
            $this->routeMatch,
            $this->event
        );
    }

    /**
     * @param array $featureToggles
     *
     * @return $this
     */
    public function withFeatureToggles(array $featureToggles = [])
    {
        $serviceManager = $this->getServiceManager();
        $config = $serviceManager->get('Config');

        $map = [];
        foreach ($featureToggles as $name => $value) {
            $map[] = [(string) $name, (bool) $value];
            $config['feature_toggle'][$name] = $value;
        }

        $featureToggles = $this
            ->getMockBuilder(FeatureToggles::class)
            ->disableOriginalConstructor()
            ->getMock();
        $featureToggles
            ->method('isEnabled')
            ->will($this->returnValueMap($map));

        $serviceManager->setService('Feature\FeatureToggles', $featureToggles);
        $serviceManager->setService('Config', $config);

        return $this;
    }

    /**
     * Helper: Answers an instance of a simple route stack so we can dispatch events to
     * the controller under test. The service manager MUST be set by this stage or an
     * exception is thrown.
     *
     * NO INTERNAL STATE MODIFIED.
     *
     * @return \Zend\Mvc\Router\SimpleRouteStack
     */
    public function createRouter()
    {
        $config = $this->getServiceManager()->get('Config');
        $routerConfig = isset($config['router']) ? $config['router'] : [];

        return HttpRouter::factory($routerConfig);
    }

    /**
     * Helper: Given the name of a function, $controllerName, creates a new.
     *
     * @param $controllerName String
     *
     * @return $this AbstractFrontendControllerTestCase
     */
    public function createHttpRequestForController($controllerName)
    {
        return $this
            ->setControllerHandler($controllerName)
            ->resetRequest();
    }

    /**
     * Helper: Creates a new route matcher such that $controllerName will be given
     * the chance to handle any future dispatched events during a test sequence.
     *
     * @param $controllerName
     *
     * @return $this AbstractFrontendControllerTestCase
     */
    public function setControllerHandler($controllerName)
    {
        $this->routeMatch = new RouteMatch(['controller' => $controllerName]);

        $this->event = new MvcEvent();

        $this->event->setRouteMatch($this->routeMatch);

        $this->controller->setEvent($this->event);

        return $this;
    }

    /**
     * Mocks the OpenAMClient that is now used from the web frontend.
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function mockOpenAMClient()
    {
        $mockOpenAMClient = $this->getMockBuilder(OpenAMClient::class)
            ->disableOriginalConstructor()
            ->setMethods(['validateCredentials'])
            ->getMock();
        $mockOpenAMClient->expects($this->any())
            ->method('validateCredentials')
            ->will($this->returnValue(true));
        $this->getServiceManager()->setService(OpenAMClientInterface::class, $mockOpenAMClient);

        return $mockOpenAMClient;
    }

    /**
     * Helper: Resets the internal ->request to a new Zend\Http\Request instance.
     *
     * @return $this AbstractFrontendControllerTestCase
     */
    public function resetRequest()
    {
        $this->request = new Request();

        return $this;
    }

    /**
     * @param $controller AbstractDvsaActionController
     *
     * @return AbstractDvsaActionController
     */
    protected function setController(AbstractDvsaActionController $controller = null)
    {
        $this->controller = $controller;
        $this->controller->setServiceLocator(Bootstrap::getServiceManager());

        return $this;
    }

    /**
     * @return AbstractDvsaActionController
     */
    protected function getController()
    {
        return $this->controller;
    }

    /**
     * @param StubIdentityAdapter $identityAdapter
     *
     * @throws \Exception
     */
    protected function setupAuthenticationServiceForIdentity(StubIdentityAdapter $identityAdapter)
    {
        $serviceManager = $this->getServiceManager();

        $authServiceMock = new AuthenticationService(new NonPersistent(), $identityAdapter);

        $serviceManager->setAllowOverride(true);
        $serviceManager->setService('ZendAuthenticationService', $authServiceMock);

        $authServiceMock->clearIdentity();

        /** @var Result $result */
        $result = $authServiceMock->authenticate();

        // Need to replace all references to the old authentication service and identity
        /** @var MotFrontendIdentityProvider $motIdentityProvider */
        $motIdentityProvider = $serviceManager->get('MotIdentityProvider');
        $motIdentityProvider->setZendAuthenticationService($authServiceMock);

        /** @var LoggedInUserManager $loggedInUserManager */
        $loggedInUserManager = $serviceManager->get('LoggedInUserManager');

        $loggedInUserManager->setIdentityProvider($motIdentityProvider);

        if ($result->isValid()) {
            $this->createNewAuthorizationService($result->getIdentity());
        } else {
            $this->createNewAuthorizationService(null);
        }
    }

    protected function setupAuthorizationService($grantedPermissions = [], $grantedRoles = [])
    {
        $serviceManager = $this->getServiceManager();
        $serviceManager->setAllowOverride(true);

        $motIdentity = new Identity();
        $motIdentity->setPersonAuthorization(
            new PersonAuthorization(
                new ListOfRolesAndPermissions($grantedRoles, $grantedPermissions),
                [], [], []
            )
        );
        $this->createNewAuthorizationService($motIdentity);
    }

    /** @var Identity $motIdentity */
    private function createNewAuthorizationService($motIdentity)
    {
        $serviceManager = $this->getServiceManager();

        // doesn't need to do anything, authorization already set
        $mockRestClient = XMock::of(HttpRestJsonClient::class);

        $mock = new LazyMotFrontendAuthorisationService(new MotIdentityProvider($motIdentity), $mockRestClient);

        $serviceManager->setService('AuthorisationService', $mock);
    }

    /** @return Identity */
    protected function getCurrentIdentity()
    {
        return $this->getServiceManager()->get('MotIdentityProvider')->getIdentity();
    }

    protected function getAuthenticationServiceMockForFailure()
    {
        $authServiceMock = \DvsaCommonTest\TestUtils\XMock::of(\Zend\Authentication\AuthenticationService::class);
        $authServiceMock->expects($this->once())
            ->method('hasIdentity')
            ->will($this->returnValue(false));

        $serviceManager = $this->getServiceManager();
        $serviceManager->setAllowOverride(true);
        $serviceManager->setService('ZendAuthenticationService', $authServiceMock);

        return $authServiceMock;
    }

    protected function setServiceManager($serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }

    /**
     * @throws \Exception
     *
     * @return ServiceManager
     */
    protected function getServiceManager()
    {
        if (is_null($this->serviceManager)) {
            throw new \Exception('Service manager must be set before calling setUp method');
        }

        return $this->serviceManager;
    }

    public function setRestClientServiceName($restClientServiceName)
    {
        $this->restClientServiceName = $restClientServiceName;
    }

    /**
     * @param array $payload to mimic the response's body
     * @param int   $status  to mimic the response's status, default to success (200)
     * @param array $headers
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    public function getMockHttpClientFactory($payload = [], $status = 200, $headers = [])
    {
        $response = new GuzzleHttpResponse(
            $status,
            $headers,
            json_encode($payload)
        );

        $httpClient = new Client([
            'handler' => new MockHandler([$response]),
        ]);

        $mockHttpClientFactory = $this->getMock(Factory::class, ['getHttpClient']);
        $mockHttpClientFactory->method('getHttpClient')->willReturn($httpClient);

        return $mockHttpClientFactory;
    }

    public function getRestClientMockForServiceManager()
    {
        if (!$this->restClientServiceName) {
            $this->restClientServiceName = self::NAME_DEFAULT_API_SERVICE;
        }
        $restClientMock = \DvsaCommonTest\TestUtils\XMock::of(HttpRestJsonClient::class);

        $serviceManager = $this->getServiceManager();
        $serviceManager->setAllowOverride(true);
        $serviceManager->setService($this->restClientServiceName, $restClientMock);

        return $restClientMock;
    }

    public function getRestClientMock($method, $returnValue, $param1 = null, $param2 = null)
    {
        $restClientMock = $this->getRestClientMockForServiceManager($this->restClientServiceName);

        $params = array_filter([$param1, $param2]);

        $this->mockMethod($restClientMock, $method, $this->any(), $returnValue, $params);

        return $restClientMock;
    }

    public function getRestClientMockThrowingException($method, $displayMessage = 'REST ERROR!', $params = null)
    {
        $exception = new ValidationException('/', $method, [], 10, [['displayMessage' => $displayMessage]]);

        return $this->getRestClientMockThrowingSpecificException($method, $exception, $params);
    }

    public function getRestClientMockThrowingNotFoundException($method, $displayMessage = 'NOT FOUND')
    {
        $exception = new NotFoundException('/', $method, [], 10, $displayMessage);

        return $this->getRestClientMockThrowingSpecificException($method, $exception);
    }

    public function getRestClientMockThrowingSpecificException($method, $exception, $params = null)
    {
        $restClientMock = $this->getRestClientMockForServiceManager();

        $this->mockMethod($restClientMock, $method, $this->once(), $exception, $params);

        return $restClientMock;
    }

    public function getSessionMock($serviceName = 'MotSession')
    {
        $sessionMock = \DvsaCommonTest\TestUtils\XMock::of(\Zend\Session\Container::class);
        $sessionMock->nominatedTester = null;

        $serviceManager = $this->getServiceManager();
        $serviceManager->setAllowOverride(true);
        $serviceManager->setService($serviceName, $sessionMock);

        return $sessionMock;
    }

    public function getSessionStdClassMock($serviceName = 'MotSession')
    {
        $sessionMock = new \stdClass();

        $serviceManager = $this->getServiceManager();
        $serviceManager->setAllowOverride(true);
        $serviceManager->setService($serviceName, $sessionMock);

        return $sessionMock;
    }

    public function getFlashMessengerMockForOneCall($method, $param)
    {
        $flashMock = \DvsaCommonTest\TestUtils\XMock::of(\Zend\Mvc\Controller\Plugin\FlashMessenger::class);
        $flashMock->expects($this->once())
            ->method($method)
            ->with($param);

        $this->controller->getPluginManager()->setService('flashMessenger', $flashMock, false);

        return $flashMock;
    }

    public function getFlashMessengerMockForManyCalls($method, $param)
    {
        $flashMock = \DvsaCommonTest\TestUtils\XMock::of(\Zend\Mvc\Controller\Plugin\FlashMessenger::class);
        $flashMock->expects($this->any())
            ->method($method)
            ->with($param);

        $this->controller->getPluginManager()->setService('flashMessenger', $flashMock, false);

        return $flashMock;
    }

    public function getFlashMessengerMockForNoCalls($method)
    {
        $flashMock = \DvsaCommonTest\TestUtils\XMock::of(\Zend\Mvc\Controller\Plugin\FlashMessenger::class);
        $flashMock->expects($this->never())
            ->method($method);

        $this->controller->getPluginManager()->setService('flashMessenger', $flashMock, false);

        return $flashMock;
    }

    public function getFlashMessengerMockForAddManyErrorMessage($errorMessage)
    {
        return $this->getFlashMessengerMockForManyCalls('addErrorMessage', $errorMessage);
    }

    public function getFlashMessengerMockForNoErrorMessage()
    {
        return $this->getFlashMessengerMockForNoCalls('addErrorMessage');
    }

    public function getFlashMessengerMockForAddSuccessMessage($successMessage)
    {
        return $this->getFlashMessengerMockForOneCall('addSuccessMessage', $successMessage);
    }

    public function getFlashMessengerMockForAddErrorMessage($errorMessage)
    {
        return $this->getFlashMessengerMockForOneCall('addErrorMessage', $errorMessage);
    }

    public function getFlashMessengerMockForAddInfoMessage($errorMessage)
    {
        return $this->getFlashMessengerMockForOneCall('addInfoMessage', $errorMessage);
    }

    /**
     * @param string $action
     * @param array  $params
     *
     * @return \Zend\Http\Response
     */
    public function getResponseForAction($action = '', $params = [])
    {
        $this->routeMatch->setParam('action', $action);

        foreach ($params as $key => $value) {
            $this->routeMatch->setParam($key, $value);
        }

        $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();

        return $response;
    }

    /**
     * Check response status code.
     *
     * @param int      $expectStatusCode
     * @param Response $response
     */
    protected function assertResponseStatus($expectStatusCode, $response = null)
    {
        if (!$response) {
            $response = $this->getController()->getResponse();
        }

        $this->assertEquals($expectStatusCode, $response->getStatusCode());
    }

    /**
     * @param Response $response
     * @param string   $expectedLocation
     */
    protected function assertRedirectLocation2($expectedLocation, $response = null)
    {
        if (!$response) {
            $response = $this->getController()->getResponse();
        }

        $this->assertResponseStatus(self::HTTP_REDIRECT_CODE, $response);
        $this->assertRedirectLocation($response, $expectedLocation);
    }

    /**
     * @param Response $response
     * @param string   $expectedLocation
     */
    protected function assertRedirectLocation($response, $expectedLocation)
    {
        $this->assertEquals(self::HTTP_REDIRECT_CODE, $response->getStatusCode());

        /** @var Location $locationHeader */
        $locationHeader = $response->getHeaders()->get('location');
        $this->assertEquals(
            $expectedLocation,
            $locationHeader->getUri()
        );
    }

    protected function setParams(array $params)
    {
        foreach ($params as $param => $value) {
            $this->routeMatch->setParam($param, $value);
        }
    }

    public function getResultForAction($action = '', $params = [])
    {
        $this->routeMatch->setParam('action', $action);

        foreach ($params as $key => $value) {
            $this->routeMatch->setParam($key, $value);
        }

        $result = $this->controller->dispatch($this->request);

        return $result;
    }

    /**
     * @param string $method
     * @param string $action
     * @param array  $routeParams
     * @param array  $queryParams
     * @param array  $postParams
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function getResultForAction2($method, $action = null, $routeParams = [], $queryParams = [], $postParams = [])
    {
        if ($method) {
            $this->request->setMethod($method);
        }

        if ($action) {
            $this->routeMatch->setParam('action', $action);
        }

        //  --  set route params    --
        if (!empty($routeParams) && is_array($routeParams)) {
            foreach ($routeParams as $key => $value) {
                $this->routeMatch->setParam($key, $value);
            }
        }

        //  --  set query params --
        if (!empty($queryParams) && is_array($queryParams)) {
            $this->request->setQuery(new Parameters($queryParams));
        }

        //  --  set post params --
        if (!empty($postParams) && is_array($postParams)) {
            $this->request->setPost(new Parameters($postParams));
        }

        $result = $this->controller->dispatch($this->request);

        return $result;
    }

    protected function setGetAndQueryParams(array $queryParams)
    {
        $this->request->setMethod('get');
        foreach ($queryParams as $param => $value) {
            $this->request->getQuery()->set($param, $value);
        }
    }

    protected function setPostAndPostParams(array $postParams)
    {
        $this->request->setMethod('post');
        foreach ($postParams as $param => $value) {
            $this->request->getPost()->set($param, $value);
        }
    }

    protected function canActionBeAccessedAndResponseWithHttpStatusCode($action, $httpStatusCode = self::HTTP_OK_CODE)
    {
        $this->routeMatch->setParam('action', $action);
        $this->controller->dispatch($this->request);

        /** @var Response $response */
        $response = $this->controller->getResponse();
        $this->assertEquals($httpStatusCode, $response->getStatusCode());

        return $response;
    }

    protected function jsonFixture($filename, $root = __DIR__)
    {
        return json_decode(
            file_get_contents(
                $root.'/fixtures/'.$filename.'.json'
            ),
            true
        );
    }
}
