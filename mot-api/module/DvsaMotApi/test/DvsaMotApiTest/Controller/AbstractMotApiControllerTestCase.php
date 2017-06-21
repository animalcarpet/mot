<?php

namespace DvsaMotApiTest\Controller;

use DvsaCommonApiTest\Controller\AbstractRestfulControllerTestCase;
use DvsaMotApi\Service\MotTestService;
use SiteApi\Service\SiteService;
use Zend\Http\Header\ContentType;
use Zend\Http\Request;

/**
 * Class AbstractMotApiControllerTestCase.
 */
abstract class AbstractMotApiControllerTestCase extends AbstractRestfulControllerTestCase
{
    /**
     * @return \PHPUnit_Framework_MockObject_MockObject | MotTestService
     */
    protected function getMockMotTestService()
    {
        return $this->getMockServiceManagerClass('MotTestService', MotTestService::class);
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
