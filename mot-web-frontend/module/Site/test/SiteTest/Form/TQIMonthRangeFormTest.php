<?php

namespace SiteTest\Form;

use DvsaCommonTest\TestUtils\TestCaseTrait;
use Site\Form\TQIMonthRangeForm;
use Zend\Stdlib\Parameters;

class TQIMonthRangeFormTest extends \PHPUnit_Framework_TestCase
{
    use TestCaseTrait;

    /** @var TQIMonthRangeForm */
    private $model;

    public function testWhenFlagIsTrueBothRangesAreValid()
    {
        $threeMonthsRange = true;
        $this->model = new TQIMonthRangeForm($threeMonthsRange);

        $this->assertTrue($this->model->isValid(1));
        $this->assertTrue($this->model->isValid(3));
    }

    public function testWhenFlagIsFalseThreeMonthsRangeIsInvalid()
    {
        $threeMonthsRange = false;
        $this->model = new TQIMonthRangeForm($threeMonthsRange);

        $this->assertTrue($this->model->isValid(1));
        $this->assertFalse($this->model->isValid(3));
    }

    public function testWhenGettingFlagTheValueIsCorrect()
    {
        $threeMonthsRange = false;
        $this->model = new TQIMonthRangeForm($threeMonthsRange);

        $this->assertEquals($threeMonthsRange, $this->model->is3MonthsViewEnabled());

        $threeMonthsRange = true;
        $this->model = new TQIMonthRangeForm($threeMonthsRange);

        $this->assertEquals($threeMonthsRange, $this->model->is3MonthsViewEnabled());
    }
}
