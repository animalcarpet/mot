<?php

namespace DvsaCommon\Date;

/**
 * This class was created for faking get current date.
 * The purpose of this class is to allow testing/demoing new RFRs
 */
final class RfrCurrentDateFaker
{
    /**
     * @var DateTimeHolder
     */
    private $dateTimeHolder;

    /**
     * @var \DateTime
     */
    private $fakeCurrentDate;

    /**
     * @param DateTimeHolder $dateTimeHolder
     * @param \DateTime|null $fakeCurrentDate
     */
    public function __construct(DateTimeHolder $dateTimeHolder, \DateTime $fakeCurrentDate = null)
    {
        $this->dateTimeHolder = $dateTimeHolder;
        $this->fakeCurrentDate = $fakeCurrentDate;
    }

    /**
     * @return \DateTime
     */
    public function getCurrentDateTime()
    {
        if ($this->isEnabled()) {
            return $this->fakeCurrentDate;
        }

        return $this->dateTimeHolder->getCurrent();
    }

    /**
     * @return bool
     */
    private function isEnabled()
    {
        return $this->fakeCurrentDate !== null;
    }

}