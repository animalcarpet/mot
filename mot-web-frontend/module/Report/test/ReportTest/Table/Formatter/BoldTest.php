<?php

namespace ReportTest\Table\Formatter;

use Report\Table\ColumnOptions;
use Report\Table\Formatter\Bold;
use Zend\Stdlib\Parameters;
use Zend\View\Renderer\PhpRenderer;

class BoldTest extends \PHPUnit_Framework_TestCase
{
    public function testFormat()
    {
        // logical block: prepare parameters
        $column = new ColumnOptions();
        $column->setField('testFieldA');

        $expectFieldValue = 'testFieldAValue';

        $rowData = [
            'testFieldA' => $expectFieldValue,
        ];

        //  logical block: call
        $output = Bold::format($rowData, $column, new PhpRenderer());

        //  logical block: check
        $this->assertEquals('<b>'.$expectFieldValue.'</b>', trim($output));
    }
}
