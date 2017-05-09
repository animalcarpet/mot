<?php

namespace PersonApi\Controller;

use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaCommon\Utility\DtoHydrator;
use DvsaCommonApi\Controller\AbstractDvsaRestfulController;
use DvsaCommonApi\Model\ApiResponse;
use PersonApi\Service\DemoTestRequestsService;
use DvsaCommon\DtoSerialization\DtoReflectiveDeserializer;

class DemoTestRequestsController extends AbstractDvsaRestfulController implements AutoWireableInterface
{
    private $demoTestRequestsService;
    private $deserializer;

    public function __construct(DemoTestRequestsService $demoTestRequestsService, DtoReflectiveDeserializer $deserializer)
    {
        $this->demoTestRequestsService = $demoTestRequestsService;
        $this->deserializer = $deserializer;
    }

    public function create($data)
    {
        return ApiResponse::jsonOk($this->demoTestRequestsService->findDemoTestRequestsForUsers(DtoHydrator::jsonToDto($data)));
    }
}
