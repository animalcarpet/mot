<?php

namespace DvsaMotApi\Controller;

use DvsaCommonApi\Controller\AbstractDvsaRestfulController;
use DvsaCommonApi\Model\ApiResponse;
use DvsaMotApi\Service\MotTestOptionsService;

class MotTestOptionsController extends AbstractDvsaRestfulController
{
    public function __construct()
    {
        $this->setIdentifierName('motTestNumber');
    }

    public function get($motTestNumber)
    {
        $motTestOptionsDto = $this->getMotTestOptionsService()->getOptions($motTestNumber);

        return ApiResponse::jsonOk($motTestOptionsDto->toArray());
    }

    /** @return MotTestOptionsService */
    private function getMotTestOptionsService()
    {
        return $this->serviceLocator->get(MotTestOptionsService::class);
    }
}
