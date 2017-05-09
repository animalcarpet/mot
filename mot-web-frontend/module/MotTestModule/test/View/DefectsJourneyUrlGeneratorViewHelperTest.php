<?php

namespace Dvsa\Mot\Frontend\MotTestModuleTest\View;

use Dvsa\Mot\Frontend\MotTestModule\View\DefectsJourneyContextProvider;
use Dvsa\Mot\Frontend\MotTestModule\View\DefectsJourneyUrlGenerator;
use Dvsa\Mot\Frontend\MotTestModule\View\DefectsJourneyUrlGeneratorViewHelper;
use ReflectionObject;

class DefectsJourneyUrlGeneratorViewHelperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DefectsJourneyContextProvider
     */
    private $contextProvider;

    /**
     * @var DefectsJourneyUrlGenerator
     */
    private $urlGenerator;

    /**
     * @var DefectsJourneyUrlGeneratorViewHelper
     */
    private $helper;

    public function setUp()
    {
        $this
            ->urlGenerator = $this->getMockBuilder(DefectsJourneyUrlGenerator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this
            ->contextProvider = $this->getMockBuilder(DefectsJourneyContextProvider::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->helper = new DefectsJourneyUrlGeneratorViewHelper(
            $this->urlGenerator, $this->contextProvider);
    }

    public function testInvokeIsImplemented()
    {
        $r = new ReflectionObject($this->helper);
        $this->assertTrue($r->hasMethod('__invoke'));
    }

    public function testGetContext()
    {
        $context = 'testContext';
        $this->setUpContextProvider($context);

        $this->assertEquals($context, $this->helper->getContext());
    }

    public function testGetContextViaInvoke()
    {
        $context = 'testContext';
        $this->setUpContextProvider($context);

        $helper = $this->helper;
        $this->assertEquals($context, $helper()->getContext());
    }

    public function testToAddDefect()
    {
        $url = 'testUrl';
        $defectId = 10;
        $defectType = 'prs';
        $this->setUpUrlGenerator('toAddDefect', $url);

        $this->assertEquals($url, $this->helper->toAddDefect($defectId, $defectType));
    }

    public function testToAddManualAdvisory()
    {
        $url = 'testUrl';
        $this->setUpUrlGenerator('toAddManualAdvisory', $url);

        $this->assertEquals($url, $this->helper->toAddManualAdvisory());
    }

    public function testToEditDefect()
    {
        $url = 'testUrl';
        $defectId = 10;
        $this->setUpUrlGenerator('toEditDefect', $url);

        $this->assertEquals($url, $this->helper->toEditDefect($defectId));
    }

    public function testToRemoveDefect()
    {
        $url = 'testUrl';
        $defectId = 10;
        $this->setUpUrlGenerator('toRemoveDefect', $url);

        $this->assertEquals($url, $this->helper->toRemoveDefect($defectId));
    }

    public function testGoBack()
    {
        $url = 'testUrl';
        $this->setUpUrlGenerator('goBack', $url);

        $this->assertEquals($url, $this->helper->goBack());
    }

    private function setUpContextProvider($context)
    {
        $this
            ->contextProvider
            ->expects($this->once())
            ->method('getContext')
            ->willReturn($context);
    }

    private function setUpUrlGenerator($method, $url)
    {
        $this
            ->urlGenerator
            ->expects($this->once())
            ->method($method)
            ->willReturn($url);
    }
}
