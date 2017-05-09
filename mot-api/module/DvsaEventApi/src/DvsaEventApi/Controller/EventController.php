<?php

namespace DvsaEventApi\Controller;

use DvsaCommon\Utility\DtoHydrator;
use DvsaCommonApi\Controller\AbstractDvsaRestfulController;
use DvsaCommonApi\Model\ApiResponse;
use DvsaEventApi\Service\EventService;

/**
 * This function is the main controller for the Event module
 * He is used for:
 * - Listing all the event for the AE/SITE/PERSON.
 *
 * Class EventController
 */
class EventController extends AbstractDvsaRestfulController
{
    /**
     * This function allow us to post the search form in the event history list
     * It is returning the list of the event for the AE/SITE/PERSON.
     *
     * @param array $data
     *
     * @return \Zend\View\Model\JsonModel
     */
    public function create($data)
    {
        $id = $this->params()->fromRoute('id');
        $type = $this->params()->fromRoute('type');

        $dto = DtoHydrator::jsonToDto($data);

        $results = $this->getService()->getList($id, $type, $dto);
        $jsonResponse = DtoHydrator::dtoToJson($results);

        // VM-8447 :: sanitize API response on textual content.
        $jsonResponse['events'] = array_map(
            function ($each) {
                $each['description'] = htmlentities($each['description']);

                return $each;
            }, $jsonResponse['events']
        );

        return ApiResponse::jsonOk($jsonResponse);
    }

    /**
     * This function allow us to get the information about an event.
     *
     * @param int $id
     *
     * @return \Zend\View\Model\JsonModel
     */
    public function get($id)
    {
        return ApiResponse::jsonOk(DtoHydrator::dtoToJson($this->getService()->get($id)));
    }

    /**
     * This function get the EventService from the service locator
     * and returns it.
     *
     * @return \DvsaEventApi\Service\EventService
     */
    protected function getService()
    {
        return $this->getServiceLocator()->get(EventService::class);
    }
}
