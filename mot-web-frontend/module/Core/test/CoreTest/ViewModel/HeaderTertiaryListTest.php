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
        $header->addRow(self::FIRST_ROW);
        $header->addRow(self::SECOND_ROW);
        $header->addRow(self::THIRD_ROW);

        $rows = $header->getRows();
        $this->assertCount(3, $rows);
        $this->assertEquals($rows[0], self::FIRST_ROW);
        $this->assertEquals($rows[1], self::SECOND_ROW);
        $this->assertEquals($rows[2], self::THIRD_ROW);
    }
}