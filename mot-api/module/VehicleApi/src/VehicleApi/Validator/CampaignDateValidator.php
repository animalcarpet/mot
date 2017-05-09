<?php
/**
 * This file is part of the DVSA MOT API project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace VehicleApi\Validator;

use VehicleApi\MysteryShopper\CampaignDates;
use Zend\Validator\AbstractValidator;

/**
 * Class CampaignDateValidator.
 */
class CampaignDateValidator extends AbstractValidator
{
    /**
     * Validity constants.
     *
     * @var string
     */
    const COLLIDING_START_DATE = 'startDateColliding';
    const COLLIDING_END_DATE = 'endDateColliding';
    const COVERING_ANOTHER_CAMPAIGN = 'coversAnotherCampaign';
    const IS_IN_THE_PAST = 'isInThePast';
    const END_BEFORE_START = 'endBeforeStart';
    const LAST_TEST_IN_FUTURE = 'lastTestInFuture';

    /**
     * Validation failure message template definitions.
     *
     * @var array
     */
    protected $messageTemplates = [
        self::COLLIDING_START_DATE => 'Provided start date is overlapping with an existing campaign for the same vehicle',
        self::COLLIDING_END_DATE => 'Provided end date is overlapping with an existing campaign for the same vehicle',
        self::COVERING_ANOTHER_CAMPAIGN => 'Provided start and date are covering an existing campaign for the same vehicle',
        self::IS_IN_THE_PAST => 'Campaign\'s start date must be in the future',
        self::END_BEFORE_START => 'Campaign\'s end date should be after its start date',
        self::LAST_TEST_IN_FUTURE => 'Last test\'s date must be in the past',
    ];

    /**
     * Expected key definitions.
     *
     * @var string
     */
    const KEY_START = 'start';
    const KEY_END = 'end';
    const KEY_BOOKED_DATE_RANGES = 'booked_date_ranges';

    const ERR_MSG_NOT_DATETIME = 'Provided booked campaign start and end dates must be instance of DateTime';
    const ERR_MSG_VALUE_TYPE = 'First argument passed to this validator must be instance of %s';
    const ERR_MSG_NO_CONTEXT = 'This validator is rely on the context, with the key "%s"';
    const ERR_MSG_MISSING_KEY_DESCRIPTION = ', holding all the potential existing campaign for the given campaign';

    /**
     * Start date of the current campaign (to be made).
     *
     * @var \DateTime
     */
    private $startDate;

    /**
     * End date of the current campaign (to be made).
     *
     * @var \DateTime
     */
    private $endDate;

    /**
     * Last MOT test date (to be faked).
     *
     * @var \DateTime
     */
    private $lastTestDate;

    /**
     * As part of the context list of the potentially booked campaigns for the same vehicle.
     *
     * @var array
     */
    private $bookedDateRanges;

    /**
     * To Validated all the required dates in order to start a new campaign
     *  - Campaign's start date
     *  - Campaign's end date
     *  - Fake date of the last MOT test took place on the given vehicle.
     *
     * This validator is context based as it needs the potential list of booked campaigns to check against.
     *  - all the potentially booked campaigns must be child of an element with the key "booked_date_ranges".
     *  - all the dates should be of PHP's DateTime instance.
     * e.g.
     *    "booked_date_ranges" => [
     *        [ 'start_date' => {DateTime}, 'end_date' => {DateTime} ]
     *    ];
     *
     * @param CampaignDates $value
     * @param array         $context
     *
     * @throws \InvalidArgumentException
     *
     * @return bool
     */
    public function isValid($value, $context = null)
    {
        if (!$value instanceof CampaignDates) {
            throw new \InvalidArgumentException(
                sprintf(self::ERR_MSG_VALUE_TYPE, CampaignDates::class)
            );
        }

        if (is_null($context) || false === array_key_exists(self::KEY_BOOKED_DATE_RANGES, $context)) {
            throw new \InvalidArgumentException(
                sprintf(
                    self::ERR_MSG_NO_CONTEXT.self::ERR_MSG_MISSING_KEY_DESCRIPTION,
                    self::KEY_BOOKED_DATE_RANGES
                )
            );
        }

        $isValid = true;

        $this->setCurrentCampaignStartAndEndDate($value, $context);

        $this->assertDateRangesAreDateTime();

        // validate end date is in the future
        if (!is_null($this->startDate) && $this->startDate < new \DateTime('now')) {
            $this->error(self::IS_IN_THE_PAST);
            $isValid = false;
        }

        // validate end date is greater than start date
        if (!is_null($this->startDate) && !is_null($this->endDate) && $this->startDate > $this->endDate) {
            $this->error(self::END_BEFORE_START);
            $isValid = false;
        }

        // validate last test date is in the past
        if (!is_null($this->lastTestDate) && $this->lastTestDate >= new \DateTime('now')) {
            $this->error(self::LAST_TEST_IN_FUTURE);
            $isValid = false;
        }

        if (false === $isValid) {
            return $isValid;
        }

        foreach ($this->bookedDateRanges as $bookedDateRange) {
            $bookedStartDate = $this->extractStartDate($bookedDateRange);
            $bookedEndDate = $this->extractEndDate($bookedDateRange);

            // validate the start date to make sure is not colliding with any existing booked campaigns
            if ($this->startDate >= $bookedStartDate &&
                $this->startDate <= $bookedEndDate
            ) {
                $this->error(self::COLLIDING_START_DATE);
                $isValid = false;
            }

            // validate the end date to make sure is not colliding with any existing booked campaigns
            if (
                $this->endDate >= $bookedStartDate &&
                $this->endDate <= $bookedEndDate
            ) {
                $this->error(self::COLLIDING_END_DATE);
                $isValid = false;
            }

            // validate there are no campaign within the new campaign duration.
            if (
                $this->startDate < $bookedStartDate &&
                $this->endDate > $bookedEndDate
            ) {
                $this->error(self::COVERING_ANOTHER_CAMPAIGN);
                $isValid = false;
            }

            if (false === $isValid) {
                return $isValid;
            }
        }

        return $isValid;
    }

    /**
     * Extract and Set all the required elements from the provided value and context.
     *
     * @param string $value   JSON formatted date range for the new campaign
     * @param array  $context
     */
    private function setCurrentCampaignStartAndEndDate(CampaignDates $value, $context)
    {
        $this->startDate = $value->getStart(true);

        $this->endDate = $value->getEnd(true);

        $this->lastTestDate = $value->getLastTest(true);

        $this->bookedDateRanges = $context[self::KEY_BOOKED_DATE_RANGES];
    }

    /**
     * To make sure all the provided dates in the potentially booked ranges are instance of DateTime.
     *
     * @throws \InvalidArgumentException
     */
    private function assertDateRangesAreDateTime()
    {
        foreach ($this->bookedDateRanges as $bookedDateRange) {
            if (
                !$this->extractStartDate($bookedDateRange) instanceof \DateTime ||
                !$this->extractEndDate($bookedDateRange) instanceof \DateTime
            ) {
                throw new \InvalidArgumentException(self::ERR_MSG_NOT_DATETIME);
            }
        }
    }

    /**
     * @param array $dateRange
     *
     * @return mixed
     */
    private function extractStartDate($dateRange)
    {
        return $dateRange[$this->getOption(self::KEY_START)];
    }

    /**
     * @param array $dateRange
     *
     * @return mixed
     */
    private function extractEndDate($dateRange)
    {
        return $dateRange[$this->getOption(self::KEY_END)];
    }
}
