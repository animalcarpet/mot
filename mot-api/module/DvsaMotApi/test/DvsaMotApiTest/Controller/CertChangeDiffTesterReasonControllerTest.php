<?php

namespace DvsaMotApiTest\Controller;

use DvsaMotApi\Controller\CertChangeDiffTesterReasonController;
use DvsaMotApi\Service\CertificateChangeService;

/**
 * Class CertChangeDiffTesterReasonControllerTest.
 */
class CertChangeDiffTesterReasonControllerTest extends AbstractMotApiControllerTestCase
{
    protected function setUp()
    {
        $this->controller = new CertChangeDiffTesterReasonController();
        parent::setUp();
    }

    public function testGetAll_shouldReturnCorrectData()
    {
        //given
        $expectedData = ['data' => ['id' => 1]];
        $mockCertChangeDiffTesterReasonService = $this->getMockCertChangeDiffTesterReasonService();
        $mockCertChangeDiffTesterReasonService->expects($this->once())
            ->method('getDifferentTesterReasonsAsArray')
            ->will($this->returnValue(['id' => 1]));

        //when
        $result = $this->controller->dispatch($this->request);

        //then
        $this->assertEquals($expectedData, $result->getVariables());
    }

    protected function getMockCertChangeDiffTesterReasonService()
    {
        return $this->getMockServiceManagerClass(
            'CertificateChangeService',
            CertificateChangeService::class
        );
    }
}
