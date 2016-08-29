<?php

namespace DvsaEventApi\Controller;

use DvsaCommonApi\Controller\AbstractDvsaRestfulController;
use DvsaCommonApi\Model\ApiResponse;
use DvsaEventApi\Service\EventPersonCreationService;

class EventPersonCreationController extends AbstractDvsaRestfulController
{
    private $eventPersonCreationService;

    public function __construct(EventPersonCreationService $eventPersonCreationService)
    {
        $this->eventPersonCreationService = $eventPersonCreationService;
    }

    public function create($data)
    {
        $id = $this->params()->fromRoute('id');

        $eventTypeCode = $data['eventTypeCode'];
        $description = $data['description'];

        $this->eventPersonCreationService->createPersonEvent($id, $eventTypeCode, $description);

        return ApiResponse::jsonOk();
    }
}
