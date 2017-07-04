<?php
namespace DvsaCommon\Mapper;

use DvsaCommon\Enum\VehicleClassCode;
use DvsaCommon\Enum\WeightSourceCode;
use DvsaCommon\Utility\TypeCheck;

class BrakeTestWeightSourceMapper
{
    //we only map Group B (class 3 and above)
    private $weightSourcesToBeMapped = [
        VehicleClassCode::CLASS_3 => [
            WeightSourceCode::MISW,
            WeightSourceCode::VSI,
        ],
        VehicleClassCode::CLASS_4 => [
            WeightSourceCode::MISW,
            WeightSourceCode::VSI,
        ],
        VehicleClassCode::CLASS_5 => [
            WeightSourceCode::DGW,
            WeightSourceCode::DGW_MAM,
            WeightSourceCode::VSI,
        ],
        VehicleClassCode::CLASS_7 => [
            WeightSourceCode::DGW,
            WeightSourceCode::VSI,
        ],
    ];

    private $weightSourcesToBeSaved = [
        VehicleClassCode::CLASS_3 => WeightSourceCode::ORD_MISW,
        VehicleClassCode::CLASS_4 => WeightSourceCode::ORD_MISW,
        VehicleClassCode::CLASS_5 => WeightSourceCode::ORD_DGW_MAM,
        VehicleClassCode::CLASS_7 => WeightSourceCode::ORD_DGW,
    ];

    /**
     * Maps official weight source to final weight source (which is going to be saved to Vehicle record).
     * We map classes 3 and above, only official weight sources are mapped (wrong source for class raises Exception).
     *
     * @param string $vehicleClassCode
     * @param string $weightSourceCode
     * @return string
     */
    public function mapOfficialWeightSourceToVehicleWeightSource($vehicleClassCode, $weightSourceCode) {
        $outputWeightSource = $this->getVehicleWeightSourceForOfficialWeightSource($vehicleClassCode, $weightSourceCode);

        if($outputWeightSource == null) {
            throw new \InvalidArgumentException(
                'Wrong weight source! "' . $weightSourceCode . '"' .
                ' is not official weight source for class "' . $vehicleClassCode . '"'
            );
        } else {
            return $outputWeightSource;
        }
    }

    /**
     * @param string $vehicleClassCode
     * @param string $weightSourceCode
     * @return bool
     */
    public function isOfficialWeightSource($vehicleClassCode, $weightSourceCode) {
        return $this->getVehicleWeightSourceForOfficialWeightSource($vehicleClassCode, $weightSourceCode) != null;
    }

    /**
     * @param string $vehicleClassCode
     * @param string $weightSourceCode
     * @return string
     */
    private function getVehicleWeightSourceForOfficialWeightSource($vehicleClassCode, $weightSourceCode) {
        TypeCheck::assertString($vehicleClassCode);
        TypeCheck::assertString($weightSourceCode);

        if(!array_key_exists($vehicleClassCode, $this->weightSourcesToBeMapped)) {
            return null;
        }
        $sourcesToBeMappedForClass = $this->weightSourcesToBeMapped[$vehicleClassCode];

        if(!in_array($weightSourceCode, $sourcesToBeMappedForClass)) {
            return null;
        }
        return $this->weightSourcesToBeSaved[$vehicleClassCode];
    }
}