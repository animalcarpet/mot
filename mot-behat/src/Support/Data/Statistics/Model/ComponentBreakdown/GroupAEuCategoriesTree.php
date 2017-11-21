<?php
namespace Dvsa\Mot\Behat\Support\Data\Statistics\Model\ComponentBreakdown;

use Dvsa\Mot\Behat\Support\Data\Model\ReasonForRejection\GroupA\GroupAEuReasonForRejection;

class GroupAEuCategoriesTree
{
    const IDENTIFICATION_OF_THE_VEHICLE = 20000;

    public static function getCategoryByRfrId($rfrId)
    {
        $tree = static::get();
        $categoryId = null;
        foreach ($tree as $category => $rfrs) {
            if (in_array($rfrId, $rfrs)) {
                $categoryId = $category;
                break;
            }
        }

        if ($categoryId === null) {
            throw new \InvalidArgumentException(sprintf("Category for rfr '%s' not found", $rfrId));
        }

        return $categoryId;
    }

    public static function get()
    {
        return [
            self::IDENTIFICATION_OF_THE_VEHICLE => [
                GroupAEuReasonForRejection::BEHAT_TEST
            ],
        ];
    }
}
