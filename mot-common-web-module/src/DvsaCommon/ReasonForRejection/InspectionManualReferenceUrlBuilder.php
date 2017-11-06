<?php
namespace DvsaCommon\ReasonForRejection;

use DvsaCommon\Enum\VehicleClassId;
use DvsaCommon\Model\VehicleClassGroup;

class InspectionManualReferenceUrlBuilder
{
    public static function build(string $inspectionManualReference, string $vehicleClassCode): string
    {
        if (VehicleClassGroup::isGroupA($vehicleClassCode)) {
            $vehicleClass = VehicleClassId::CLASS_1;
        } else {
            $vehicleClass = VehicleClassId::CLASS_4;
        }

        $url = "";
        if (empty($inspectionManualReference) === false) {
            $inspectionManualReferenceParts = explode('.', $inspectionManualReference);
            if (count($inspectionManualReferenceParts) >= 2) {
                $url =sprintf(
                    'documents/manuals/m%ds0%s000%s01.htm',
                    $vehicleClass,
                    $inspectionManualReferenceParts[0],
                    $inspectionManualReferenceParts[1]
                );
            }
        }

        return $url;
    }
}