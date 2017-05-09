<?php

namespace DvsaMotApi\Model\OutputFormat;

use DvsaCommonApi\Model\OutputFormat;
use DvsaEntities\Entity\MotTest;

/**
 * Format output for CVS file.
 */
class OutputFormatDataCsvMotTestLog extends OutputFormat
{
    /**
     * Responsible for extracting the current item into the required format.
     *
     * @param array         $results
     * @param string        $key
     * @param MotTest|array $item
     */
    public function extractItem(&$results, $key, $item)
    {
        if ($this->getSourceType() == self::SOURCE_TYPE_NATIVE) {
            $testNr = $item['number'];

            $result = [
                'siteNumber' => $item['siteNumber'],
                'clientIp' => $item['client_ip'],
                'testDateTime' => $item['testDate'],
                'testNumber' => $item['number'],
                'vehicleVRM' => $item['registration'],
                'vehicleVIN' => $item['vin'],
                'vehicleMake' => $item['makeName'],
                'vehicleModel' => $item['modelName'],
                'vehicleClass' => $item['vehicle_class'],
                'testUsername' => $item['userName'],
                'testType' => $item['testTypeName'],
                'status' => $item['status'],
                'testDuration' => gmdate('H:i:s', $item['testDuration']),
                'emRecTester' => $item['emRecTester'],
                'emRecDateTime' => $item['emRecDateTime'],
                'emReason' => $item['emReason'],
                'emCode' => $item['emCode'],
            ];

            $results[$testNr] = $result;
        }
        /* Code for Elastic Search not implemented for the moment */
        /* else {
            $src = $item['_source'];

            $testNr = $src['number'];

            $completeDate = $src['completedDate'] ?: $src['startedDate'];

            $result = [
                'siteNumber'   => $src['siteNumber'],
                'testDateTime' => $src['testDate'],
                'vehicleVRM'   => $src['registration'],
                'vehicleMake'  => $src['make'],
                'vehicleModel' => $src['model'],

                'testUsername' => $item->getTester()->getUsername(),

                'testType'     => $src['testType'],
                'status'       => $src['status'],
                'testDuration' => gmdate(
                    "H:i:s",
                    round(
                        DateUtils::getDatesTimestampDelta($completeDate, $src['startedDate'])
                        / 60
                    )
                ),
            ];
        }*/
    }
}
