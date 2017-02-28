<?php
/**
 * Created by PhpStorm.
 * User: eduardol
 * Date: 13/02/2017
 * Time: 14:13
 */

namespace Dashboard\ViewModel;

class SlotsViewModelTest extends \PHPUnit_Framework_TestCase
{
    public function testShouldReturnTrueForValueGreaterThan1()
    {
        $isVisible = new SlotsViewModel(0, 2);
        $this->assertTrue($isVisible->isOverallSiteCountVisible());
    }

    public function testShouldReturnFalseForValueLessOrEqual1()
    {
        $isVisible = new SlotsViewModel(0, 1);
        $this->assertFalse($isVisible->isOverallSiteCountVisible());
    }
}

