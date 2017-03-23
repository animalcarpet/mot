<?php

namespace DashboardTest\ViewModel;

use Dashboard\ViewModel\TrainingTestViewModel;
use DvsaCommonTest\TestUtils\XMock;
use PHPUnit_Framework_TestCase;
use Zend\Mvc\Controller\Plugin\Url;

class TrainingTestViewModelTest extends PHPUnit_Framework_TestCase
{
    /** @var Url|\PHPUnit_Framework_MockObject_MockObject $mockUrl */
    private $mockUrl;

    public function setup() {
        $this->mockUrl = XMock::of(Url::class);
    }

    public function testTrainingTestButtonHrefAndTextWithoutInProgressTrainingTest()
    {
        $trainingTestViewModel = new TrainingTestViewModel($this->mockUrl);
        $expectedHref = 'training-test-vehicle-search';
        $this->mockUrl
            ->method('fromRoute')
            ->willReturn($expectedHref);

        $this->assertEquals('action-start-mot-demonstration', $trainingTestViewModel->getLinkId());
        $this->assertEquals('Start training test', $trainingTestViewModel->getLinkViewModel()->getText());
        $this->assertEquals($expectedHref, $trainingTestViewModel->getLinkViewModel()->getHref());
    }

    public function testTrainingTestButtonHrefAndTextWithInProgressTrainingTest()
    {
        $motTestNumber = '1010101';
        $expectedHref = 'mot-test/'.$motTestNumber;
        $this->mockUrl
            ->method('fromRoute')
            ->willReturn($expectedHref);

        $trainingTestViewModel = new TrainingTestViewModel($this->mockUrl);
        $trainingTestViewModel->setInProgressTestNumber($motTestNumber);

        $this->assertEquals('action-resume-mot-demonstration', $trainingTestViewModel->getLinkId());
        $this->assertEquals('Resume training test', $trainingTestViewModel->getLinkViewModel()->getText());
        $this->assertEquals($expectedHref, $trainingTestViewModel->getLinkViewModel()->getHref());
    }
}
