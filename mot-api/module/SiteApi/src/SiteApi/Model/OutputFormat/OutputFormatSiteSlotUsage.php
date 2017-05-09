<?php

namespace SiteApi\Model\OutputFormat;

use DvsaCommon\Date\DateTimeApiFormat;
use DvsaEntities\Entity\MotTest;
use DvsaCommonApi\Model\OutputFormat;

/**
 * Formats slot usage out of passed MOT test.
 */
class OutputFormatSiteSlotUsage extends OutputFormat
{
    /**
     * Responsible for extracting the current item into the required format
     * and adding to the passed results array.
     *
     * @param $results
     * @param $key
     * @param MotTest $item
     *
     * @return mixed
     *
     * @SuppressWarnings("unused")
     */
    public function extractItem(&$results, $key, $item)
    {
        $result = [
            'id' => $item->getId(),
            'date' => DateTimeApiFormat::dateTime($item->getCompletedDate()),
            'tester' => $item->getTester()->getFirstName().' '.$item->getTester()->getFamilyName(),
            'vrn' => $item->getVehicle()->getRegistration(),
        ];

        $results[$item->getId()] = $result;
    }
}
