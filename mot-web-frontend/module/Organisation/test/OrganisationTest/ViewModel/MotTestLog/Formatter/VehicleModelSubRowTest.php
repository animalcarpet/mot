<?php

namespace OrganisationTest\ViewModel\MotTestLog;

use DvsaCommonTest\TestUtils\TestCaseViewTrait;
use Organisation\ViewModel\MotTestLog\Formatter\VehicleModelSubRow;
use Report\Table\ColumnOptions;

class VehicleModelSubRowTest extends \PHPUnit_Framework_TestCase
{
    use TestCaseViewTrait;

    public function testFormat()
    {
        //  logical block: create view renderer
        $renderer = $this->getPhpRenderer(
            [
                'mot-test-log/formatter/vehicle-model-sub-row' => __DIR__.'/../../../../../view/organisation/mot-test-log/formatter/vehicle-model-sub-row.phtml',
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
        $actual = VehicleModelSubRow::format($rowData, $column, $renderer);

        //  logical block: check
        $doc = new \DOMDocument();
        $doc->loadHTML($actual);
        $xpath = new \DOMXPath($doc);

        $entries = $xpath->query('//span');
        $this->assertEquals($expectFieldValue, trim($entries->item(0)->nodeValue));
    }
}
