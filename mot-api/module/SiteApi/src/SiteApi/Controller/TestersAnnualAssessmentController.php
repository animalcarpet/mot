<?php

namespace SiteApi\Controller;

use DvsaCommon\DtoSerialization\DtoReflectiveSerializer;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaCommonApi\Controller\AbstractDvsaRestfulController;
use DvsaCommonApi\Model\ApiResponse;
use SiteApi\Service\TestersAnnualAssessmentService;

class TestersAnnualAssessmentController extends AbstractDvsaRestfulController implements AutoWireableInterface
{
    private $dtoReflectiveSerializer;
    private $testersAnnualAssessmentService;

    public function __construct(
        DtoReflectiveSerializer $dtoReflectiveSerializer,
        TestersAnnualAssessmentService $testersAnnualAssessmentService
    )
    {
        $this->dtoReflectiveSerializer = $dtoReflectiveSerializer;
        $this->testersAnnualAssessmentService = $testersAnnualAssessmentService;
    }

    public function get($siteId)
    {
        return ApiResponse::jsonOk(
            $this->dtoReflectiveSerializer->serialize(
                $this->testersAnnualAssessmentService->getTestersAnnualAssessment($siteId)
            )
        );
    }
}