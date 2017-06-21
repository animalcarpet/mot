<?php

namespace DvsaMotApiTest\Controller;

require 'MotTestControllerMockSupport.php';

use DvsaCommon\Constants\SpecialNoticeAudience;
use DvsaCommon\Dto\Common\MotTestDto;
use DvsaCommon\Dto\Site\VehicleTestingStationDto;
use DvsaCommon\Enum\MotTestTypeCode;
use DvsaCommon\Enum\SiteBusinessRoleCode;
use DvsaCommon\Enum\VehicleClassCode;
use DvsaCommonApi\Service\Exception\ForbiddenException;
use DvsaCommonApiTest\Transaction\TestTransactionExecutor;
use DvsaEntities\Entity\MotTest;
use DvsaEntities\Entity\Person;
use DvsaEntities\Entity\Vehicle;
use DvsaMotApi\Controller\MotTestController;
use DvsaMotApi\Service\CertificateChangeService;
use DvsaMotApiTest\Traits\MockTestTypeTrait;

/**
 * Class MotTestControllerTest.
 */
class MotTestControllerTest extends AbstractMotApiControllerTestCase
{
    use MockTestTypeTrait;

    public function testGetCanBeAccessed()
    {
        $this->mockValidAuthorization([SiteBusinessRoleCode::TESTER]);

        $motTestNumber = 1;
        $expectedMotTestData = ['id' => $motTestNumber];
        $expectedData = ['data' => $expectedMotTestData];

        $mockMotTestService = $this->getMockMotTestService();

        $mockMotTestService->expects($this->once())
            ->method('getMotTestData')
            ->with($motTestNumber)
            ->willReturn($expectedMotTestData);

        $result = $this->getResultForAction('get', '', ['motTestNumber' => $motTestNumber]);

        $this->assertResponseStatusAndResult(self::HTTP_OK_CODE, $expectedData, $result);
    }

    public function testCreateMotWithValidData()
    {
        $mockVehicleId = 123;
        $motTest = $this->getMockMotTest($mockVehicleId);
        $expectedData = ['data' => ['motTestNumber' => null, 'dvsaVehicleId' => $mockVehicleId]];

        $this->request->setMethod('post');
        $this->request->getPost()->set('vehicleTestingStationId', '1');
        $this->request->getPost()->set('vehicleId', '1');
        $this->request->getPost()->set('primaryColour', 'Blue');
        $this->request->getPost()->set('secondaryColour', 'Red');
        $this->request->getPost()->set('fuelTypeId', 4);
        $this->request->getPost()->set('vehicleClassCode', VehicleClassCode::CLASS_5);
        $this->request->getPost()->set('hasRegistration', true);

        $mockMotTestService = $this->getMockMotTestService();
        $mockMotTestService->expects($this->once())
            ->method('createMotTest')
            ->will($this->returnValue($motTest));

        $result = $this->controller->dispatch($this->request);

        $this->assertResponseStatusAndResult(self::HTTP_OK_CODE, $expectedData, $result);
    }

    /**
     * @expectedException     \DvsaCommonApi\Service\Exception\ForbiddenException
     * @expectedExceptionCode 403
     */
    public function testCreateWithInvalidReturnsBadRequestResponse()
    {
        $forbiddenMessage = 'You are not authorised to test a class 4 vehicle';
        $this->mockValidAuthorization([SiteBusinessRoleCode::TESTER, 'NOT-THE-RIGHT-CLASS']);

        $mockMotTestService = $this->getMockMotTestService();
        $mockMotTestService->expects($this->once())
            ->method('createMotTest')
            ->willThrowException(new ForbiddenException($forbiddenMessage));

        //  --  call & check    --
        $this->request->getPost()->set('vehicleTestingStationId', '1');
        $this->request->getPost()->set('vehicleId', '1');
        $this->request->getPost()->set('primaryColour', 'Blue');
        $this->request->getPost()->set('fuelTypeId', 3);
        $this->request->getPost()->set('vehicleClass', 4);
        $this->request->getPost()->set('hasRegistration', true);
        $this->request->getPost()->set('vehicleClassCode', VehicleClassCode::CLASS_1);

        $result = $this->getResultForAction('post');
        $response = $this->getController()->getResponse();

        $this->assertResponseStatusAndResultHasError(
            $response,
            self::HTTP_ERR_403,
            $result,
            $forbiddenMessage,
            ForbiddenException::ERROR_CODE_FORBIDDEN
        );
    }

    public function testUpdateExistingMotRecordForReinspectionWithTooMuchData()
    {
        $this->mockValidAuthorization([SiteBusinessRoleCode::TESTER, 'TESTER-CLASS-1']);

        $mockMotTestService = $this->getMockMotTestService();
        $mockMotTestService->expects($this->exactly(2))
            ->method('getMotTest')
            ->with(42)
            ->will(
                $this->returnValue($this->getMotTestMock(MotTestTypeCode::TARGETED_REINSPECTION))
            );

        $this->controller = new MotTestControllerMockSupport();
        TestTransactionExecutor::inject($this->controller);
        $this->setUpController($this->controller);

        //  --  call & check    --
        $this->request->setContent(
            http_build_query(['siteid' => 42, 'location' => 'in a layby', 'operation' => 'updateSiteLocation'])
        );

        $this->getResultForAction('put', null, ['motTestNumber' => 42]);

        $this->assertResponseStatus(self::HTTP_ERR_400);
    }

    public function testUpdateExistingMotRecordForReinspectionWithExistingSiteId()
    {
        $this->mockValidAuthorization([SiteBusinessRoleCode::TESTER, 'TESTER-CLASS-1']);

        $mockMotTestService = $this->getMockMotTestService();
        $mockMotTestService->expects($this->exactly(2))
            ->method('getMotTest')
            ->with(42)
            ->willReturn(
                $this->getMotTestMock(MotTestTypeCode::TARGETED_REINSPECTION)
            );

        $site = (new VehicleTestingStationDto())->setId(1234);
        $mockTestingService = $this->getMockVehicleTestingStationService();
        $mockTestingService->expects($this->once())
            ->method('getSiteBySiteNumber')
            ->with(42)
            ->willReturn($site);

        $mockMotTestService->expects($this->once())
            ->method('updateMotTestLocation')
            ->with('validUser', 42, 1234, null)
            ->will($this->returnValue(true));

        $this->controller = new MotTestControllerMockSupport();
        TestTransactionExecutor::inject($this->controller);
        $this->setUpController($this->controller);

        //  --  call & check    --
        $this->request->setContent(http_build_query(['siteid' => 42, 'operation' => 'updateSiteLocation']));
        $this->getResultForAction('put', null, ['motTestNumber' => 42]);

        $this->assertResponseStatus(self::HTTP_OK_CODE);
    }

    public function testUpdateExistingMotRecordForReinspectionWithAdHocSiteComment()
    {
        $location = 'in a layby having a brew';
        $this->mockValidAuthorization([SiteBusinessRoleCode::TESTER, 'TESTER-CLASS-1']);

        $mockMotTestService = $this->getMockMotTestService();
        $mockMotTestService->expects($this->exactly(2))
            ->method('getMotTest')
            ->with(42)
            ->will(
                $this->returnValue($this->getMotTestMock(MotTestTypeCode::TARGETED_REINSPECTION))
            );

        $mockMotTestService->expects($this->once())
            ->method('updateMotTestLocation')
            ->with('validUser', 42, null, $location)
            ->will($this->returnValue(true));

        $this->controller = new MotTestControllerMockSupport();
        $this->setUpController($this->controller);

        //  --  call & check    --
        $this->request->setContent(http_build_query(['location' => $location, 'operation' => 'updateSiteLocation']));
        $this->getResultForAction('put', null, ['motTestNumber' => 42]);

        $this->assertResponseStatus(self::HTTP_OK_CODE);
    }

    public function testUpdateExistingMotRecordForReinspectionWithAdHocSiteCommentThatFailsToCreate()
    {
        $location = 'in a layby having a brew';
        $this->mockValidAuthorization([SiteBusinessRoleCode::TESTER, 'TESTER-CLASS-1']);

        $mockMotTestService = $this->getMockMotTestService();
        $mockMotTestService->expects($this->exactly(2))
            ->method('getMotTest')
            ->with(42)
            ->willReturn($this->getMotTestMock(MotTestTypeCode::TARGETED_REINSPECTION));

        $mockMotTestService->expects($this->once())
            ->method('updateMotTestLocation')
            ->with('validUser', 42, null, $location)
            ->will($this->returnValue(false));

        $this->controller = new MotTestControllerMockSupport();
        $this->setUpController($this->controller);

        //  --  call & check    --
        $this->request->setContent(http_build_query(['location' => $location, 'operation' => 'updateSiteLocation']));
        $this->getResultForAction('put', null, ['motTestNumber' => 42]);

        $this->assertResponseStatus(self::HTTP_ERR_400);
    }

    public function testUpdateExistingMotRecordWithOnePersonTest()
    {
        $onePersonTest = 1;
        $onePersonReInspection = 2;

        $this->mockValidAuthorization([SiteBusinessRoleCode::TESTER, 'TESTER-CLASS-1']);

        $mockMotTestService = $this->getMockMotTestService();
        $mockMotTestService->expects($this->exactly(2))
            ->method('getMotTest')
            ->with(42)
            ->willReturn($this->getMotTestMock(MotTestTypeCode::TARGETED_REINSPECTION));

        $mockMotTestService->expects($this->once())
            ->method('updateOnePersonTest')
            ->with(42, $onePersonTest, $onePersonReInspection)
            ->will($this->returnValue(true));

        $this->controller = new MotTestControllerMockSupport();
        $this->setUpController($this->controller);

        //  --  call & check    --
        $this->request->setContent(
            http_build_query(
                [
                    'onePersonTest' => $onePersonTest,
                    'onePersonReInspection' => $onePersonReInspection,
                    'operation' => 'updateOnePersonTest',
                ]
            )
        );
        $this->getResultForAction('put', null, ['motTestNumber' => 42]);

        $this->assertResponseStatus(self::HTTP_OK_CODE);
    }

    public function testUpdateExistingMotRecordWithOnePersonTestBadRequest()
    {
        $onePersonTest = null;
        $onePersonReInspection = 2;

        $this->mockValidAuthorization([SiteBusinessRoleCode::TESTER, 'TESTER-CLASS-1']);

        $mockMotTestService = $this->getMockMotTestService();
        $mockMotTestService->expects($this->exactly(2))
            ->method('getMotTest')
            ->with(42)
            ->willReturn(
                $this->getMotTestMock(MotTestTypeCode::TARGETED_REINSPECTION)
            );

        $mockMotTestService->expects($this->never())
            ->method('updateOnePersonTest');

        $this->controller = new MotTestControllerMockSupport();
        $this->setUpController($this->controller);

        //  --  call & check    --
        $this->request->setContent(
            http_build_query(
                [
                    'onePersonTest' => $onePersonTest,
                    'onePersonReInspection' => $onePersonReInspection,
                    'operation' => 'updateOnePersonTest',
                ]
            )
        );
        $this->getResultForAction('put', null, ['motTestNumber' => 42]);

        $this->assertResponseStatus(self::HTTP_ERR_400);
    }

    /**
     * @dataProvider certificateDetailsDataProvider
     */
    public function testCertificateDetailsAction($replacementValue, $isReplacement)
    {
        $this->mockValidAuthorization([SiteBusinessRoleCode::TESTER, 'TESTER-CLASS-1']);

        $motTestNr = 999;
        $motTestId = 888;

        $motTestDto = new MotTestDto();
        $motTestDto->setId($motTestId)
            ->setMotTestNumber($motTestNr);

        $mockMotTestService = $this->getMockMotTestService();
        $mockMotTestService->expects($this->once())
            ->method('getMotTestData')
            ->with($motTestNr)
            ->willReturn($motTestDto);

        $mockMotTestService->expects($this->once())
            ->method('getCertificateIds')
            ->with($motTestDto)
            ->willReturn([10, 20]);

        $mockMotTestService->expects($this->once())
            ->method('getReplacementCertificate')
            ->with($motTestId)
            ->willReturn($replacementValue);

        $mockDocumentService = $this->getMockDocumentService();
        $mockDocumentService->expects($this->at(0))
            ->method('getReportName')
            ->with(10, null)
            ->willReturn('ReportA');

        $mockDocumentService->expects($this->at(1))
            ->method('getReportName')
            ->with(20, null)
            ->willReturn('ReportB');

        $this->controller = new MotTestControllerMockSupport();
        $this->setUpController($this->controller);

        $result = $this->getResultForAction('get', 'getCertificateDetails', ['motTestNumber' => $motTestNr]);

        $expectedData = [
            ['documentId' => 10, 'reportName' => 'ReportA', 'isReplacement' => $isReplacement],
            ['documentId' => 20, 'reportName' => 'ReportB', 'isReplacement' => $isReplacement],
        ];

        $this->assertResponseStatusAndResult(self::HTTP_OK_CODE, ['data' => $expectedData], $result);
    }

    public function certificateDetailsDataProvider()
    {
        return [
            ['truthy', true],
            [null, false],
        ];
    }

    protected function setUp()
    {
        $this->controller = new MotTestController();
        TestTransactionExecutor::inject($this->controller);
        parent::setUp();
    }

    protected function getMockDocumentService()
    {
        return $this->getMockServiceManagerClass(
            'DocumentService',
            \DvsaDocument\Service\Document\DocumentService::class
        );
    }

    protected function getMockMotTestRepository()
    {
        return $this->getMockServiceManagerClass(
            'MotTestRepository',
            \DvsaEntities\Repository\MotTestRepository::class
        );
    }

    protected function getMockCertificateChangeReasonService()
    {
        return $this->getMockServiceManagerClass(
            'CertificateChangeService',
            CertificateChangeService::class
        );
    }

    private function getMotTestMock($testTypeCode)
    {
        $testMock = $this->getMockBuilder(MotTest::class)->disableOriginalConstructor()->getMock();
        $testTypeMock = $this->getMotTestTypeMock($testTypeCode);
        $testMock->expects($this->any())
            ->method('getMotTestType')
            ->willReturn($testTypeMock);

        return $testMock;
    }

    public function testCreateMotWithValidDataAndContingency()
    {
        $mockVehicleId = 123;
        $motTest = $this->getMockMotTest($mockVehicleId);
        $expectedData = ['data' => ['motTestNumber' => null, 'dvsaVehicleId' => $mockVehicleId]];

        $mockMotTestService = $this->getMockMotTestService();
        $mockMotTestService->expects($this->once())
            ->method('createMotTest')
            ->will($this->returnValue($motTest));

        //  --  call & check    --
        $this->request->getPost()->set('vehicleTestingStationId', '1');
        $this->request->getPost()->set('vehicleId', '1');
        $this->request->getPost()->set('primaryColour', 'Blue');
        $this->request->getPost()->set('secondaryColour', 'Red');
        $this->request->getPost()->set('fuelTypeId', 4);
        $this->request->getPost()->set('vehicleClassCode', VehicleClassCode::CLASS_5);
        $this->request->getPost()->set('hasRegistration', true);
        $this->request->getPost()->set('contingencyId', 3);
        $this->request->getPost()->set(
            'contingencyDto', [
                '_class' => 'DvsaCommon\\Dto\\MotTesting\\ContingencyTestDto',
            ]
        );
        $result = $this->getResultForAction('post');

        $this->assertResponseStatusAndResult(self::HTTP_OK_CODE, $expectedData, $result);
    }

    private function getMockMotTest($vehicleId, $persenId = 5, $authorisedVehicleClass = SpecialNoticeAudience::TESTER_CLASS_4)
    {
        $person = new Person();
        $person->setId(5);

        $mockVehicleId = 123;
        $mockVehicle = new Vehicle();
        $mockVehicle->setId($mockVehicleId);

        $this->mockValidAuthorization([SiteBusinessRoleCode::TESTER, 'TESTER-CLASS-4'], null, $person);

        $motTest = new MotTest();
        $motTest->setTester($person)
            ->setVehicle($mockVehicle);

        return $motTest;
    }
}
