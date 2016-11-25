<?php

namespace Dvsa\Mot\Behat\Support\Data\Statistics;

use Dvsa\Mot\Behat\Support\Data\Collection\DataCollection;
use Dvsa\Mot\Behat\Support\Data\Params\SiteParams;
use DvsaCommon\Dto\Common\MotTestDto;
use DvsaCommon\Enum\MotTestStatusName;
use DvsaCommon\Enum\MotTestTypeCode;
use DvsaCommon\Enum\VehicleClassCode;

class MotTestFilter
{
    /**
     * @param DataCollection $motCollection
     * @return DataCollection
     */
    public function filterTestsForGroupA(DataCollection $motCollection)
    {
        $vehicleClasses = [VehicleClassCode::CLASS_1, VehicleClassCode::CLASS_2];
        return $this->filterTestsWithClass($motCollection, $vehicleClasses);
    }

    /**
     * @param DataCollection $motCollection
     * @return DataCollection
     */
    public function filterTestsForGroupB(DataCollection $motCollection)
    {
        $vehicleClasses = [VehicleClassCode::CLASS_3, VehicleClassCode::CLASS_4, VehicleClassCode::CLASS_5, VehicleClassCode::CLASS_7];
        return $this->filterTestsWithClass($motCollection, $vehicleClasses);
    }

    /**
     * @param DataCollection $motCollection
     * @param array $vehicleClasses
     * @return DataCollection
     */
    private function filterTestsWithClass(DataCollection $motCollection, array $vehicleClasses)
    {
        return $motCollection->filter(function (MotTestDto $mot) use ($vehicleClasses) {
            return in_array($mot->getVehicleClass()->getCode(), $vehicleClasses);
        });
    }

    /**
     * @param DataCollection $motCollection
     * @param int $months
     * @return DataCollection
     */
    public function filterByMonths(DataCollection $motCollection, $months)
    {
        $startDate = new \DateTime(sprintf("first day of %d months ago", $months));
        $startDate->setTime(0, 0, 0);

        $endDate = new \DateTime(sprintf("last day of %d months ago", $months));
        $endDate->setTime(23, 59, 59);

        return $motCollection->filter(function (MotTestDto $mot) use ($startDate, $endDate) {
            $completedDate = new \DateTime($mot->getCompletedDate());
            $emergencyLog = $mot->getEmergencyLog();
            $correctTypes = [MotTestTypeCode::NORMAL_TEST, MotTestTypeCode::MYSTERY_SHOPPER];
            $hasCorrectStatus = in_array($mot->getStatus(), [MotTestStatusName::FAILED, MotTestStatusName::PASSED]);
            $hasCorrectType = (in_array($mot->getTestType()->getCode(), $correctTypes) && empty($emergencyLog));
            return ($completedDate >= $startDate && $completedDate <= $endDate && $hasCorrectStatus && $hasCorrectType);
        });
    }

    /**
     * @param int $testerId
     * @param DataCollection $motCollection
     * @return DataCollection
     */
    public function filterByTesterId(DataCollection $motCollection, $testerId)
    {
        return $motCollection->filter(function (MotTestDto $dto) use ($testerId) {
            return $dto->getTester()->getId() === $testerId;
        });
    }

    /**
     * @param int $siteId
     * @param DataCollection $motCollection
     * @return DataCollection
     */
    public function filterBySiteId(DataCollection $motCollection, $siteId)
    {
        return $motCollection->filter(function (MotTestDto $mot) use ($siteId) {
            return $mot->getVehicleTestingStation()[SiteParams::ID] === $siteId;
        });
    }
}
