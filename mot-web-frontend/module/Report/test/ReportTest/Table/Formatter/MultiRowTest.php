<?php

namespace ReportTest\Table\Formatter;

use DvsaCommonTest\TestUtils\TestCaseViewTrait;
use Report\Table\ColumnOptions;
use Report\Table\Formatter\MultiRow;

class MultiRowTest extends \PHPUnit_Framework_TestCase
{
    use TestCaseViewTrait;

    public function testFormat()
    {
        //  logical block: create view renderer
        $renderer = $this->getPhpRenderer(
            [
                'table/formatter/multi-row' => __DIR__.'/../../../../view/table/formatter/multi-row.phtml',
            ]
        );

        // logical block: prepare parameters
        $column = new ColumnOptions();
        $column->setField('testFieldA');
        $expectFieldValue1 = 'testFieldAValue1';
        $expectFieldValue2 = 'testFieldAValue2';

        $expectFieldValue = [$expectFieldValue1, $expectFieldValue2];
        $expectedOutput = trim($expectFieldValue1.'<br>'.$expectFieldValue2.'</span>');

        $rowData = [
            'testFieldA' => $expectFieldValue,
        ];

        //  logical block: call
        $output = MultiRow::format($rowData, $column, $renderer);

        //  logical block: check
        $this->assertStringEndsWith($expectedOutput, trim($output));
    }
}
