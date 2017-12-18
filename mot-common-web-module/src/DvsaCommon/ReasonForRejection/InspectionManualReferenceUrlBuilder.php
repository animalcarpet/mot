<?php
namespace DvsaCommon\ReasonForRejection;

use DvsaCommon\Constants\ManualsFiles;
use DvsaCommon\Enum\VehicleClassId;
use DvsaCommon\Model\VehicleClassGroup;

class InspectionManualReferenceUrlBuilder
{
    const MANUALS_URL_PREFIX = 'documents/manuals';

    public static function build(string $inspectionManualReference, string $vehicleClassCode,
                                 bool $isPreEuDirective): string
    {
        $url = "";
        if (empty($inspectionManualReference) === false) {
            if ($isPreEuDirective) {
                $url = self::buildPreEu($inspectionManualReference, $vehicleClassCode);
            } else {
                $url = self::buildForEuRoadworthiness($inspectionManualReference, $vehicleClassCode);
            }
        }

        return $url;

    }

    private static function buildPreEu(string $inspectionManualReference, string $vehicleClassCode)
    {
        if (VehicleClassGroup::isGroupA($vehicleClassCode)) {
            $vehicleClass = VehicleClassId::CLASS_1;
        } else {
            $vehicleClass = VehicleClassId::CLASS_4;
        }

        return self::assemblePreEuManualUrl($vehicleClass, $inspectionManualReference);

    }

    private static function buildForEuRoadworthiness(string $inspectionManualReference, string $vehicleClassCode)
    {
        $result = preg_match('/(\d+)(\.\d+)+/', $inspectionManualReference, $inspectionManualReferenceSection);
        $vehicleClass = self::assembleVehicleClassUrlPart($vehicleClassCode);

        if ($result == 0) {
            return self::assembleInvalidInspectionManualReferenceUrl($inspectionManualReference, $vehicleClass);
        }

        $sectionNumber = $inspectionManualReferenceSection[1];

        $sectionUrlSlug = ManualsFiles::getFileNameFromMap($vehicleClassCode, $sectionNumber);

        return self::assembleEuRoadworthinessManualUrl($vehicleClass, $inspectionManualReference, $sectionUrlSlug);
    }

    private static function assembleInvalidInspectionManualReferenceUrl(string $inspectionManualReference, string $vehicleClass)
    {
        if (strlen($inspectionManualReference) > 0) {
            return sprintf(
                '%s/class%s/',
                self::MANUALS_URL_PREFIX,
                $vehicleClass
            );
        }
        return "";
    }

    private static function assembleVehicleClassUrlPart(string $vehicleClassCode)
    {
        if (VehicleClassGroup::isGroupA($vehicleClassCode)) {
            return implode("", VehicleClassGroup::getGroupAClasses());
        } else {
            return implode("", VehicleClassGroup::getGroupBClasses());
        }
    }

    private static function assemblePreEuManualUrl(string $vehicleClass, string $inspectionManualReference)
    {
        $matches = [];
        preg_match('/(\d+)\.(\d+)/', $inspectionManualReference, $matches);
        $url = "";

        if (count($matches) >= 2) {
            $sectionPart = $matches[1];
            $subSectionPart = $matches[2];

            $url = sprintf(
                '%s/m%ds0%s000%s01.htm',
                self::MANUALS_URL_PREFIX,
                $vehicleClass,
                $sectionPart,
                $subSectionPart
            );
        }

        return $url;
    }

    private static function assembleEuRoadworthinessManualUrl(string $vehicleClass, string $inspectionManualReference,
                                                              string $sectionUrlSlug)
    {
        $matches = [];
        $regexResult = preg_match('/\d+(\.\d+)+/', $inspectionManualReference, $matches);

        if ($regexResult == 0) {
            $sectionLink = "";
        } else {
            $sectionLink = sprintf('#section_%s', $matches[0]);
        }

        return sprintf(
            '%s/class%s/%s.html%s',
            self::MANUALS_URL_PREFIX,
            $vehicleClass,
            $sectionUrlSlug,
            $sectionLink
        );
    }
}