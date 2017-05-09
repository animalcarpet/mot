<?php

namespace ReportTest\Table\Formatter;

use DvsaCommonTest\TestUtils\TestCaseViewTrait;
use Report\Table\ColumnOptions;
use Report\Table\Formatter\SubRow;
use Zend\Stdlib\Parameters;

class SubRowTest extends \PHPUnit_Framework_TestCase
{
    use TestCaseViewTrait;

    public function testFormat()
    {
        //  logical block: create view renderer
        $renderer = $this->getPhpRenderer(
            [
                'table/formatter/sub-row' => __DIR__.'/../../../../view/table/formatter/sub-row.phtml',
            ]
        );

        // logical block: prepare parameters
        $column = new ColumnOptions();
        $column->setField('testFieldA');

        $expectFieldValue = 'testFieldAValue';

        $rowData = [
            'testFieldA' => $expectFieldValue,
        ];

        //  logical block: call
        $output = SubRow::format($rowData, $column, $renderer);

        //  logical block: check
        $this->assertStringEndsWith($expectFieldValue.'</span>', trim($output));
    }
}
