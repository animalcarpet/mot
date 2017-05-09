<?php

namespace PersonApi\Controller;

use DvsaCommon\Http\HttpStatus;
use DvsaCommonApi\Controller\AbstractDvsaRestfulController;
use DvsaCommonApi\Model\ApiResponse;
use PersonApi\Service\PersonEventService;

class PersonEventController extends AbstractDvsaRestfulController
{
    /**
     * @var PersonEventService
     */
    private $personEventService;

    public function __construct(
        PersonEventService $personEventService
    ) {
        $this->personEventService = $personEventService;
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
            $personId = $this->params()->fromRoute('id');
            $result = $this->personEventService->create($personId, $data);

            if (true === $result->isSuccessful()) {
                return ApiResponse::jsonOk(['eventId' => $result->getEventId()]);
            }
        } catch (\InvalidArgumentException $e) {
            return $this->sendError(HttpStatus::HTTP_UNPROCESSABLE_ENTITY, $e->getMessage());
        }

        return $this->sendError(HttpStatus::HTTP_UNPROCESSABLE_ENTITY, 'Unable to create an event');
    }
}
