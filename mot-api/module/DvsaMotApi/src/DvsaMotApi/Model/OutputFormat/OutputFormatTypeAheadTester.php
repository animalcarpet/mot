<?php

namespace DvsaMotApi\Model\OutputFormat;

use DvsaCommonApi\Model\OutputFormat;

/**
 * Class OutputFormatTypeAheadVehicleSearch.
 */
class OutputFormatTypeAheadTester extends OutputFormat
{
    /**
     * Responsible for extracting the current item into the required format.
     *
     * @param $results
     * @param $key
     * @param $item
     *
     * @return array|mixed
     */
    public function extractItem(&$results, $key, $item)
    {
        $key = 123; // phpmd fudge
        $id = $item->getId();

        if ($id) {
            $results[$id] = implode(
                ', ',
                [
                    $item->getUserName(),
                    $item->getDisplayName(),
                ]
            );
        }
    }
}
