<?php

namespace VehicleApi\MysteryShopper;

/**
 * Class CampaignDates.
 */
class CampaignDates
{
    /**
     * Start date of the campaign.
     *
     * @var string
     */
    private $start;

    /**
     * End date of the campaign.
     *
     * @var string
     */
    private $end;

    /**
     * Date of the last MOT test took place.
     *
     * @var string
     */
    private $lastTest;

    /**
     * @param string $start    Campaign's start date
     * @param string $end      Campaign's end date
     * @param string $lastTest Vehicle's fake last MOT test date
     */
    public function __construct($start, $end, $lastTest)
    {
        $this->start = $start;
        $this->end = $end;
        $this->lastTest = $lastTest;
    }

    /**
     * Get the start date for the new campaign.
     *
     * @return string
     */
    public function getStart($asDateTime = false)
    {
        if (is_null($this->start)) {
            return;
        }

        if (true == $asDateTime) {
            return new \DateTime($this->start);
        }

        return $this->start;
    }

    /**
     * Get the end date for the new campaign.
     *
     * @return string
     */
    public function getEnd($asDateTime = false)
    {
        if (is_null($this->end)) {
            return;
        }

        if (true == $asDateTime) {
            return new \DateTime($this->end);
        }

        return $this->end;
    }

    /**
     * Get the last MOT test date for the new campaign
     *   to fake the history.
     *
     * @return string
     */
    public function getLastTest($asDateTime = false)
    {
        if (is_null($this->lastTest)) {
            return;
        }

        if (true == $asDateTime) {
            return new \DateTime($this->lastTest);
        }

        return $this->lastTest;
    }
}
