<?php

namespace Dvsa\Mot\Behat\Support\Data\Statistics;

use Dvsa\Mot\Behat\Support\Data\Collection\DataCollection;
use DvsaCommon\Date\TimeSpan;
use DvsaCommon\Dto\Common\MotTestDto;
use DvsaCommon\Enum\MotTestStatusName;

class PerformanceCalculator
{
    private $total;
    private $averageTime;
    private $percentageFailed;
    private $averageVehicleAgeInMonths;
    private $isAverageVehicleAgeAvailable;

    public function __construct(DataCollection $motCollection)
    {
        $this->calculate($motCollection);
    }

    private function calculate(DataCollection $motCollection)
    {
        $total = count($motCollection);
        $time = 0;
        $numOfFailedTests = 0;
        $age = 0;

        if ($total === 0) {
            $this->total = 0;
            $this->averageTime = new TimeSpan(0, 0, 0, 0);
            $this->percentageFailed = 0;
            $this->averageVehicleAgeInMonths = 0;
            $this->isAverageVehicleAgeAvailable = false;
        } else {
            /** @var MotTestDto $mot */
            foreach ($motCollection as $mot) {
                $completedDate = new \DateTime($mot->getCompletedDate());
                $startedDate = new \DateTime($mot->getStartedDate());
                $diff = $completedDate->diff($startedDate);

                $time += $diff->d * 24 * 60 + $diff->h * 60 + $diff->i + $diff->s / 60;

                if ($mot->getStatus() === MotTestStatusName::FAILED) {
                    $numOfFailedTests++;
                }

                $currentDate = new \DateTime($mot->getCompletedDate());
                $manufactureDate = $mot->getVehicle()->getManufactureDate();
                $diff = $currentDate->diff(new \DateTime($manufactureDate));
                $age += $diff->y * 12 + $diff->m;
            }

            $avgTime = $time / $total;

            $days = floor($avgTime / (24 * 60));
            $avgTime -= $days * 24 * 60;

            $hours = floor($avgTime / 60);
            $avgTime -= $hours * 60;

            $minutes = floor($avgTime);
            $avgTime -= $minutes;

            $seconds = floor($avgTime * 60);

            $avgVehicleAge = round($age / $total);
            $percentageFailed = ($numOfFailedTests / $total) * 100;

            $this->total = $total;
            $this->averageTime = new TimeSpan($days, $hours, $minutes, $seconds);
            $this->percentageFailed = $percentageFailed;
            $this->averageVehicleAgeInMonths = $avgVehicleAge;
            $this->isAverageVehicleAgeAvailable = true;
        }
    }

    /**
     * @return int
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * @return TimeSpan
     */
    public function getAverageTime()
    {
        return $this->averageTime;
    }

    /**
     * @return float
     */
    public function getPercentageFailed()
    {
        return $this->percentageFailed;
    }

    /**
     * @return int
     */
    public function getAverageVehicleAgeInMonths()
    {
        return $this->averageVehicleAgeInMonths;
    }

    /**
     * @return bool
     */
    public function getIsAverageVehicleAgeAvailable()
    {
        return $this->isAverageVehicleAgeAvailable;
    }
}
