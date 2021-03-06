<?php

namespace Site\Form;

use Site\Form\Element\SimpleRadio;
use Zend\Form\Element\Hidden;
use Zend\Form\Element\Submit;
use Zend\Validator\InArray;

class TQIMonthRangeForm
{
    const RANGE_ONE_MONTH = 1;
    const RANGE_THREE_MONTHS = 3;

    private $validMonthRanges;
    private $radio1Month;
    private $radio3Months;
    private $submit;
    private $backTo;
    private $gqr3MonthsViewEnabled;

    public function __construct(bool $gqr3MonthsViewEnabled)
    {
        $this->radio1Month = new SimpleRadio();
        $this->radio1Month
            ->setValue(self::RANGE_ONE_MONTH)
            ->setAttribute('id', 'last1Month')
            ->setName('monthRange')
            ->setLabel('Last month')
        ;

        $this->radio3Months = new SimpleRadio();
        $this->radio3Months
            ->setValue(self::RANGE_THREE_MONTHS)
            ->setName('monthRange')
            ->setLabel('Last 3 months')
            ->setAttribute('id', 'last3Months');

        $this->submit = new Submit();
        $this->submit->setValue('Update results');
        $this->submit->setName('u');
        $this->submit->setAttribute('class', 'button');

        $this->backTo = new Hidden('returnToAETQI');

        $this->toggle3MonthsView($gqr3MonthsViewEnabled);
        $this->gqr3MonthsViewEnabled = $gqr3MonthsViewEnabled;
    }

    private function toggle3MonthsView($threeMonthsViewEnabled) {
        if($threeMonthsViewEnabled) {
            $this->validMonthRanges = [
                self::RANGE_ONE_MONTH,
                self::RANGE_THREE_MONTHS
            ];
        } else {
            $this->validMonthRanges = [
                self::RANGE_ONE_MONTH
            ];
        }
    }

    public function is3MonthsViewEnabled() {
        return $this->gqr3MonthsViewEnabled;
    }

    /**
     * @return SimpleRadio
     */
    public function getRadio1Month():SimpleRadio
    {
        return $this->radio1Month;
    }

    /**
     * @return SimpleRadio
     */
    public function getRadio3Months():SimpleRadio
    {
        return $this->radio3Months;
    }

    /**
     * @return Submit
     */
    public function getSubmit():Submit
    {
        return $this->submit;
    }

    public function setValue($option):TQIMonthRangeForm
    {
        if ($option == self::RANGE_ONE_MONTH) {
            $this->radio1Month->setChecked(true);
        } else {
            $this->radio3Months->setChecked(true);
        }

        return $this;
    }

    /**
     * @param string $backTo
     * @return TQIMonthRangeForm
     */
    public function setBackTo($backTo):TQIMonthRangeForm
    {
        $this->backTo->setValue($backTo);
        return $this;
    }

    /**
     * @return Hidden
     */
    public function getBackTo():Hidden
    {
        return $this->backTo;
    }

    public function isValid(int $monthRange)
    {
        $inArray = new InArray();
        $inArray->setHaystack($this->validMonthRanges);
        return $inArray->isValid($monthRange);
    }

}