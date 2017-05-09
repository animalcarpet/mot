<?php

namespace CsrfTest;

use Csrf\CsrfConstants;
use Csrf\CsrfSupport;
use Csrf\CsrfValidatingListener;
use Csrf\InvalidCsrfException;
use DvsaCommonTest\TestUtils\NumbProbe;
use DvsaCommonTest\TestUtils\XMock;
use Zend\EventManager\EventManager;
use Zend\Http\Request;
use Zend\Http\Response;
use Zend\Mvc\Application;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Router\RouteMatch;
use Zend\ServiceManager\ServiceManager;
use Zend\Stdlib\Parameters;

/**
 * Class CsrfValidatingListenerTest.
 */
class CsrfValidatingListenerTest extends \PHPUnit_Framework_TestCase
{
    private function buildMvcEvent($isPost = true, $tokenGenerated = 'VALID_TOKEN', $tokenInPayload = null)
    {
        $csrfSupport = XMock::of(CsrfSupport::class);
        $csrfSupport->expects($this->any())->method('getCsrfToken')->will(
            $this->returnValue($tokenGenerated)
        );

        $mvcEvent = new MvcEvent();
        $sm = new ServiceManager();
        $sm->setAllowOverride(true);
        $sm->setService('CsrfSupport', $csrfSupport);
        $sm->setService('EventManager', new EventManager());
        $sm->setService('config', ['csrf' => ['enabled' => true]]);
        $request = new Request();
        $mvcEvent->setRequest($request);
        if ($isPost) {
            $request->setMethod(Request::METHOD_POST);
        } else {
            $request->setMethod(Request::METHOD_GET);
        }
        if ($tokenInPayload) {
            $request->setPost(new Parameters([CsrfConstants::REQ_TOKEN => $tokenInPayload]));
        }
        $mvcEvent->setRouteMatch(new RouteMatch([]));

        $sm->setService('Request', $request);
        $sm->setService('Response', new Response());
        $mvcEvent->setApplication(new Application(new NumbProbe(), $sm));

        return $mvcEvent;
    }

    public function testValidate_givenNoCsrfInPayload_shouldThrowException()
    {
        $this->setExpectedException(InvalidCsrfException::class);

        $listener = new CsrfValidatingListener();
        $mvcEvent = $this->buildMvcEvent(true);
        $listener->validate($mvcEvent);
    }

    public function testValidate_givenLoginRoutePosted_shouldPass()
    {
        $listener = new CsrfValidatingListener();
        $mvcEvent = $this->buildMvcEvent(true);
        $routeMatch = new RouteMatch([]);
        $routeMatch->setMatchedRouteName('login');
        $mvcEvent->setRouteMatch($routeMatch);
        $r = new Request();
        $r->setMethod('POST');
        $mvcEvent->setRequest($r);
        $listener->validate($mvcEvent);
    }

    public function testValidate_givenWrongToken_shouldThrowException()
    {
        $this->setExpectedException(InvalidCsrfException::class);
        $token = 'XXXXXXXX';
        $listener = new CsrfValidatingListener();
        $mvcEvent = $this->buildMvcEvent(true, $token);
        $listener->validate($mvcEvent);
    }

    public function testValidate_givenValidToken_shouldPass()
    {
        $generatedToken = $tokenInPayload = 'VALID_TOKEN';
        $listener = new CsrfValidatingListener();
        $mvcEvent = $this->buildMvcEvent(true, $generatedToken, $tokenInPayload);
        $listener->validate($mvcEvent);
    }
}
