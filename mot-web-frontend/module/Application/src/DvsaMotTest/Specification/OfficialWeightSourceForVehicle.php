<?php
namespace DvsaMotTest\Specification;

use Dvsa\Mot\ApiClient\Resource\Item\DvsaVehicle;
use Dvsa\Mot\ApiClient\Resource\Item\VehicleClass;
use Dvsa\Mot\ApiClient\Resource\Item\WeightSource;
use DvsaCommon\Enum\VehicleClassCode;
use DvsaCommon\Enum\WeightSourceCode;
use DvsaCommon\Specification\SpecificationInterface;

class OfficialWeightSourceForVehicle implements SpecificationInterface
{
    /** @var array $classCodeToOfficialWeightSourceMap */
    private $classCodeToOfficialWeightSourceMap = [
        VehicleClassCode::CLASS_3 => [
            WeightSourceCode::MISW,
            WeightSourceCode::VSI,
            WeightSourceCode::ORD_MISW,
        ],

        VehicleClassCode::CLASS_4 => [
            WeightSourceCode::MISW,
            WeightSourceCode::VSI,
            WeightSourceCode::ORD_MISW,
        ],

        VehicleClassCode::CLASS_5 => [
            WeightSourceCode::DGW,
            WeightSourceCode::DGW_MAM,
            WeightSourceCode::VSI,
            WeightSourceCode::ORD_DGW_MAM,
        ],

        VehicleClassCode::CLASS_7 => [
            WeightSourceCode::DGW,
            WeightSourceCode::VSI,
            WeightSourceCode::ORD_DGW,
        ],
    ];

    /**
     * Verify if given candidate object fulfills the specification
     *
     * @param mixed|DvsaVehicle $candidate
     * @return bool
     */
    public function isSatisfiedBy($candidate)
    {
        return
            $this->isDvsaVehicleResource($candidate) &&
            $this->isVehicleWeightSet($candidate) &&
            $this->isVehicleClassSet($candidate) &&
            $this->isVehicleWeightSourceSet($candidate) &&
            $this->isWeightSourceOfficialForThisClass($candidate);
    }

    /**
     * @param $candidate
     * @return bool
     */
    private function isDvsaVehicleResource($candidate)
    {
        return $candidate instanceof DvsaVehicle;
    }

    /**
     * @param DvsaVehicle $candidate
     * @return bool
     */
    private function isVehicleWeightSet(DvsaVehicle $candidate)
    {
        $weight = $candidate->getWeight();

        return !empty($weight) && $weight !== 0;

    }

    /**
     * @param DvsaVehicle $candidate
     * @return bool
     */
    private function isVehicleClassSet(DvsaVehicle $candidate)
    {
        $classObj = $candidate->getVehicleClass();

        return
            !empty($classObj) &&
            $classObj instanceof VehicleClass &&
            !empty($classObj->getCode()) &&
            !empty($classObj->getName());
    }

    /**
     * @param DvsaVehicle $candidate
     * @return bool
     */
    private function isVehicleWeightSourceSet(DvsaVehicle $candidate)
    {
        $sourceObj = $candidate->getWeightSource();

        return
            !empty($sourceObj) &&
            $sourceObj instanceof WeightSource &&
            !empty($sourceObj->getCode()) &&
            !empty($sourceObj->getName());
    }

    /**
     * @param DvsaVehicle $candidate
     * @return bool
     */
    private function isWeightSourceOfficialForThisClass(DvsaVehicle $candidate)
    {
        $classCode = $candidate->getVehicleClass()->getCode();
        $sourceCode = $candidate->getWeightSource()->getCode();

        if(!isset($this->classCodeToOfficialWeightSourceMap[$classCode])){
            return false;
        }

        $officialSources = $this->classCodeToOfficialWeightSourceMap[$classCode];

        return in_array($sourceCode, $officialSources);
    }
}