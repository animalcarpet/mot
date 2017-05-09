<?php

namespace OrganisationApi\Controller;

use DvsaCommon\Http\HttpStatus;
use DvsaCommonApi\Controller\AbstractDvsaRestfulController;
use DvsaCommonApi\Model\ApiResponse;
use OrganisationApi\Service\OrganisationEventService;

class OrganisationEventController extends AbstractDvsaRestfulController
{
    /**
     * @var OrganisationEventService
     */
    private $organisationEventService;

    public function __construct(
        OrganisationEventService $organisationEventService
    ) {
        $this->organisationEventService = $organisationEventService;
    }

    /**
     * Helper function to send error codes back to recipient.
     *
     * @param $code
     * @param $message
     *
     * @return \Zend\View\Model\JsonModel
     */
    protected function sendError($code, $message)
    {
        $this->getResponse()->setStatusCode($code);

        return ApiResponse::jsonError(['errors' => $message]);
    }

    /**
     * @param mixed $data
     *
     * @return \Zend\View\Model\JsonModel
     */
    public function create($data)
    {
        if (!is_array($data) || empty($data)) {
            return $this->sendError(HttpStatus::HTTP_UNPROCESSABLE_ENTITY, 'Invalid data provided.');
        }

        try {
            $organisationId = $this->params()->fromRoute('organisationId');
            $result = $this->organisationEventService->create($organisationId, $data);

            if (true === $result->isSuccessful()) {
                return ApiResponse::jsonOk(['eventId' => $result->getEventId()]);
            }
        } catch (\InvalidArgumentException $e) {
            return $this->sendError(HttpStatus::HTTP_UNPROCESSABLE_ENTITY, $e->getMessage());
        }

        return $this->sendError(HttpStatus::HTTP_UNPROCESSABLE_ENTITY, 'Unable to create an event');
    }
}
