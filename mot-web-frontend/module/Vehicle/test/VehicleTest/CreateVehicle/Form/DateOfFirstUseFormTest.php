<?php

namespace VehicleTest\CreateVehicle\Form;

use Vehicle\CreateVehicle\Form\DateOfFirstUseForm;

class DateOfFirstUseFormTest extends \PHPUnit_Framework_TestCase
{
    const ERROR_MUST_BE_NUMBERIC = 'Can only contain numbers';
    const ERROR_ENTER_VALID_DATE = 'Enter a valid date';
    const ERROR_DATE_IS_IN_FUTURE = 'Enter a date in the past';

    public function setUp()
    {
        parent::setUp();
    }

    /**
     * @dataProvider validDataProvider
     */
    public function testWhenValidDateIsEntered_noValidationMessageIsShown($day, $month, $year)
    {
        $form = $this->buildForm(null);
        $form->setData($this->setDataValues($day, $month, $year));

        $this->assertTrue($form->isValid());
        $this->assertCount(0, $form->getMessages());
    }

    /**
     * @dataProvider emptyDataProvider
     */
    public function testWhenOneOrMoreFieldsAreLeftEmpty_validationWillFail($day, $month, $year)
    {
        $form = $this->buildForm(null);
        $form->setData($this->setDataValues($day, $month, $year));

        $this->assertFalse($form->isValid());
        $this->assertCount(1, $form->getMessages());
        $this->assertSame(self::ERROR_ENTER_VALID_DATE, $form->getMessages()[DateOfFirstUseForm::FIELD_DAY][0]);
    }

    /**
     * @dataProvider nonNumericDataProvider
     */
    public function testWhenNonNumericCharactersAreEntered_validationWillFail($day, $month, $year)
    {
        $form = $this->buildForm(null);
        $form->setData($this->setDataValues($day, $month, $year));

        $this->assertFalse($form->isValid());
        $this->assertCount(1, $form->getMessages());
        $this->assertSame(self::ERROR_MUST_BE_NUMBERIC, $form->getMessages()[DateOfFirstUseForm::FIELD_DAY][0]);
    }

    /**
     * @dataProvider invalidDataProvider
     */
    public function testwhenInvalidDateisEntered_validationWillFail($day, $month, $year)
    {
        $form = $this->buildForm(null);
        $form->setData($this->setDataValues($day, $month, $year));

        $this->assertFalse($form->isValid());
        $this->assertCount(1, $form->getMessages());
        $this->assertSame(self::ERROR_ENTER_VALID_DATE, $form->getMessages()[DateOfFirstUseForm::FIELD_DAY][0]);
    }

    /**
     * @dataProvider futureDataProvider
     */
    public function testwhenDateEnteredIsInTheFuture_validationWillFail($day, $month, $year)
    {
        $form = $this->buildForm(null);
        $form->setData($this->setDataValues($day, $month, $year));

        $this->assertFalse($form->isValid());
        $this->assertCount(1, $form->getMessages());
        $this->assertSame(self::ERROR_DATE_IS_IN_FUTURE, $form->getMessages()[DateOfFirstUseForm::FIELD_DAY][0]);
    }

    public function validDataProvider() {
        return [
            ['day' => '14', 'month' => '11', 'year' => '2010'],
            ['day' => '01', 'month' => '12', 'year' => '1990'],
            ['day' => '31', 'month' => '01', 'year' => '2014'],
            ['day' => '29', 'month' => '02', 'year' => '2016'],
            ['day' => '30', 'month' => '11', 'year' => '2015'],
        ];
    }

    public function emptyDataProvider() {
        return [
            ['day' => '', 'month' => '', 'year' => ''],
            ['day' => '', 'month' => '12', 'year' => '1990'],
            ['day' => '31', 'month' => '', 'year' => '2014'],
            ['day' => '31', 'month' => '01', 'year' => ''],
        ];
    }

    public function nonNumericDataProvider() {
        return [
            ['day' => 'a', 'month' => 'b', 'year' => 'c'],
            ['day' => 'a', 'month' => '12', 'year' => '1990'],
            ['day' => '31', 'month' => 'b', 'year' => '2014'],
            ['day' => '31', 'month' => '01', 'year' => 'c'],
            ['day' => '31', 'month' => '01', 'year' => '2014c'],
        ];
    }

    public function invalidDataProvider() {
        return [
            ['day' => '144', 'month' => '11', 'year' => '2010'],
            ['day' => '01', 'month' => '13', 'year' => '1990'],
            ['day' => '31', 'month' => '01', 'year' => '20144'],
            ['day' => '30', 'month' => '02', 'year' => '2016'],
            ['day' => '29', 'month' => '02', 'year' => '2015'],
            ['day' => '29', 'month' => '02', 'year' => '201'],
            ['day' => '29', 'month' => '02', 'year' => '20'],
            ['day' => '29', 'month' => '02', 'year' => '2'],
        ];
    }

    public function futureDataProvider() {

        $futureByOneDayDate = new \DateTime();
        $futureByOneDayDate->modify('+1 day');

        $futureByOneMonthDate = new \DateTime();
        $futureByOneMonthDate->modify('+1 month');

        $futureByOneYearDate = new \DateTime();
        $futureByOneYearDate->modify('+1 year');

        $futureDateData = [];

        array_push($futureDateData, ['day' => $futureByOneDayDate->format('d'), 'month' => $futureByOneDayDate->format('n'), 'year' => $futureByOneDayDate->format('Y')]);
        array_push($futureDateData, ['day' => $futureByOneMonthDate->format('d'), 'month' => $futureByOneMonthDate->format('n'), 'year' => $futureByOneMonthDate->format('Y')]);
        array_push($futureDateData, ['day' => $futureByOneYearDate->format('d'), 'month' => $futureByOneYearDate->format('n'), 'year' => $futureByOneYearDate->format('Y')]);

        return $futureDateData;
    }

    private function setDataValues($day, $month, $year)
    {
        return [
            DateOfFirstUseForm::FIELD_DAY => $day,
            DateOfFirstUseForm::FIELD_MONTH => $month,
            DateOfFirstUseForm::FIELD_YEAR => $year
        ];
    }

    private function buildForm($dateData)
    {
        return new DateOfFirstUseForm($dateData);
    }
}