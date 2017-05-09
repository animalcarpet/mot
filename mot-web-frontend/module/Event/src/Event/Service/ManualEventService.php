<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Event\Service;

use DvsaCommon\HttpRestJson\Client as HttpRestJsonClient;
use DvsaCommon\HttpRestJson\Exception\GeneralRestException;
use DvsaCommon\InputFilter\Event\OutcomeInputFilter;
use DvsaCommon\InputFilter\Event\RecordInputFilter;
use DvsaCommon\UrlBuilder\OrganisationUrlBuilder;
use DvsaCommon\UrlBuilder\PersonUrlBuilder;
use DvsaCommon\UrlBuilder\SiteUrlBuilder;
use Event\Step\OutcomeStep;
use Event\Step\RecordStep;

class ManualEventService
{
    /**
     * @var HttpRestJsonClient
     */
    private $jsonClient;

    /**
     * @param HttpRestJsonClient $jsonClient
     */
    public function __construct(
        HttpRestJsonClient $jsonClient
    ) {
        $this->jsonClient = $jsonClient;
    }

    /**
     * @param array  $sessionData
     * @param string $category    One of AE|NT|VTS
     *
     * @return bool
     */
    public function addEvent($category, $id, array $sessionData)
    {
        $apiData = $this->prepareDataForApi($category, $sessionData);
        $url = $this->getAPIUrl($category, $id);

        try {
            $this->jsonClient->post($url, $apiData);

            return true;
        } catch (GeneralRestException $e) {
            return false;
        }
    }

    /**
     * @param string $category One of AE|NT|VTS
     * @param int    $id
     *
     * @return string|null
     */
    private function getAPIUrl($category, $id)
    {
        switch ($category) {
            case 'NT':
                return PersonUrlBuilder::byId($id)->event();
            case 'AE':
                return OrganisationUrlBuilder::organisationById($id)->createEvent();
            case 'VTS':
                return SiteUrlBuilder::site($id)->createEvent();
        }
        throw new \InvalidArgumentException('Unknown category type');
    }

    /**
     * @param string $category    One of AE|NT|VTS
     * @param array  $sessionData
     *
     * @return array
     */
    private function prepareDataForApi($category, array $sessionData)
    {
        $record = $this->dataExists($sessionData, RecordStep::STEP_ID);
        $outcome = $this->dataExists($sessionData, OutcomeStep::STEP_ID);

        return [
            'eventTypeCode' => $this->dataExists($record, RecordInputFilter::FIELD_TYPE),
            'eventOutcomeCode' => $this->dataExists($outcome, OutcomeInputFilter::FIELD_OUTCOME),
            'eventCategoryCode' => $category,
            'eventDate' => $this->dataExists($record, RecordInputFilter::FIELD_DATE),
            'eventDescription' => $this->dataExists($outcome, OutcomeInputFilter::FIELD_NOTES),
        ];
    }

    /**
     * @param array  $data
     * @param string $dataKey
     *
     * @throws \OutOfBoundsException if data does not exist
     *
     * @return array
     */
    private function dataExists(array $data, $dataKey)
    {
        if (!isset($data[$dataKey])) {
            throw new \OutOfBoundsException("Data key [{$dataKey}] not set");
        }

        return $data[$dataKey];
    }
}
