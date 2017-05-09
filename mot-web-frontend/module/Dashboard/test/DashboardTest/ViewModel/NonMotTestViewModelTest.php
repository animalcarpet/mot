<?php

namespace DashboardTest\ViewModel;

use Dashboard\Security\DashboardGuard;
use Dashboard\ViewModel\NonMotTestViewModel;
use DvsaCommonTest\TestUtils\XMock;
use PHPUnit_Framework_TestCase;
use Zend\Mvc\Controller\Plugin\Url;

class NonMotTestViewModelTest extends PHPUnit_Framework_TestCase
{
    /** @var DashboardGuard|\PHPUnit_Framework_MockObject_MockObject $mockDashboardGuard */
    private $mockDashboardGuard;

    /** @var Url|\PHPUnit_Framework_MockObject_MockObject $mockUrl */
    private $mockUrl;

    public function setup()
    {
        $this->mockDashboardGuard = XMock::of(DashboardGuard::class);
        $this->mockUrl = XMock::of(Url::class);
    }

    public function testNonMotTestButtonHrefAndTextWithoutInProgressNonMotTest()
    {
        $nonMotTestViewModel = new NonMotTestViewModel($this->mockDashboardGuard, $this->mockUrl);
        $expectedHref = 'non-mot-test-vehicle-search/';
        $this->mockUrl
            ->method('fromRoute')
            ->willReturn($expectedHref);

        $this->assertEquals('action-start-non-mot', $nonMotTestViewModel->getLinkId());
        $this->assertEquals('Start a non-MOT test', $nonMotTestViewModel->getLinkViewModel()->getText());
        $this->assertEquals($expectedHref, $nonMotTestViewModel->getLinkViewModel()->getHref());
    }

    public function testNonMotTestButtonHrefAndTextWithInProgressNonMotTest()
    {
        $nonMotTestNumber = '1010101';
        $expectedHref = 'mot-test/'.$nonMotTestNumber;
        $this->mockUrl
            ->method('fromRoute')
            ->willReturn($expectedHref);

        $nonMotTestViewModel = new NonMotTestViewModel($this->mockDashboardGuard, $this->mockUrl);
        $nonMotTestViewModel->setInProgressNonMotTestNumber($nonMotTestNumber);

        $this->assertEquals('action-resume-non-mot', $nonMotTestViewModel->getLinkId());
        $this->assertEquals('Enter non-MOT test results', $nonMotTestViewModel->getLinkViewModel()->getText());
        $this->assertEquals($expectedHref, $nonMotTestViewModel->getLinkViewModel()->getHref());
    }
}
