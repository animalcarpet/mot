<?php

namespace ApplicationTest\Controller;

use Application\Controller\FormsController;
use CoreTest\Controller\AbstractFrontendControllerTestCase;
use DvsaCommon\HttpRestJson\Exception\RestApplicationException;
use DvsaCommon\UrlBuilder\ReportUrlBuilder;
use DvsaCommonTest\Bootstrap;
use Dvsa\Mot\Frontend\Test\StubIdentityAdapter;
use Zend\Mvc\Router\RouteMatch;

/**
 * Class FormsControllerTest.
 */
class FormsControllerTest extends AbstractFrontendControllerTestCase
{
    protected function setUp()
    {
        $this->setServiceManager(Bootstrap::getServiceManager());
        $this->controller = new FormsController();
        $this->controller->setServiceLocator(Bootstrap::getServiceManager());
        parent::setUp();

        $this->setupAuthenticationServiceForIdentity(StubIdentityAdapter::asTester());
    }

    public function testIndexActionCanBeAccessed()
    {
        $this->getRestClientMockForServiceManager();
        $this->getResultForAction('index');

        $this->assertResponseStatus(self::HTTP_OK_CODE);
    }

    public function testCt20RequestsPdfWhenLocationKnown()
    {
        $this->runGoodContingencyTest('contingencyPassCertificate', 'CT20');
    }

    public function testCt30RequestsPdfWhenLocationKnown()
    {
        $this->runGoodContingencyTest('contingencyFailCertificate', 'CT30');
    }

    public function testCt32RequestsPdfWhenLocationKnown()
    {
        $this->runGoodContingencyTest('contingencyAdvisoryCertificate', 'CT32');
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage failed
     */
    public function testCt30RequestsPdfWhenLocationKnownPdfReqFailure()
    {
        $this->runFailContingencyTest('contingencyFailCertificate', 'CT30');
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage failed
     */
    public function testCt32RequestsPdfWhenLocationKnownPdfReqFailure()
    {
        $this->runFailContingencyTest('contingencyAdvisoryCertificate', 'CT32');
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage failed
     */
    public function testCt20RequestsPdfWhenLocationKnownPdfReqFailure()
    {
        $this->runFailContingencyTest('contingencyPassCertificate', 'CT20');
    }

    /**
     * @expectedException \DvsaCommon\HttpRestJson\Exception\RestApplicationException
     */
    public function testCt30RequestsPdfWhenLocationKnownPdfRestFailure()
    {
        $this->runFailContingencyTestRestException('contingencyFailCertificate', 'CT30');
    }

    /**
     * @expectedException \DvsaCommon\HttpRestJson\Exception\RestApplicationException
     */
    public function testCt32RequestsPdfWhenLocationKnownPdfRestFailure()
    {
        $this->runFailContingencyTestRestException('contingencyAdvisoryCertificate', 'CT32');
    }

    /**
     * @expectedException \DvsaCommon\HttpRestJson\Exception\RestApplicationException
     */
    public function testCt20RequestsPdfWhenLocationKnownPdfRestFailure()
    {
        $this->runFailContingencyTestRestException('contingencyPassCertificate', 'CT20');
    }

    public function testCt20RedirectsWhenNoVtsLocationLoaded()
    {
        $this->runNoLocationTest(
            'contingencyPassCertificate',
            'CT20',
            'forms/contingency-pass-certificate'
        );
    }

    public function testCt30RedirectsWhenNoVtsLocationLoaded()
    {
        $this->runNoLocationTest(
            'contingencyFailCertificate',
            'CT30',
            'forms/contingency-fail-certificate'
        );
    }

    public function testCt32RedirectsWhenNoVtsLocationLoaded()
    {
        $this->runNoLocationTest(
            'contingencyAdvisoryCertificate',
            'CT32',
            'forms/contingency-advisory-certificate'
        );
    }

    protected function runNoLocationTest($action, $name, $routeName)
    {
        $this->getRestClientMockForServiceManager();

        // wipe the Vts location to force a redirect
        $this->controller
            ->getServiceLocator()
            ->get('MotIdentityProvider')
            ->getIdentity()
            ->setCurrentVts(null);

        $this->routeMatch->setMatchedRouteName($routeName);
        $this->getResultForAction($action);

        $this->assertResponseStatus(self::HTTP_REDIRECT_CODE);
    }

    protected function runGoodContingencyTest($action, $name)
    {
        $restMock = $this->getRestClientMockForServiceManager();

        $this->mockMethod($restMock, 'getPdf', null, ['the pdf data'], $this->buildContingencyUrl($name));

        $result = $this->getResultForAction($action);

        $this->assertResponseStatus(self::HTTP_OK_CODE);

        $this->assertInstanceOf(\Zend\Http\Response::class, $result);
        $headers = $result->getHeaders()->toArray();
        $this->assertArrayHasKey('Content-Type', $headers);
        $this->assertEquals('application/pdf', $headers['Content-Type']);
    }

    protected function runFailContingencyTest($action, $name)
    {
        $restMock = $this->getRestClientMockForServiceManager();

        $this->mockMethod($restMock, 'getPdf', null, new \Exception('pdf failed'), $this->buildContingencyUrl($name));

        $result = $this->getResultForAction($action);

        $this->assertResponseStatus(self::HTTP_OK_CODE);

        $this->assertInstanceOf(\Zend\Http\Response::class, $result);
        $headers = $result->getHeaders()->toArray();
        $this->assertArrayHasKey('Content-Type', $headers);
        $this->assertEquals('application/pdf', $headers['Content-Type']);
    }

    protected function runFailContingencyTestRestException($action, $name)
    {
        $restMock = $this->getRestClientMockForServiceManager();

        $restMock->expects($this->once())
            ->method('getPdf')
            ->with($this->buildContingencyUrl($name))
            ->willThrowException(new RestApplicationException(null, null, null, null));

        $result = $this->getResultForAction($action);

        $this->assertResponseStatus(self::HTTP_OK_CODE);

        $this->assertInstanceOf(\Zend\Http\Response::class, $result);
        $headers = $result->getHeaders()->toArray();
        $this->assertArrayHasKey('Content-Type', $headers);
        $this->assertEquals('application/pdf', $headers['Content-Type']);
    }

    protected function buildContingencyUrl($name)
    {
        $certificateUrl = ReportUrlBuilder::printContingencyCertificate($name);

        $testStation = '';
        $inspAuthority = '';

        $motIdProv = $this->controller->getServiceLocator()->get('MotIdentityProvider');

        /** @var \Dvsa\Mot\Frontend\AuthenticationModule\Model\VehicleTestingStation $vts */
        $vts = $motIdProv->getIdentity()->getCurrentVts();

        if ($vts) {
            $testStation = $vts->getSiteNumber();

            $inspAuthority = $vts->getName().PHP_EOL.
                preg_replace("/,\s*/", PHP_EOL, $vts->getAddress());
        }

        return $certificateUrl->queryParams(
            [
                'testStation' => $testStation,
                'inspAuthority' => $inspAuthority,
            ]
        );
    }
}
