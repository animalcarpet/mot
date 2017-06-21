<?php

namespace DvsaCommonApiTest\Controller;

use DvsaCommonTest\Bootstrap;
use DvsaEntities\Entity\Person;
use DvsaAuthentication\Identity;
use Zend\Http\Request;
use Zend\Http\Response;
use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Router\Http\TreeRouteStack as HttpRouter;
use Zend\Mvc\Router\RouteMatch;
use Zend\ServiceManager\ServiceManager;

/**
 * Common logic for all api controller unit tests.
 *
 * Should be added to a class extending   \PHPUnit_Framework_TestCase
 * This class should also implement       ApiControllerUnitTestInterface
 */
trait ApiControllerUnitTestTrait
{
    private $methodsForActions
        = [
            'create' => 'post',
            'delete' => 'delete',
            'deleteList' => 'delete',
            'get' => 'get',
            'getList' => 'get',
            'head' => 'head',
            'options' => 'options',
            'replaceList' => 'put',
            'update' => 'put',
        ];

    /**
     * Used when authentication and authorization is mocked.
     */
    protected $MOCK_USER_ID = 5;

    /**
     * @var AbstractRestfulController
     */
    protected $controller;
    /**
     * @var Request
     */
    protected $request;
    /**
     * @var Response
     */
    protected $response;
    /**
     * @var RouteMatch
     */
    protected $routeMatch;
    /**
     * @var MvcEvent
     */
    protected $event;
    /**
     * @var ServiceManager
     */
    protected $serviceManager;

    protected function setUpTestCase()
    {
        $this->serviceManager = Bootstrap::getServiceManager();
        $this->serviceManager->setAllowOverride(true);
        $this->request = new Request();
        $this->routeMatch = new RouteMatch(['controller' => 'index']);
        $this->event = new MvcEvent();
        $config = $this->serviceManager->get('Config');
        $routerConfig = isset($config['router']) ? $config['router'] : [];
        $router = HttpRouter::factory($routerConfig);
        $this->event->setRouter($router);
        $this->event->setRouteMatch($this->routeMatch);
        $this->controller->setEvent($this->event);
        $this->controller->setServiceLocator($this->serviceManager);
    }

    /**
     * @param array  $requiredUserRoles
     * @param array  $requiredVtsRoles
     * @param Person $person
     *
     * @return Identity
     */
    protected function mockValidAuthorization(
        $requiredUserRoles = [],
        $requiredVtsRoles = [],
        Person $person = null
    ) {
        if (!empty($person)) {
            $userId = $person->getId();
            $userName = $person->getUsername();
        } else {
            $userId = $this->MOCK_USER_ID;
            $userName = 'validUser';
            $person = new Person();
            $person->setId($userId)
                ->setUsername($userName);
        }

        $mockIdentityProvider = $this->getMockServiceManagerClass(
            'DvsaAuthenticationService', \Zend\Authentication\AuthenticationService::class
        );

        $identity = new Identity($person);
        $mockIdentityProvider->expects($this->any())
            ->method('getIdentity')
            ->will($this->returnValue($identity));

        return $identity;
    }
    protected function getMockServiceManagerClass($serviceAlias, $mockClass, $methods = null)
    {
        $mock = \DvsaCommonTest\TestUtils\XMock::of($mockClass, $methods);
        $this->serviceManager->setService($serviceAlias, $mock);

        return $mock;
    }

    /**
     * Iterates all REST methods passed argument and asserts if they are allowed or not in tested controller.
     *
     * @param array $methods
     */
    public function assertMethodsOk($methods = [])
    {
        $notAllowedMethod = $this->methodsForActions;

        foreach ($methods as $method) {
            $this->throwRuntimeExceptionIfNotSupportedMethod($method);
            unset($notAllowedMethod[$method]);
            $this->setupAndAssertMethod($method, ApiControllerUnitTestInterface::OK);
        }

        foreach ($notAllowedMethod as $method => $httpMethod) {
            $this->throwRuntimeExceptionIfNotSupportedMethod($method);
            $this->setupAndAssertMethod($method, ApiControllerUnitTestInterface::NOT_ALLOWED);
        }
    }

    private function setupAndAssertMethod($method, $code)
    {
        $method = 'assert'.ucfirst($method);
        $this->$method($code);
        $this->setUpTestCase();
    }

    private function throwRuntimeExceptionIfNotSupportedMethod($method)
    {
        if (false === isset($this->methodsForActions[$method])) {
            throw new \RuntimeException('Method '.$method.' not supported');
        }
    }

    protected function assertAction($method, $code)
    {
        $this->throwRuntimeExceptionIfNotSupportedMethod($method);

        $this->request->setMethod($this->methodsForActions[$method]);
        /*
         * ApiControllerUnitTestInterface::mockServices
         */
        $this->mockServices();

        $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();
        $this->assertEquals($code, $response->getStatusCode(), 'Method: '.$method);
    }

    public function assertCreate($code)
    {
        $this->assertAction('create', $code);
    }

    public function assertDelete($code)
    {
        $this->routeMatch->setParam('id', 1);
        $this->assertAction('delete', $code);
    }

    public function assertDeleteList($code)
    {
        $this->assertAction('deleteList', $code);
    }

    public function assertGet($code)
    {
        $this->routeMatch->setParam('id', '1');
        $this->assertAction('get', $code);
    }

    public function assertGetList($code)
    {
        $this->assertAction('getList', $code);
    }

    public function assertReplaceList($code)
    {
        $this->assertAction('replaceList', $code);
    }

    public function assertUpdate($code)
    {
        $this->routeMatch->setParam('id', '1');
        $this->assertAction('update', $code);
    }

    public function assertHead($code)
    {
        $this->assertAction('head', $code);
    }

    public function assertOptions($code)
    {
        $this->assertAction('options', $code);
    }

    /**
     * @param $classPath
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function createMock($classPath)
    {
        $serviceMock = \DvsaCommonTest\TestUtils\XMock::of($classPath);
        $this->serviceManager->setService($classPath, $serviceMock);

        return $serviceMock;
    }
}
