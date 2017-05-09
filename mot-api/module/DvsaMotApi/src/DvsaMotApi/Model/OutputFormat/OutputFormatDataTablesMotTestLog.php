<?php

namespace DvsaMotApi\Model\OutputFormat;

use DvsaCommon\Date\DateTimeDisplayFormat;
use DvsaCommonApi\Model\OutputFormat;
use DvsaEntities\Entity\MotTest;

/**
 * Format output for Table.
 */
class OutputFormatDataTablesMotTestLog extends OutputFormat
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

            //  --  spit on parts Date, Time and milliseconds   --
            $testDateTime = new \DateTime($item['testDate']);

            $testTime = '';
            if ($item['emLogId'] === null) {
                $testTime = DateTimeDisplayFormat::time($testDateTime);
            }

            $result = [
                'motTestNumber' => $testNr,
                'siteNumber' => $item['siteNumber'],
                'testDate' => DateTimeDisplayFormat::date($testDateTime),
                'testTime' => $testTime,
                'vehicleVRM' => $item['registration'],
                'vehicleMake' => $item['makeName'],
                'vehicleModel' => $item['modelName'],
                'testUsername' => $item['userName'],
                'testType' => $item['testTypeName'],
                'status' => $item['status'],
            ];

            $results[$testNr] = $result;
        }
    }
}
