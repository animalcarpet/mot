<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Event\Step;

use DvsaCommon\InputFilter\Event\RecordInputFilter;

class RecordStep extends AbstractEventStep
{
    /**
     * const used for the session key, available via getId().
     */
    const STEP_ID = 'RECORD';

    /**
     * @var
     */
    protected $eventType;

    /**
     * @var
     */
    protected $day;

    /**
     * @var
     */
    protected $month;

    /**
     * @var
     */
    protected $year;

    /**
     * @var
     */
    protected $date;

    /**
     * @return string
     */
    public function getId()
    {
        return self::STEP_ID;
    }

    /**
     * Load the steps data from the session storage.
     *
     * @return array
     */
    public function load()
    {
        $values = $this->sessionService->load(self::STEP_ID);
        $this->readFromArray($values);

        return $this;
    }

    /**
     * @param array $values
     *
     * @return mixed
     */
    public function readFromArray(array $values)
    {
        if (is_array($values) && count($values)) {
            $this->setEventType($values[RecordInputFilter::FIELD_TYPE]);
            $this->setDate($values[RecordInputFilter::FIELD_DATE]);
            $this->setDay($values[RecordInputFilter::FIELD_DAY]);
            $this->setMonth($values[RecordInputFilter::FIELD_MONTH]);
            $this->setYear($values[RecordInputFilter::FIELD_YEAR]);
        }
    }

    /**
     * Export the step values as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            RecordInputFilter::FIELD_TYPE => $this->getEventType(),
            RecordInputFilter::FIELD_DATE => $this->makeDate(),
            RecordInputFilter::FIELD_DAY => $this->getDay(),
            RecordInputFilter::FIELD_MONTH => $this->getMonth(),
            RecordInputFilter::FIELD_YEAR => $this->getYear(),
        ];
    }

    /**
     * Convert our three fields into a string that the validaotrs expect.
     *
     * @return string
     */
    protected function makeDate()
    {
        return [
            RecordInputFilter::FIELD_DAY => $this->getDay(),
            RecordInputFilter::FIELD_MONTH => $this->getMonth(),
            RecordInputFilter::FIELD_YEAR => $this->getYear(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getCleanFilterWhiteList()
    {
        return [

        ];
    }

    /**
     * The route for this step.
     *
     * @return mixed
     */
    public function route()
    {
        return 'event-manual-add/record';
    }

    /**
     * describes the steps progress in the event process.
     *
     * Step 1 of 3
     * Step 2 of 3
     * etc
     *
     * @return string|null
     */
    public function getProgress()
    {
        return 'Step 1 of 3';
    }

    /**
     * @return mixed
     */
    public function getEventType()
    {
        return $this->eventType;
    }

    /**
     * @param mixed $eventType
     */
    public function setEventType($eventType)
    {
        $this->eventType = $eventType;
    }

    /**
     * @return mixed
     */
    public function getDay()
    {
        return $this->day;
    }

    /**
     * @param mixed $day
     */
    public function setDay($day)
    {
        $this->day = $day;
    }

    /**
     * @return mixed
     */
    public function getMonth()
    {
        return $this->month;
    }

    /**
     * @param mixed $month
     */
    public function setMonth($month)
    {
        $this->month = $month;
    }

    /**
     * @return mixed
     */
    public function getYear()
    {
        return $this->year;
    }

    /**
     * @param mixed $year
     */
    public function setYear($year)
    {
        $this->year = $year;
    }

    /**
     * @return mixed
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param mixed $date
     */
    public function setDate($date)
    {
        $this->date = $date;
    }
}
