<?php

namespace DvsaMotApiTest\Test;

/**
 * Returns a ReasonForRejection JSON object for use in unit tests.
 */
class ReasonForRejectionBuilder
{
    public static function create()
    {
        return [
            0 => [
                'type' => 'FAIL',
                'locationLateral' => null,
                'locationLongitudinal' => null,
                'locationVertical' => null,
                'comment' => null,
                'failureDangerous' => false,
                'generated' => false,
                'customDescription' => null,
                'onOriginalTest' => false,
                'id' => 1,
                'rfrId' => 8460,
                'name' => 'Body condition',
                'nameCy' => '',
                'testItemSelectorDescription' => 'Body',
                'failureText' => 'or chassis has excessive corrosion seriously affecting its strength within 30cm of the body mountings',
                'testItemSelectorDescriptionCy' => null,
                'failureTextCy' => 'mae\'r siasi wedi rhydu gormod, sy\'n effeithio\'n ddifrifol ar ei gryfder o fewn 30cm i fowntinau\'r corff',
                'testItemSelectorId' => 5696,
                'inspectionManualReference' => '6.1.B.2',
                'markedAsRepaired' => false,
            ],
        ];
    }
}
