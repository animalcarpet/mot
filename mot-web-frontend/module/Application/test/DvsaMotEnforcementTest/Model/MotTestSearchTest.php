<?php

namespace DvsaMotEnforcementTest\Model;

use DvsaMotEnforcement\Model\MotTestSearch;
use PHPUnit_Framework_TestCase;

class MotTestSearchTest extends PHPUnit_Framework_TestCase
{
    public function testExchangeArray()
    {
        $form = new MotTestSearch();
        $form->exchangeArray(['searchValue' => 42]);
        $this->assertEquals(42, $form->searchValue);

        $form->exchangeArray([]);
        $this->assertEquals(null, $form->searchValue);
    }
}
