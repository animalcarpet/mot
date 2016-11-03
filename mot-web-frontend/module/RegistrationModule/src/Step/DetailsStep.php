<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Dvsa\Mot\Frontend\RegistrationModule\Step;

use DvsaCommon\InputFilter\Registration\DetailsInputFilter;

class DetailsStep extends AbstractRegistrationStep
{
    /**
     * const used for the session key, available via getId().
     */
    const STEP_ID = "DETAILS";

    /**
     * @var string
     */
    private $firstName;

    /**
     * @var string
     */
    private $middleName;

    /**
     * @var string
     */
    private $lastName;

    /**
     * @var string
     */
    private $day;

    /**
     * @var string
     */
    private $month;

    /**
     * @var string
     */
    private $year;

    /**
     * @var array
     */
    private $date;

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
            $this->setFirstName($values[DetailsInputFilter::FIELD_FIRST_NAME]);
            $this->setMiddleName($values[DetailsInputFilter::FIELD_MIDDLE_NAME]);
            $this->setLastName($values[DetailsInputFilter::FIELD_LAST_NAME]);
            $this->setDay($values[DetailsInputFilter::FIELD_DAY]);
            $this->setMonth($values[DetailsInputFilter::FIELD_MONTH]);
            $this->setYear($values[DetailsInputFilter::FIELD_YEAR]);
            $this->setDate($this->makeDate());
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
            DetailsInputFilter::FIELD_FIRST_NAME     => $this->getFirstName(),
            DetailsInputFilter::FIELD_MIDDLE_NAME    => $this->getMiddleName(),
            DetailsInputFilter::FIELD_LAST_NAME      => $this->getLastName(),
            DetailsInputFilter::FIELD_DATE           => $this->makeDate(),
            DetailsInputFilter::FIELD_DAY            => $this->getDay(),
            DetailsInputFilter::FIELD_MONTH          => $this->getMonth(),
            DetailsInputFilter::FIELD_YEAR           => $this->getYear(),
        ];
    }

    protected function makeDate()
    {
        return [
            DetailsInputFilter::FIELD_DAY => $this->getDay(),
            DetailsInputFilter::FIELD_MONTH => $this->getMonth(),
            DetailsInputFilter::FIELD_YEAR => $this->getYear()
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getCleanFilterWhiteList()
    {
        return [
            DetailsInputFilter::FIELD_FIRST_NAME,
            DetailsInputFilter::FIELD_MIDDLE_NAME,
            DetailsInputFilter::FIELD_LAST_NAME,
            DetailsInputFilter::FIELD_DAY,
            DetailsInputFilter::FIELD_MONTH,
            DetailsInputFilter::FIELD_YEAR,
        ];
    }

    /**
     * The route for this step.
     *
     * @return mixed
     */
    public function route()
    {
        return 'account-register/details';
    }

    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @param string $firstName
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
    }

    /**
     * @return string
     */
    public function getMiddleName()
    {
        return $this->middleName;
    }

    /**
     * @param string $middleName
     */
    public function setMiddleName($middleName)
    {
        $this->middleName = $middleName;
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @param string $lastName
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
    }

    /**
     * @return string
     */
    public function getDay()
    {
        return $this->day;
    }

    /**
     * @param string $day
     */
    public function setDay($day)
    {
        $this->day = $day;
    }

    /**
     * @return string
     */
    public function getMonth()
    {
        return $this->month;
    }

    /**
     * @param string $month
     */
    public function setMonth($month)
    {
        $this->month = $month;
    }

    /**
     * @return string
     */
    public function getYear()
    {
        return $this->year;
    }

    /**
     * @param string $year
     */
    public function setYear($year)
    {
        $this->year = $year;
    }

    /**
     * @return array
     */
    public function getDate()
    {
        return $this->date;
    }


    /**
     * @param array $date
     */
    public function setDate($date)
    {
        $this->date = $date;
    }
}
