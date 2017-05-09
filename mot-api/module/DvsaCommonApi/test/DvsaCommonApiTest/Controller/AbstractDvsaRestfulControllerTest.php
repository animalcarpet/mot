<?php

namespace DvsaCommonApiTest\Controller;

use DvsaCommonApi\Controller\AbstractDvsaRestfulController;
use DvsaFeature\FeatureToggles;

/**
 * Class AbstractDvsaRestfulControllerTest.
 */
class AbstractDvsaRestfulControllerTest extends AbstractRestfulControllerTestCase
{
    const ENABLED_FEATURE = 'enabledFeature';
    const DISABLED_FEATURE = 'disabledFeature';

    protected function setUp()
    {
        $this->controller = new AbstractDvsaRestfulController();
        parent::setUp();
        $this->mockLogger();

        $featureToggles = $this
            ->getMockBuilder(FeatureToggles::class)
            ->disableOriginalConstructor()
            ->setMethods(['isEnabled'])
            ->getMock();
        $featureToggles
            ->expects($this->any())
            ->method('isEnabled')
            ->will($this->returnValueMap([
                [self::ENABLED_FEATURE, true],
                [self::DISABLED_FEATURE, false],
            ]));

        $this->serviceManager
            ->setService('Feature\FeatureToggles', $featureToggles);
    }

    public function testIsFeatureEnabled()
    {
        $this->assertTrue($this->controller->isFeatureEnabled(self::ENABLED_FEATURE));
        $this->assertFalse($this->controller->isFeatureEnabled(self::DISABLED_FEATURE));
    }

    /**
     * @expectedException \DvsaCommonApi\Service\Exception\NotFoundException
     */
    public function testAssertDisabledFeatureThrowsException()
    {
        $this->controller->assertFeatureEnabled(self::DISABLED_FEATURE);
    }

    public function testAssertEnabledFeatureDoesNotThrowException()
    {
        $this->controller->assertFeatureEnabled(self::ENABLED_FEATURE);
    }

    public function testCreateReturns405()
    {
        $this->request->setMethod('post');
        $result = $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();
        $this->assertResponse405Error($response, $result);
    }

    public function testDeleteReturns405()
    {
        $this->request->setMethod('delete');
        $this->routeMatch->setParam('id', 1);
        $result = $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();
        $this->assertResponse405Error($response, $result);
    }

    public function testDeleteListReturns405()
    {
        $this->request->setMethod('delete');
        $result = $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();
        $this->assertResponse405Error($response, $result);
    }

    public function testGetReturns405()
    {
        $this->routeMatch->setParam('id', 1);
        $result = $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();
        $this->assertResponse405Error($response, $result);
    }

    public function testGetListReturns405()
    {
        $result = $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();
        $this->assertResponse405Error($response, $result);
    }

    public function testHeadReturns405()
    {
        $this->request->setMethod('head');
        $this->routeMatch->setParam('id', 1);
        $result = $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();

        $this->assertEquals(405, $response->getStatusCode());
    }

    public function testOptionsReturns405()
    {
        $this->request->setMethod('options');
        $result = $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();

        $this->assertEquals(405, $response->getStatusCode());
    }

    public function testPatchReturns405()
    {
        $this->request->setMethod('patch');
        $this->routeMatch->setParam('id', 1);
        $result = $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();
        $this->assertResponse405Error($response, $result);
    }

    public function testReplaceListReturns405()
    {
        $this->request->setMethod('put');
        $result = $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();
        $this->assertResponse405Error($response, $result);
    }

    public function testUpdateReturns405()
    {
        $this->request->setMethod('put');
        $this->routeMatch->setParam('id', 1);
        $result = $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();
        $this->assertResponse405Error($response, $result);
    }
}
