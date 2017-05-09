<?php

namespace DvsaMotApi\Model\OutputFormat;

use DvsaCommonApi\Model\OutputFormat;

/**
 * Class OutputFormatDataTablesVehicle.
 */
class OutputFormatDataTablesVehicle extends OutputFormat
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
        $result = [];
        $result['vin'] = $item['_source']['vin'];
        $result['registration'] = $item['_source']['registration'];
        $result['make'] = $item['_source']['make'];
        $result['model'] = $item['_source']['model'];
        $result['displayDate'] = $item['_source']['updatedDate_display'];

        $results[$item['_source']['id']] = $result;
    }
}
