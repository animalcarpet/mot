<?php

namespace DvsaMotApiTest\Controller;

use DvsaCommonApiTest\Controller\AbstractRestfulControllerTestCase;
use SiteApi\Service\SiteService;
use Zend\Http\Header\ContentType;
use Zend\Http\Request;

/**
 * Class AbstractMotApiControllerTestCase.
 */
abstract class AbstractMotApiControllerTestCase extends AbstractRestfulControllerTestCase
{
    protected function getMockMotTestService()
    {
        return $this->getMockServiceManagerClass('MotTestService', \DvsaMotApi\Service\MotTestService::class);
    }

    protected function getMockVehicleTestingStationService()
    {
        return $this->getMockServiceManagerClass(
            SiteService::class,
            SiteService::class
        );
    }

    protected function setJsonHeader()
    {
        $header = ContentType::fromString('content-type: application/json');
        $this->request->getHeaders()->addHeader($header);
    }

    protected function setJsonRequestContent($jsonArray)
    {
        $this->setJsonHeader();
        $this->request->setContent(json_encode($jsonArray));
    }
}
