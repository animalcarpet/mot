<?php

namespace ReportTest\Table;

use Report\Table\TableOptions;

class TableOptionsTest extends \PHPUnit_Framework_TestCase
{
    public function testGetSet()
    {
        $tableOptions = new TableOptions();

        //  --  test get|set items per page --
        $value = 9999;
        $tableOptions->setItemsPerPage($value);
        $this->assertEquals($value, $tableOptions->getItemsPerPage());

        //  --  test get|set items per page options --
        $value = ['a', 'b', 'c'];
        $tableOptions->setItemsPerPageOptions($value);
        $this->assertEquals($value, $tableOptions->getItemsPerPageOptions());

        //  --  test get|set table view script  --
        $value = 'path to script';
        $tableOptions->setTableViewScript($value);
        $this->assertEquals($value, $tableOptions->getTableViewScript());

        //  --  test get|set footer view script  --
        $value = 'path to script';
        $tableOptions->setFooterViewScript($value);
        $this->assertEquals($value, $tableOptions->getFooterViewScript());

        //  --  test get|set table class --
        $value = 'table class';
        $tableOptions->setTableClass($value);
        $this->assertEquals($value, $tableOptions->getTableClass());

        //  --  test get|set has meta title  --
        $tableOptions->setHasMetaTitle(true);
        $this->assertTrue($tableOptions->hasMetaTitle());
    }
}
