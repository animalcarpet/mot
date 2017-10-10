<?php

namespace Dashboard\Model;

class HelpDeskContact
{
    private $phoneNumber;
    private $openingHrsWeekdays;
    private $openingHrsSaturday;
    private $openingHrsSunday;

    /**
     * HelpDeskContact constructor.
     * @param $phoneNumber
     * @param $openingHrsWeekdays
     * @param $openingHrsSaturday
     * @param $openingHrsSunday
     */
    public function __construct(string $phoneNumber, string $openingHrsWeekdays,
                                string $openingHrsSaturday, string $openingHrsSunday)
    {
        $this->phoneNumber = $phoneNumber;
        $this->openingHrsWeekdays = $openingHrsWeekdays;
        $this->openingHrsSaturday = $openingHrsSaturday;
        $this->openingHrsSunday = $openingHrsSunday;
    }

    /**
     * @return mixed
     */
    public function getPhoneNumber()
    {
        return $this->phoneNumber;
    }

    /**
     * @return mixed
     */
    public function getOpeningHrsWeekdays()
    {
        return $this->openingHrsWeekdays;
    }

    /**
     * @return mixed
     */
    public function getOpeningHrsSaturday()
    {
        return $this->openingHrsSaturday;
    }

    /**
     * @return mixed
     */
    public function getOpeningHrsSunday()
    {
        return $this->openingHrsSunday;
    }
}