<?php

namespace Dashboard\ViewModel;

class SlotsViewModelTest extends \PHPUnit_Framework_TestCase
{
    public function testShouldReturnTrueForValueGreaterThan1()
    {
        $isVisible = new SlotsViewModel(true, 0, 2);
        $this->assertTrue($isVisible->isOverallSiteCountVisible());
    }

    public function testShouldReturnFalseForValueLessOrEqual1()
    {
        $isVisible = new SlotsViewModel(true, 0, 1);
        $this->assertFalse($isVisible->isOverallSiteCountVisible());
    }
}

