<?php
/**
 * This file is part of the DVSA MOT API project.
 *
 * @link https://github.com/dvsa/mot
 */

namespace DvsaCommonApiTest\Controller;

use DvsaAuthentication\Identity;
use DvsaCommonTest\Bootstrap;
use DvsaEntities\Entity\Person;
use HttpResponse;
use PHPUnit_Framework_MockObject_MockObject;
use Zend\Http\Header\Authorization as ZendAuthorizationHeader;
use Zend\Http\Header\ContentType;
use Zend\Http\Request;
use Zend\Http\Response;
use Zend\Mvc\Controller\AbstractController;
use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Router\Http\TreeRouteStack as HttpRouter;
use Zend\Mvc\Router\RouteMatch;
use Zend\ServiceManager\ServiceManager;
use Zend\Stdlib\Parameters;
use Zend\View\Model\ModelInterface;
use Zend\View\Model\ViewModel;

/**
 * Class AbstractRestfulControllerTestCase.
 */
abstract class AbstractRestfulControllerTestCase extends BaseRestfulControllerTestCase
{
    /**
     * Used when authentication and authorization is mocked.
     */
    const MOCK_USER_ID = 5;

    /** @var AbstractController */
    protected $controller;
    /** @var Request */
    protected $request;
    /** @var Response */
    protected $response;
    /** @var RouteMatch */
    protected $routeMatch;
    /** @var MvcEvent */
    protected $event;
    /** @var ServiceManager */
    protected $serviceManager;

    protected function setUp()
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
        if (null !== $this->controller) {
            $this->setUpController($this->controller);
        }
    }

    protected function setUpController(AbstractController $controller)
    {
        $controller->setEvent($this->event);
        $controller->setServiceLocator($this->serviceManager);
    }

    protected function mockLogger()
    {
        $loggerMock = $this->getMockWithDisabledConstructor(\Zend\Log\Logger::class);

        $this->serviceManager->setService('Application/Logger', $loggerMock);
    }

    protected function mockValidAuthorization(
        $requiredUserRoles = [],
        $requiredVtsRoles = [],
        Person $person = null
    ) {
        if (!empty($person)) {
            $userId = $person->getId();
            $userName = $person->getUsername();
        } else {
            $userId = self::MOCK_USER_ID;
            $userName = 'validUser';
            $person = new Person();
            $person->setId($userId)->setUsername($userName);
        }

        $mockIdentityProvider = $this->getMockServiceManagerClass(
            'DvsaAuthenticationService', \Zend\Authentication\AuthenticationService::class
        );

        $mockIdentityProvider->expects($this->any())
            ->method('getIdentity')
            ->will($this->returnValue(new Identity($person)));
    }

    protected function setRequestHeaderAuthorizationWithToken($accessToken)
    {
        $header = new ZendAuthorizationHeader();

        $this->setValue($header, $accessToken);

        $this->request->getHeaders()->addHeader($header);
    }

    protected function getMockServiceManagerClass($serviceAlias, $mockClass, $methods = null)
    {
        $mock = $this->getMockWithDisabledConstructor($mockClass, $methods);
        $this->serviceManager->setService($serviceAlias, $mock);

        return $mock;
    }

    /**
     * This 'pointless' function is to suppress a PHPStorm warning about
     * ZendAuthorizationHeader->value having protected access.
     */
    private function setValue($header, $accessToken)
    {
        $header->value = "Bearer $accessToken";
    }

    /**
     * TODO inline this superceded method.
     */
    protected function getMockWithDisabledConstructor($mockClass, $methods = null)
    {
        return \DvsaCommonTest\TestUtils\XMock::of($mockClass, $methods);
    }

    /**
     * @param AbstractRestfulController $controller
     *
     * @return AbstractRestfulControllerTestCase
     */
    protected function setController(AbstractRestfulController $controller = null)
    {
        $this->controller = $controller;

        return $this;
    }

    /**
     * @return AbstractController
     */
    protected function getController()
    {
        return $this->controller;
    }

    protected function setupMockForCalls(
        PHPUnit_Framework_MockObject_MockObject $mock,
        $method,
        $returnValue,
        $with = null,
        $once = false
    ) {
        $times = $once ? $this->once() : $this->any();

        if ($returnValue instanceof \Exception) {
            $thisReturnValue = $this->throwException($returnValue);
        } else {
            $thisReturnValue = $this->returnValue($returnValue);
        }

        $method = $mock->expects($times)->method($method);
        if (!is_null($with)) {
            $method->with($with);
        }
        $method->will($thisReturnValue);

        return $mock;
    }

    /**
     * @param string $action
     * @param array  $postParams
     * @param array  $routeParams
     *
     * @return HttpResponse|\Zend\Stdlib\ResponseInterface
     */
    public function getPostResultToAction($action, array $postParams, array $routeParams = [])
    {
        return $this->getResultForAction(Request::METHOD_POST, $action, $routeParams, [], $postParams);
    }

    /**
     * @param string      $method
     * @param string|null $action
     * @param array       $routeParams
     * @param array       $queryParams
     * @param array       $postParams
     * @param array       $putParams
     *
     * @return HttpResponse|\Zend\Stdlib\ResponseInterface
     */
    public function getResultForAction(
        $method,
        $action = null,
        $routeParams = [],
        $queryParams = [],
        $postParams = [],
        $putParams = []
    ) {
        if ($method) {
            $this->request->setMethod($method);
        }

        if ($action) {
            $this->routeMatch->setParam('action', $action);
        }

        //  --  set route params    --
        if (!empty($routeParams) && is_array($routeParams)) {
            $content = null;
            if (in_array(strtoupper($method), [Request::METHOD_POST, Request::METHOD_PUT])) {
                if (!empty($postParams)) {
                    $content = $postParams;
                } elseif (!empty($putParams)) {
                    $content = $putParams;
                } elseif (count($routeParams) > 1) {
                    $content = end($routeParams);
                    array_pop($routeParams);
                }
            }

            foreach ($routeParams as $key => $value) {
                $this->routeMatch->setParam($key, $value);
            }

            if (!empty($content)) {
                $this->request->getHeaders()->addHeader(ContentType::fromString('content-type: application/json'));
                $this->request->setContent(json_encode($content));
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

    protected function assertResponseStatus($expectedStatus, $response = null)
    {
        if ($response === null && !($response instanceof Response)) {
            $response = $this->getController()->getResponse();
        }

        $this->assertEquals($expectedStatus, $response->getStatusCode());
    }

    protected function assertResponseStatusAndResult(
        $expectedStatus,
        $expectedResult,
        ModelInterface $result,
        $response = null
    ) {
        if ($response === null && !($response instanceof Response)) {
            $response = $this->getController()->getResponse();
        }

        $this->assertResponseStatus($expectedStatus, $response);
        $this->assertInstanceOf(\Zend\View\Model\JsonModel::class, $result);
        $this->assertEquals($expectedResult, $result->getVariables());
    }

    protected function assertResultHasErrors(
        ViewModel $result,
        $errorArray
    ) {
        $this->assertInstanceOf("Zend\View\Model\JsonModel", $result);
        $vars = $result->getVariables();
        $this->assertTrue(array_key_exists('errors', $vars), 'Should have errors');

        foreach ($errorArray as $error) {
            $this->assertEquals($error['message'], $vars['errors'][0]['message']);
            $this->assertEquals($error['code'], $vars['errors'][0]['code']);
            $this->assertTrue(array_key_exists('displayMessage', $vars['errors'][0]), 'Should have display message');
        }
    }
}
