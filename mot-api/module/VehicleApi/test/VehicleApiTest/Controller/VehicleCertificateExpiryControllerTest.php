<?php

namespace VehicleApiTest\Controller;

use DvsaMotApi\Service\CertificateExpiryService;
use DvsaMotApiTest\Controller\AbstractMotApiControllerTestCase;
use VehicleApi\Controller\VehicleCertificateExpiryController;
use DvsaCommon\Date\DateUtils;

class VehicleCertificateExpiryControllerTest extends AbstractMotApiControllerTestCase
{
    /** @var VehicleCertificateExpiryController */
    protected $controller;

    protected $certExpServiceMock;

    public function setUp()
    {
        $this->setController(new VehicleCertificateExpiryController());
        parent::setUp();

        $loggerMock = $this->getMockWithDisabledConstructor(\Zend\Log\Logger::class);
        $this->serviceManager->setService('Application/Logger', $loggerMock);

        $this->certExpServiceMock = $this->getMockWithDisabledConstructor(CertificateExpiryService::class);
        $this->serviceManager->setService('CertificateExpiryService', $this->certExpServiceMock);
    }

    public function testGteFunctionsWithGivenContingencyDate()
    {
        $contingencyDatetime = '2010-10-10T10:10:11Z';
        $this->certExpServiceMock->expects($this->once())
            ->method('getExpiryDetailsForVehicle')
            ->with(42, true, DateUtils::toDateTime($contingencyDatetime));

        /** @var \HttpResponse|\Zend\Stdlib\ResponseInterface $result */
        $result = $this->getResultForAction(
            'get',
            null,
            ['id' => 42, 'isDvla' => true],
            [
                VehicleCertificateExpiryController::FIELD_CONTINGENCY_DATETIME => $contingencyDatetime,
            ],
            []
        );

        $this->assertInstanceOf('Zend\View\Model\JsonModel', $result);
    }
}
