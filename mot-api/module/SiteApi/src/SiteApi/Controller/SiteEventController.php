<?php
/**
 * This file is part of the DVSA MOT API project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace SiteApi\Controller;

use DvsaCommon\Http\HttpStatus;
use SiteApi\Service\SiteEventService;
use DvsaCommonApi\Controller\AbstractDvsaRestfulController;
use DvsaCommonApi\Model\ApiResponse;

class SiteEventController extends AbstractDvsaRestfulController
{
    /**
     * @var SiteEventService
     */
    private $siteEventService;

    public function __construct(SiteEventService $siteEventService)
    {
        $this->siteEventService = $siteEventService;
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
            $siteId = $this->params()->fromRoute('siteId');
            $result = $this->siteEventService->create($siteId, $data);

            if (true === $result->isSuccessful()) {
                return ApiResponse::jsonOk(['eventId' => $result->getEventId()]);
            }
        } catch (\InvalidArgumentException $e) {
            return $this->sendError(HttpStatus::HTTP_UNPROCESSABLE_ENTITY, $e->getMessage());
        }

        return $this->sendError(HttpStatus::HTTP_UNPROCESSABLE_ENTITY, 'Unable to create an event');
    }
}
