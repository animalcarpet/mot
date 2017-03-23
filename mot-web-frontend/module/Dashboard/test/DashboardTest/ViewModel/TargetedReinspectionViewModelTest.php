<?php

namespace Dashboard\ViewModel;

use DvsaCommonTest\TestUtils\XMock;
use Zend\Mvc\Controller\Plugin\Url;

class TargetedReinspectionViewModelTest extends \PHPUnit_Framework_TestCase
{
    private $mockUrl;

    public function setup()
    {
        $this->mockUrl = XMock::of(Url::class);
    }

    public function testGetUrl()
    {
        $testNumberInProgress = '1010101';
        $expectedHref = 'mot-test/'.$testNumberInProgress;
        $this->mockUrl
            ->method('fromRoute')
            ->willReturn($expectedHref);

        $targetedReinspectionViewModel = new TargetedReinspectionViewModel(
            $this->mockUrl,
            false,
            false,
            $testNumberInProgress
        );

        $this->assertEquals('mot-test/'.$testNumberInProgress, $targetedReinspectionViewModel->getUrl());
    }
}
