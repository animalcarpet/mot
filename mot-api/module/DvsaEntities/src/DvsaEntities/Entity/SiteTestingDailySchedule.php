<?php

namespace DvsaEntities\Entity;

use Doctrine\ORM\Mapping as ORM;
use DvsaCommon\Date\Time;
use DvsaEntities\EntityTrait\CommonIdentityTrait;

/**
 * SiteTestingDailySchedule.
 *
 * @ORM\Table(name="site_testing_daily_schedule")
 * @ORM\Entity(repositoryClass="DvsaEntities\Repository\SiteTestingDailyScheduleRepository")
 */
class SiteTestingDailySchedule extends Entity
{
    use CommonIdentityTrait;

    /**
     * @var Site
     *
     * @ORM\ManyToOne(targetEntity="Site", fetch="LAZY", inversedBy="siteTestingSchedule")
     * @ORM\JoinColumn(name="site_id", referencedColumnName="id", nullable=false)
     */
    private $site;

    /**
     * @var Time
     *
     * @ORM\Column(name="open_time", type="Time", nullable=true)
     */
    private $openTime;

    /**
     * @var Time
     *
     * @ORM\Column(name="close_time", type="Time", nullable=true)
     */
    private $closeTime;

    /**
     * @var int
     *
     * @ORM\Column(name="weekday", type="integer", length=1, nullable=false)
     */
    private $weekday;

    public function setCloseTime(Time $closeTime = null)
    {
        $this->closeTime = $closeTime;

        return $this;
    }

    public function getCloseTime()
    {
        return $this->closeTime;
    }

    public function setOpenTime(Time $openTime = null)
    {
        $this->openTime = $openTime;

        return $this;
    }

    public function getOpenTime()
    {
        return $this->openTime;
    }

    public function setSite(Site $site)
    {
        $this->site = $site;

        return $this;
    }

    public function getSite()
    {
        return $this->site;
    }

    /**
     * @param int $weekday
     *
     * @return $this
     */
    public function setWeekday($weekday)
    {
        $this->weekday = $weekday;

        return $this;
    }

    public function getWeekday()
    {
        return $this->weekday;
    }

    /**
     * @return bool
     */
    public function isClosed()
    {
        return $this->openTime === null && $this->closeTime === null;
    }

    /**
     * @param SiteTestingDailySchedule[] $schedule
     * @param int                        $weekday
     *
     * @return SiteTestingDailySchedule
     */
    public static function getScheduleForWeekday(array $schedule, $weekday)
    {
        foreach ($schedule as $dailySchedule) {
            if ($dailySchedule->getWeekday() === $weekday) {
                return $dailySchedule;
            }
        }

        return null;
    }

    /**
     * @param Time                            $referenceTime
     * @param SiteTestingDailySchedule[]|null $schedule
     *
     * @return bool
     */
    public static function isOutsideSchedule(Time $referenceTime, $schedule = null)
    {
        // no hours defined - everything closed - temporary
        $isOutsideHours = $schedule === null || count($schedule) === 0;
        if (!$isOutsideHours) {
            $dayOfWeek = (int) $referenceTime->format('N');
            foreach ($schedule as $dayHours) {
                if ($dayHours->getWeekday() === $dayOfWeek) {
                    /* closing time of 00:00:00 must be treated as 24:00:00 to run calculation correctly */
                    $isOutsideHours = $dayHours->isClosed()
                        || ($referenceTime->lesserThan($dayHours->getOpenTime())
                            || $referenceTime->toTimestamp24() >= $dayHours->getCloseTime()->toTimestamp24());
                    break;
                }
            }
        }

        return $isOutsideHours;
    }
}
