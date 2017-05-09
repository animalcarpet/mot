<?php

namespace OrganisationTest\ViewModel\Search;

use Organisation\ViewModel\Search\AeSearchViewModel;

/**
 * Class AeSearchViewModelTest.
 */
class AeSearchViewModelTest extends \PHPUnit_Framework_TestCase
{
    public function testAeSearchViewModel()
    {
        $view = new AeSearchViewModel();

        $this->assertTrue($view->isAeFound());
        $this->assertEquals(null, $view->getErrorMessage());
        $this->assertInstanceOf(AeSearchViewModel::class, $view->setIsAeFound(false));
        $this->assertFalse($view->isAeFound());
        $this->assertEquals('AE Number was not found', $view->getErrorMessage());
        $this->assertEquals('/authorised-examiner/1', $view->getDetailPage(1));
        $this->assertInstanceOf(AeSearchViewModel::class, $view->setSearch('A12345'));
        $this->assertEquals('A12345', $view->getSearch());
    }
}
