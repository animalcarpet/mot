<?php

namespace DvsaMotApiTest\Controller;

use DvsaCommon\Dto\Common\MotTestDto;
use DvsaCommon\Dto\Common\MotTestTypeDto;
use DvsaCommon\Dto\Person\PersonDto;
use DvsaCommon\Dto\VehicleClassification\VehicleClassDto;
use DvsaCommon\Enum\MotTestStatusName;
use DvsaCommon\Enum\SiteBusinessRoleCode;
use DvsaCommon\Utility\DtoHydrator;
use DvsaCommonApi\Service\Exception\ForbiddenException;
use DvsaCommonTest\TestUtils\XMock;
use DvsaMotApi\Controller\MotTestStatusController;
use DvsaMotApi\Service\CertificateCreationService;
use DvsaMotApi\Service\MotTestStatusChangeNotificationService;
use DvsaMotApi\Service\MotTestStatusChangeService;
use DvsaMotApiTest\Test\ReasonForRejectionBuilder;
use PHPUnit_Framework_MockObject_MockObject as MockObj;

/**
 * Mot Test Status Controller Test.
 */
class MotTestStatusControllerTest extends AbstractMotApiControllerTestCase
{
    /** @var MotTestStatusChangeService|MockObj */
    private $mockStatusChangeSrv;
    /** @var CertificateCreationService|MockObj */
    private $mockCertCreationSrv;
    /** @var MotTestStatusChangeNotificationService|MockObj */
    private $mockStatusChangeNotificationSrv;

    protected function setUp()
    {
        $this->mockStatusChangeSrv = XMock::of(MotTestStatusChangeService::class);
        $this->mockCertCreationSrv = XMock::of(CertificateCreationService::class);
        $this->mockStatusChangeNotificationSrv = XMock::of(MotTestStatusChangeNotificationService::class);

        $this->controller = new MotTestStatusController(
            $this->mockStatusChangeSrv,
            $this->mockCertCreationSrv,
            $this->mockStatusChangeNotificationSrv
        );

        parent::setUp();
    }

    public function testCreateWithValidData()
    {
        $documentId = 7;

        $this->mockValidAuthorization([SiteBusinessRoleCode::TESTER]);

        $motTestNumber = '1';
        $data = ['status' => 'PASSED'];
        $status = $data['status'];

        $expectedMotTestData = (new MotTestDto())
            ->setMotTestNumber($motTestNumber)
            ->setDocument($documentId)
            ->setExpiryDate('2015-01-01')
            ->setIssuedDate('2014-01-01')
            ->setTester((new PersonDto())->setDisplayName('Testy McTest'))
            ->setVehicleClass((new VehicleClassDto())->setCode(4))
            ->setTestType((new MotTestTypeDto())->setCode('EN'))
            ->setReasonsForRejection(ReasonForRejectionBuilder::create());

        $expectedData = ['data' => $expectedMotTestData];

        $this->mockMethod(
            $this->mockStatusChangeSrv, 'updateStatus', $this->once(), $expectedMotTestData, [$motTestNumber, $data]
        );

        $this->mockMethod(
            $this->mockCertCreationSrv, 'create', $this->once(), $expectedMotTestData, ['1', $expectedMotTestData]
        );

        $result = $this->getResultForAction(
            'post', null, ['motTestNumber' => $motTestNumber], null, ['status' => $status]
        );

        $this->assertResponseStatusAndResult(self::HTTP_OK_CODE, DtoHydrator::dtoToJson($expectedData), $result);
    }

    /**
     * @expectedException     \DvsaCommonApi\Service\Exception\ForbiddenException
     * @expectedExceptionCode 403
     */
    public function testCreateReturnsErrorResponse()
    {
        $this->mockValidAuthorization([SiteBusinessRoleCode::TESTER]);

        $motTestId = 1;
        $status = MotTestStatusName::PASSED;
        $forbiddenMessage = 'Test incomplete, brake tests required';

        $this->mockMethod(
            $this->mockStatusChangeSrv, 'updateStatus', $this->once(), new ForbiddenException($forbiddenMessage)
        );

        $result = $this->getResultForAction('post', null, ['id' => $motTestId], null, ['status' => $status]);

        $this->assertResponseStatusAndResultHasError(
            $this->getController()->getResponse(),
            self::HTTP_ERR_403,
            $result,
            $forbiddenMessage,
            ForbiddenException::ERROR_CODE_FORBIDDEN
        );
    }
}
