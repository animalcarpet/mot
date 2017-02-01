<?php

namespace DashboardTest\ViewModel;

use Dashboard\ViewModel\HeroActionViewModel;
use PHPUnit_Framework_TestCase;

class HeroActionViewModelTest extends PHPUnit_Framework_TestCase
{
    public function testEmptyHeroActionNotVisible()
    {
        $heroAction = new HeroActionViewModel();

        $this->assertFalse($heroAction->isVisible());
    }
}
