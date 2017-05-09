<?php

namespace CoreTest\ViewModel;

use Core\ViewModel\Header\HeaderTertiaryList;

class HeaderTertiaryListTest extends \PHPUnit_Framework_TestCase
{
    const FIRST_ROW = 'first-row';
    const SECOND_ROW = 'second-row';
    const THIRD_ROW = 'third-row';

    public function testHeaderTertiary()
    {
        $header = new HeaderTertiaryList();
        $header->addElement(self::FIRST_ROW)->bold();
        $header->addElement(self::SECOND_ROW);
        $header->addElement(self::THIRD_ROW);

        $elements = $header->getElements();
        $this->assertCount(3, $elements);
        $this->assertEquals($elements[0]->getText(), self::FIRST_ROW);
        $this->assertTrue($elements[0]->isBold());
        $this->assertEquals($elements[1]->getText(), self::SECOND_ROW);
        $this->assertFalse($elements[1]->isBold());
        $this->assertEquals($elements[2]->getText(), self::THIRD_ROW);
        $this->assertFalse($elements[2]->isBold());
    }
}
