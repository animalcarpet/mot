<?php

namespace DvsaMotApi\Model;

/**
 * Class MotTestComparator.
 */
class MotTestComparator
{
    /**
     * Compare that two Rfr arrays are the same.
     *
     * @param $arr1
     * @param $arr2
     *
     * @return array
     */
    public function compareRfrArray($arr1, $arr2)
    {
        $results = [];

        foreach ($arr1 as $rfr1) {
            $rfrEqual = false;
            foreach ($arr2 as $rfr2) {
                if ($this->rfrIsEqual($rfr1, $rfr2)) {
                    $rfrEqual = true;
                }
            }
            if (!$rfrEqual) {
                $results[] = $rfr1;
            }
        }

        foreach ($arr2 as $rfr2) {
            $rfrEqual = false;
            foreach ($arr1 as $rfr1) {
                if ($this->rfrIsEqual($rfr1, $rfr2)) {
                    $rfrEqual = true;
                }
            }
            if (!$rfrEqual) {
                $results[] = $rfr2;
            }
        }

        return $results;
    }

    /**
     * Compare that two Rfrs are equal (by their fields).
     *
     * @param $rfr1
     * @param $rfr2
     *
     * @return bool
     */
    public function rfrIsEqual($rfr1, $rfr2)
    {
        // We can NOT compare arrays using ===  because these are not the only values that appear in the array.
        if ($rfr1['rfrId'] != $rfr2['rfrId']) {
            return false;
        }
        if ($rfr1['type'] != $rfr2['type']) {
            return false;
        }
        if ($rfr1['locationLateral'] != $rfr2['locationLateral']) {
            return false;
        }
        if ($rfr1['locationLongitudinal'] != $rfr2['locationLongitudinal']) {
            return false;
        }
        if ($rfr1['locationVertical'] != $rfr2['locationVertical']) {
            return false;
        }
        if ($rfr1['comment'] != $rfr2['comment']) {
            return false;
        }
        if ($rfr1['failureDangerous'] != $rfr2['failureDangerous']) {
            return false;
        }

        return true;
    }

    public function getMotTestRfrGroupedByManualReference($motRfrs)
    {
        $motRfrsGroupedByManualReference = [];

        foreach ($motRfrs as $motRfr) {
            $inspectionManualReference = $motRfr['inspectionManualReference'];
            if (!array_key_exists($inspectionManualReference, $motRfrsGroupedByManualReference)) {
                $motRfrsGroupedByManualReference[$inspectionManualReference] = [];
            }
            unset($motRfr['motTest']);
            $motRfrsGroupedByManualReference[$inspectionManualReference][] = $motRfr;
        }

        return $motRfrsGroupedByManualReference;
    }
}
