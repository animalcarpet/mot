<?php

namespace DvsaMotApi\Model\OutputFormat;

use DvsaCommon\Constants\OdometerReadingResultType;
use DvsaCommon\Date\DateTimeApiFormat;
use DvsaCommon\Enum\MotTestTypeCode;
use DvsaCommonApi\Model\OutputFormat;
use DvsaEntities\Entity\MotTest;
use DvsaEntities\Entity\OdometerReading;

/**
 * Class OutputFormatDataTablesMotTest
 *
 * @package DvsaMotApi\Model\OutputFormat
 */
class OutputFormatDataTablesMotTest extends OutputFormat
{
    /** String for an unreadable odometer entry */
    const TEXT_NOT_READABLE = 'Unreadable';
    /** String for when no odometer was present */
    const TEXT_NO_ODOMETER = 'No odometer';
    /** String for when no reading was recorded, e.g. an aborted or abandoned test */
    const TEXT_NOT_RECORDED = 'Not recorded';

    /**
     * Responsible for extracting the current item into the required format
     *
     * @param array         $results
     * @param string        $key
     * @param MotTest|array $item
     *
     * @return void
     */
    public function extractItem(&$results, $key, $item)
    {
        if ($item instanceof MotTest) {
            $testNr = $item->getNumber();

            $reasonForCancel = $item->getMotTestReasonForCancel();
            $testDate = $item->getCompletedDate() !== null ? $item->getCompletedDate() :
                ($item->getStartedDate() !== null ? $item->getStartedDate() : null);

            $motTestType = $item->getMotTestType();

            $isDemoTest = ($motTestType->getCode() === MotTestTypeCode::DEMONSTRATION_TEST_FOLLOWING_TRAINING);

            $result = [
                'status'                => $item->getStatus(),
                'motTestNumber'         => $testNr,
                'primaryColour'         => $item->getPrimaryColour()->getName(),
                'hasRegistration'       => $item->getHasRegistration(),
                'odometer'              => $this->getOdometer($item->getOdometerReading()),
                'vin'                   => $item->getVin(),
                'registration'          => $item->getRegistration(),
                'make'                  => $item->getMakeName(),
                'model'                 => $item->getModelName(),
                'testType'              => $item->getMotTestType()->getDescription(),
                'siteId'                => $this->getSiteId($item, $isDemoTest),
                'siteNumber'            => $this->getSiteNumber($item, $isDemoTest),
                'startedDate'           => $item->getStartedDate() !== null ?
                    DateTimeApiFormat::dateTime($item->getStartedDate()) :
                    null,
                'completedDate'         => DateTimeApiFormat::dateTime(
                    $item->getCompletedDate() !== null
                        ? $item->getCompletedDate()
                        : $item->getStartedDate()
                ),
                'testerUsername'        => $item->getTester()->getUsername(),
                'reasonsForRejection'   => $reasonForCancel ? $reasonForCancel->getReason() : null,
                'testDate'              => DateTimeApiFormat::dateTime($testDate),
            ];
        } else {
            $src = $item['_source'];

            $testNr = $src['number'];

            $result = [
                'status'              => $src['status'],
                'motTestNumber'       => $testNr,
                'primaryColour'       => $src['primaryColour'],
                'hasRegistration'     => $src['hasRegistration'],
                'odometer'            => $this->getOdometer($src),
                'vin'                 => $src['vin'],
                'registration'        => $src['registration'],
                'make'                => $src['make'],
                'model'               => $src['model'],
                'testType'            => $src['testType'],
                'siteId'              => $src['siteId'],
                'siteNumber'          => $src['siteNumber'],
                'startedDate'         => $src['startedDate'],
                'completedDate'       => $src['completedDate'] ?: $src['startedDate'],
                'testerUsername'      => $src['testerUsername'],
                'reasonsForRejection' => $src['reasonsForRejection'],
                'testDate'            => $src['testDate'],
            ];
        }

        $results[$testNr] = $result;
    }

    private function getOdometer($data)
    {
        if ($data === null) {
            return self::TEXT_NOT_RECORDED;
        }

        $type = $value = $unit = null;
        if ($data instanceof OdometerReading) {
            $type = $data->getResultType();
            $value = $data->getValue();
            $unit = $data->getUnit();
        } elseif (is_array($data)) {
            $type = $data['odometerType'];
            $value = $data['odometerValue'];
            $unit = $data['odometerUnit'];
        }

        if ($type == OdometerReadingResultType::OK) {
            $result = $value . ' ' . $unit;
        } elseif ($type == OdometerReadingResultType::NOT_READABLE) {
            $result = self::TEXT_NOT_READABLE;
        } elseif ($type == OdometerReadingResultType::NO_ODOMETER) {
            $result = self::TEXT_NO_ODOMETER;
        } else {
            $result = self::TEXT_NOT_RECORDED;
        }

        return $result;
    }

    /**
     * @param MotTest $item
     * @param boolean $isDemoTest
     * @return int|null
     */
    private function getSiteId(MotTest $item, $isDemoTest)
    {
        if($isDemoTest || !is_object($item->getVehicleTestingStation())){
            return null;
        }

        return $item->getVehicleTestingStation()->getId();
    }

    /**
     * @param MotTest $item
     * @param boolean $isDemoTest
     * @return null|string
     */
    private function getSiteNumber(MotTest $item, $isDemoTest)
    {
        if($isDemoTest || !is_object($item->getVehicleTestingStation())){
            return null;
        }

        return $item->getVehicleTestingStation()->getSiteNumber();
    }
}
