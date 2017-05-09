<?php
/**
 * This file is part of the DVSA MOT API project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace DvsaElasticSearch\Model;

use DvsaCommon\Constants\SearchParamConst;
use DvsaCommon\Date\DateTimeApiFormat;
use DvsaCommon\Date\DateTimeDisplayFormat;
use DvsaCommon\Date\DateUtils;
use DvsaCommon\Dto\Search\SearchResultDto;
use DvsaCommonApi\Service\Exception\BadRequestException;
use DvsaMotApi\Model\OutputFormat\OutputFormatDataTablesMotTest;

/**
 * Class ESDocMotTest.
 *
 * I manage the data for an MOT test and can return it in various formats.
 */
class ESDocMotTest extends ESDocType
{
    /**
     * Return the internal state for ES consumption.
     *
     * @param \DvsaEntities\Entity\MotTest $entity
     *
     * @return array
     */
    public function asEsData($entity)
    {
        $mappedRfrs = $this->getMotReasonsForRejectionStringsGroupedByType($entity->getMotTestReasonForRejections());
        $testDate = $entity->getCompletedDate() !== null ? $entity->getCompletedDate() :
            ($entity->getStartedDate() !== null ? $entity->getStartedDate() : null);

        $testDateTz = DateTimeApiFormat::dateTime($testDate);
        $testDateDisplay = !is_null($testDate) ? DateTimeDisplayFormat::dateTimeShort($testDate) : null;

        return [
            'motTestNumber' => $entity->getNumber(),
            'status' => $entity->getStatus(),
            'status_display' => $this->translateMotTestStatusForDisplay($entity->getStatus()),
            'number' => $entity->getNumber(),
            'primaryColour' => $entity->getPrimaryColour()->getName(),
            'hasRegistration' => $entity->getHasRegistration(),
            'odometerValue' => $entity->getOdometerValue(),
            'odometerUnit' => $entity->getOdometerUnit(),
            'vehicleId' => $entity->getVehicle()->getId(),
            'vin' => $entity->getVin(),
            'registration' => $entity->getRegistration(),
            'make' => $entity->getMakeName(),
            'model' => $entity->getModelName(),
            'testType' => $entity->getMotTestType()->getDescription(),
            'siteNumber' => $entity->getVehicleTestingStation() !== null ?
                    $entity->getVehicleTestingStation()->getSiteNumber() :
                    null,
            'testDate' => $testDateTz,
            'testDate_display' => $testDateDisplay,
            'startedDate' => $entity->getStartedDate() !== null ?
                    DateTimeApiFormat::dateTime($entity->getStartedDate()) :
                    null,
            'completedDate' => $entity->getCompletedDate() !== null ?
                    DateTimeApiFormat::dateTime($entity->getCompletedDate()) :
                    null,
            'startedDate_display' => $entity->getStartedDate() !== null ?
                    DateTimeDisplayFormat::dateTimeShort($entity->getStartedDate()) :
                    null,
            'completedDate_display' => $entity->getCompletedDate() !== null ?
                    DateTimeDisplayFormat::dateTimeShort($entity->getCompletedDate()) :
                    null,
            'startedDate_timestamp' => $entity->getStartedDate() !== null ?
                strtotime($entity->getStartedDate()->format('d M Y h:i'))
                + DateUtils::toUserTz($entity->getStartedDate())->getOffset() :
                null,
            'completedDate_timestamp' => $entity->getCompletedDate() !== null ?
                    strtotime($entity->getCompletedDate()->format('d M Y h:i'))
                    + DateUtils::toUserTz($entity->getCompletedDate())->getOffset() :
                    null,
            'testerId' => $entity->getTester()->getId(),
            'testerUsername' => $entity->getTester()->getUsername(),
            'reasonsForRejection' => $mappedRfrs,
        ];
    }

    protected function getMotReasonsForRejectionStringsGroupedByType($motRfrs)
    {
        $motRfrsGroupedByTypes = [];

        /**
         * @var \DvsaEntities\Entity\MotTestReasonForRejection
         */
        foreach ($motRfrs as $motRfr) {
            if (!array_key_exists($motRfr->getType()->getId(), $motRfrsGroupedByTypes)) {
                $motRfrsGroupedByTypes[$motRfr->getType()->getId()] = [];
            }
            $currentRfr = [];
            $currentRfr['id'] = $motRfr->getId();
            $currentRfr['name'] = $motRfr->getEnglishName();

            $motRfrsGroupedByTypes[$motRfr->getType()->getId()][] = $currentRfr;
        }

        return $motRfrsGroupedByTypes;
    }

    /**
     * Return the internal state for JSON or other consumption.
     *
     * @param SearchResultDto|array $results
     *
     * @return array
     *
     * @throws \DvsaCommonApi\Service\Exception\BadRequestException
     */
    public function asJson($results)
    {
        if ($results instanceof SearchResultDto) {
            $format = $results->getSearched()->getFormat();
            $data = $results->getData();
        } else {
            $format = $results['format'];
            $data = $results['data'];
        }

        if ($format == SearchParamConst::FORMAT_DATA_TABLES) {
            return (new OutputFormatDataTablesMotTest())->extractItems($data);
        }

        throw new BadRequestException(
            'Unknown search format: '.$format,
            BadRequestException::ERROR_CODE_INVALID_DATA
        );
    }

    public function translateMotTestStatusForDisplay($dbStatus)
    {
        switch ($dbStatus) {
            case 'FAILED':
                return 'FAIL';
            case 'PASSED':
                return 'PASS';
            case 'ACTIVE':
                return 'IN PROGRESS';
            default:
                return $dbStatus;
        }
    }
}
