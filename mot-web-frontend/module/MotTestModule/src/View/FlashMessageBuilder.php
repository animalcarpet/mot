<?php

namespace Dvsa\Mot\Frontend\MotTestModule\View;

/**
 * Helper class for building flash messages that contains html tags and part of the data needs to be escaped before
 * adding it to the flashManager and displaying in the view
 */
class FlashMessageBuilder
{
    /**
     * @param $defectType
     * @param $defectDetail
     * @return string
     */
    public static function defectAddedSuccessfully($defectType, $defectDetail)
    {
        return sprintf('<strong>This %s has been added:</strong><br> %s', $defectType, htmlspecialchars($defectDetail));
    }

    /**
     * @param $defectDetail
     * @return string
     */
    public static function manualAdvisoryAddedSuccessfully($defectDetail)
    {
        return sprintf('<strong>This advisory has been added:</strong><br> %s', htmlspecialchars($defectDetail));
    }

    /**
     * @param $type
     * @param $defectDetail
     * @return string
     */
    public static function defectEditedSuccessfully($type, $defectDetail)
    {
        return sprintf('<strong>This %s has been edited:</strong><br> %s', $type, htmlspecialchars($defectDetail));
    }

    /**
     * @param $defectType
     * @param $defectDescription
     * @return string
     */
    public static function defectRemovedSuccessfully($defectType, $defectDescription)
    {
        return sprintf('<strong>This %s has been removed:</strong><br> %s', $defectType, htmlspecialchars($defectDescription));
    }

    /**
     * @param $defectType
     * @param $defectDescription
     * @return string
     */
    public static function defectRepairedSuccessfully($defectType, $defectDescription)
    {
        return sprintf('The %s <strong>%s</strong> has been repaired', $defectType, htmlspecialchars($defectDescription));
    }

    /**
     * @param $defectType
     * @param $defectDescription
     * @return string
     */
    public static function defectRepairedUnsuccessfully($defectType, $defectDescription)
    {
        return sprintf('The %s <strong>%s</strong> has not been repaired. Try again.', $defectType, htmlspecialchars($defectDescription));
    }

    /**
     * @param $defectType
     * @param $defectDescription
     * @return string
     */
    public static function undoDefectRepairSuccessfully($defectType, $defectDescription)
    {
        return sprintf('The %s <strong>%s</strong> has been added', $defectType, htmlspecialchars($defectDescription));
    }

    /**
     * @param $defectType
     * @param $defectDescription
     * @return string
     */
    public static function undoDefectRepairUnsuccessfully($defectType, $defectDescription)
    {
        return sprintf('The %s <strong>%s</strong> has not been added. Try again.', $defectType, htmlspecialchars($defectDescription));
    }
}