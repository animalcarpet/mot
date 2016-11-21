<?php

namespace DvsaMotApiTest\Controller;

use DvsaAuthentication\Identity;
use DvsaCommon\Auth\AbstractMotAuthorisationService;
use DvsaCommon\Date\DateUtils;
use DvsaCommon\Dto\Common\MotTestDto;
use DvsaCommon\Dto\Common\MotTestTypeDto;
use DvsaCommon\Dto\Person\PersonDto;
use DvsaCommon\Enum\MotTestTypeCode;
use DvsaCommon\Enum\SiteBusinessRoleCode;
use DvsaCommon\Utility\ArrayUtils;
use DvsaCommonTest\TestUtils\Auth\AuthorisationServiceMock;
use DvsaCommonTest\TestUtils\XMock;
use DvsaDocument\Service\Document\DocumentService;
use DvsaEntities\Entity\Person;
use DvsaMotApi\Controller\CertificatePrintingController;
use DvsaMotApi\Service\CertificateCreationService;
use DvsaReport\Service\Report\ReportService;
use PHPUnit_Framework_MockObject_MockObject as MockObj;
use Zend\Authentication\AuthenticationService;
use Zend\Http\Client\Exception\RuntimeException;
use Zend\Http\Headers;
use Zend\Http\Request;
use Zend\Http\Response;
use Zend\Log\Logger;
use Zend\Stdlib\Parameters;
use Zend\Uri\Http;


class CertificatePrintingControllerTest extends AbstractMotApiControllerTestCase
{

    const SITE_ID = 5;

    /** @var  \DvsaMotApi\Service\MotTestService|MockObj */
    private $mockedTestService;
    /** @var ReportService|MockObj */
    private $mockedReportService;
    /** @var DocumentService|MockObj */
    private $mockedDocumentService;
    /** @var AbstractMotAuthorisationService */
    private $mockedAuthService;
    /** @var CertificateCreationService|MockObj */
    private $mockedCertificateCreationService;
    /** @var  AuthenticationService */
    private $mockedDvsaAuthenticationService;

    protected function setUp()
    {
        $this->mockedDocumentService = XMock::of(DocumentService::class);
        $this->controller = $this->createController();

        parent::setUp();

        $this->mockedTestService = $this->getMockMotTestService();
        $this->mockedReportService = $this->getMockReportService();

        $this->mockedCertificateCreationService = $this->getMockCertificateCreationService();
        $this->mockedDvsaAuthenticationService =  $this->getMockAuthenticationService();

        $mockLogger = XMock::of(Logger::class);

        $config = [
            "pdf" => [
                'invalidWatermarkText' => 'NOT VALID',
                'invalidWatermark' => true
            ]
        ];
        $this->serviceManager->setService('Application/Logger', $mockLogger);
        $this->serviceManager->setService('config', $config);
    }

    private function createController()
    {
        $authorisationService = Xmock::of(AbstractMotAuthorisationService::class);
        $authorisationService
            ->method("getRolesAsArray")
            ->willReturn([SiteBusinessRoleCode::TESTER]);

        $authorisationService
            ->method("isGrantedAtSite")
            ->willReturnCallback(function ($permission, $vtsId) {
                return $vtsId === self::SITE_ID;
            });

        $this->mockedAuthService = $authorisationService;

        return new CertificatePrintingController($this->mockedDocumentService, $this->mockedAuthService);
    }


    private function getMockReportService()
    {
        $mock = $this->getMockWithDisabledConstructor(ReportService::class);
        $this->serviceManager->setService('ReportService', $mock);
        return $mock;
    }

    private function getMockCertificateCreationService()
    {
        $mock = $this->getMockWithDisabledConstructor(CertificateCreationService::class);
        $this->serviceManager->setService(CertificateCreationService::class, $mock);
        return $mock;
    }

    private function setMockAuthService($mock)
    {
        $this->serviceManager->setService('DvsaAuthorisationService', $mock);
        return $mock;
    }

    private function getMockAuthenticationService()
    {
        $mock = $this->getMockWithDisabledConstructor(AuthenticationService::class);
        $this->serviceManager->setService('DvsaAuthenticationService', $mock);

        $mock->expects($this->any())->method('getIdentity')->willReturn(new Identity(new Person));

        return $mock;
    }

    public function testFailsIfInvalidAcceptTypeSpecified()
    {
        // the accepts header MUST specify PDF OR HTML
        $this->request->setHeaders(\Zend\Http\Headers::fromString('Accept: */*'));
        $result = $this->getResultForAction(null, 'print', ['id' => 1]);

        $this->assertEquals(self::HTTP_ERR_400, $result->getStatusCode());
    }

    public function testContingencyFailsIfInvalidAcceptTypeSpecified()
    {
        // the accepts header MUST specify PDF OR HTML
        $this->request->setHeaders(\Zend\Http\Headers::fromString('Accept: */*'));
        $result = $this->getResultForAction(null, 'printContingency', ['id' => 1]);

        $this->assertEquals(self::HTTP_ERR_400, $result->getStatusCode());
    }

    public function testContingencyFailsIfNoNameGiven()
    {
        $this->request->setHeaders(Headers::fromString('Accept: application/pdf'));
        $this->request->setQuery(new Parameters(['testStation' => 42, 'inspAuthority' => 'here']));

        $result = $this->getResultForAction(null, 'printContingency', ['id' => 99999]);

        /** @var \Zend\Http\PhpEnvironment\Response $response */
        $this->assertEquals(self::HTTP_ERR_400, $result->getStatusCode());
    }

    public function testContingencyFailsIfNoTestStationGiven()
    {
        $this->request->setHeaders(Headers::fromString('Accept: application/pdf'));
        $this->request->setQuery(new Parameters(['name'=>'CT32', 'inspAuthority' => 'here']));
        $result = $this->getResultForAction(
            null,
            'printContingency', [
                'id'   => 99999,
                'name' => 'CT32',
            ]
        );

        /** @var \Zend\Http\PhpEnvironment\Response $response */
        $this->assertEquals(self::HTTP_ERR_400, $result->getStatusCode());
    }

    public function testContingencyFailsIfNoInspectionAuthorityGiven()
    {
        $this->request->setHeaders(Headers::fromString('Accept: application/pdf'));
        $this->request->setQuery(new Parameters(['name'=>'CT32', 'testStation'=>'still here']));

        $result = $this->getResultForAction(
            null,
            'printContingency', [
                'id'   => 99999,
                'name' => 'CT32',
            ]
        );

        /** @var \Zend\Http\PhpEnvironment\Response $response */
        $this->assertEquals(self::HTTP_ERR_400, $result->getStatusCode());
    }

    public function testContingencyFailsIfInvalidNameGiven()
    {
        $this->request->setHeaders(Headers::fromString('Accept: application/pdf'));
        $this->request->setQuery(
            new Parameters(
                [
                 'testStation'   => 'still here',
                 'inspAuthority' => 'us'
                ]
            )
        );

        $result = $this->getResultForAction(
            null,
            'printContingency', [
                'id'   => 99999,
                'name' => 'rubbish-name',
            ]
        );

        /** @var \Zend\Http\PhpEnvironment\Response $response */
        $this->assertEquals(self::HTTP_ERR_400, $result->getStatusCode());
    }

    public function testContingencyReportGeneratesWithValidRequestData()
    {
        // pretend we generated a report
        $theReport = $this->getMock(\DvsaCommonApi\Model\ApiResponse::class, ['getStatusCode']);
        $theReport->expects($this->once())
            ->method('getStatusCode')
            ->willReturn(self::HTTP_OK_CODE);

        $this->mockedReportService->expects($this->once())
            ->method('getReport')
            ->with(
                'MOT/CT32.pdf',
                [
                    'Vts'                 => 'sesame street',
                    'InspectionAuthority' => 'the cookie monster',
                    'Watermark'           => 'NOT VALID',
                ]
            )->willReturn($theReport);

        //  --  call    --
        $this->request->setHeaders(Headers::fromString('Accept: application/pdf'));
        $this->request->setQuery(
            new Parameters(
                [
                    'testStation'   => 'sesame street',
                    'inspAuthority' => 'the cookie monster'
                ]
            )
        );

        $result = $this->getResultForAction(
            null,
            'printContingency', [
                'id'   => 99999,
                'name' => 'CT32',
            ]
        );

        /** @var \Zend\Http\PhpEnvironment\Response $response */
        $this->assertEquals(self::HTTP_OK_CODE, $result->getStatusCode());
    }

    public function testFailsWhenMotHasNoReportIds()
    {
        $motTestNr = 99999;
        $motTestId = 888;

        $motTestDto = self::createMotTestDto()
            ->setId($motTestId)
            ->setMotTestNumber($motTestNr);

        //  --  mock    --
        $this->mockedTestService->expects($this->once())
            ->method('getMotTestData')
            ->with($motTestNr)
            ->willReturn($motTestDto);

        $this->mockedTestService->expects($this->once())
            ->method('getCertificateIds')
            ->with($motTestDto)
            ->willReturn([]);

        $this->mockedCertificateCreationService->expects($this->any())
            ->method('createFromMotTestNumber')->willReturn($motTestDto);

        //  --  request    --
        $this->request->setHeaders(Headers::fromString('Accept: application/pdf'));
        $result = $this->getResultForAction('get', 'print', ['id' => $motTestNr]);

        //  --  check   --
        $this->assertEquals(self::HTTP_ERR_404, $result->getStatusCode());
    }

    public function testSetsRuntimeParametersForDuplicateIssueModeWelshVersion()
    {

        $motTestNr = 9999;
        $motTestId = 7777;

        $jasperDocumentId = 1000;
        $jasperReportName = 'MOT/foo.pdf';
        $siteNr = 'V0042';
        $garageName = 'FOO GARAGE';

        $motTestDto = self::createMotTestDto();
        $motTestDto
            ->setId($motTestId)
            ->setMotTestNumber($motTestNr)
            ->setVehicleTestingStation(['id'=>self::SITE_ID, 'siteNumber' => $siteNr, "dualLanguage" => true])
            ->setDocument($jasperDocumentId)
            ->setTester(
                (new PersonDto())
                    ->setDisplayName($garageName)
                    ->setFirstName('Bob')
                    ->setFamilyName($garageName)
            )
            ->setTestType(
                (new MotTestTypeDto())
                    ->setCode(MotTestTypeCode::NORMAL_TEST)
            );

        //  --  mock    --
        $this->mockedTestService->expects($this->once())
            ->method('getMotTestData')
            ->with($motTestNr)
            ->willReturn($motTestDto);

        // Return a SINGLE jasper document ID for printing...
        $this->mockedTestService->expects($this->once())
            ->method('getCertificateIds')
            ->with($motTestDto)
            ->willReturn([$jasperDocumentId]);

        // then return false when asked if it is a replacement
        $this->mockedTestService->expects($this->once())
            ->method('getReplacementCertificate')
            ->with($motTestId)
            ->willReturn(false);

        // now return a report name i.e. the tail-end of Jasper REST url
        $this->mockedDocumentService->expects($this->once())
            ->method('getReportName')
            ->willReturn($jasperReportName);

        $this->mockedDocumentService->expects($this->any())
            ->method('getSnapshotById')
            ->willReturn(['TestNumber' => $motTestNr]);

        $sessionPerson = (new Person())
            ->setId(1)
            ->setFirstName('Simon')
            ->setMiddleName('John')
            ->setFamilyName('Smith');
        $this->mockValidAuthorization([], [], $sessionPerson);

        // now the report service called be asked to print our stuff...
        $mockResponse = $this->getMockWithDisabledConstructor(Response::class);
        $mockResponse->expects($this->once())
            ->method('getStatusCode')
            ->willReturn(self::HTTP_OK_CODE);

        $date = DateUtils::nowAsUserDateTime()->format("d F Y");
        $issuerInfo = sprintf(
            CertificatePrintingController::ISSUER_INFO_ENG,
            "Duplicate",
            $sessionPerson->getDisplayShortName(),
            $date
        );

        $date = datefmt_format_object(
            DateUtils::nowAsUserDateTime(), 'dd MMMM Y', 'cy_GB'
        );
        $issuerInfo .= " / " . sprintf(
            CertificatePrintingController::ISSUER_INFO_WEL,
            "Dyblyg",
            $sessionPerson->getDisplayShortName(),
            $date
            );

        $reportArgs = [
            [
                'documentId'    => $jasperDocumentId,
                'reportName'    => $jasperReportName,
                'runtimeParams' => [
                    'Watermark'   => 'NOT VALID',
                    CertificatePrintingController::JREPORT_PRM_ISSUER => $issuerInfo,
                    'snapshotData' => ['TestNumber' => $motTestNr]
                ]
            ]
        ];

        $this->mockedReportService->expects($this->once())
            ->method('getMergedPdfReports')
            ->with($reportArgs)
            ->willReturn($mockResponse);

        //  --  request    --
        $this->request->setHeaders(Headers::fromString('Accept: application/pdf'));

        $result = $this->getResultForAction('get', 'print', ['id' => $motTestNr, 'dupmode' => 'dup']);

        $this->assertEquals(self::HTTP_OK_CODE, $result->getStatusCode());
    }

    public function testPrintByDocIdFailsIfInvalidAcceptTypeSpecified()
    {
        // the accepts header MUST specify PDF OR HTML
        $this->request->setHeaders(\Zend\Http\Headers::fromString('Accept: */*'));
        $result = $this->getResultForAction('get', 'printByDocId', ['id' => 1]);

        $this->assertEquals(self::HTTP_ERR_400, $result->getStatusCode());
    }

    public function testPrintByDocIdInvalidReportName()
    {
        $docId = 99999;

        //  --  mock    --
        $this->mockedDocumentService->expects($this->once())
            ->method('getReportName')
            ->with($docId)
            ->willReturn(null);

        //  --  request    --
        $this->request->setHeaders(Headers::fromString('Accept: application/pdf'));
        $result = $this->getResultForAction('get', 'printByDocId', ['docId' => $docId]);

        //  --  check   --
        $this->assertEquals(self::HTTP_ERR_400, $result->getStatusCode());
    }

    public function testPrintByDocIdInvalidDocId()
    {
        //  --  mock    --
        $this->mockedDocumentService->expects($this->once())
            ->method('getReportName')
            ->willReturn('aaa');

        //  --  request    --
        $this->request->setHeaders(Headers::fromString('Accept: application/pdf'));
        $result = $this->getResultForAction('get', 'printByDocId', ['docId' => null]);

        //  --  check   --
        $this->assertEquals(self::HTTP_ERR_400, $result->getStatusCode());
    }

    /**
     * An unauthorised exception should be thrown if we fail the permission
     * check
     *
     */
    public function testUnauthExceptionWhenPrintingWithoutAuthorisation()
    {
        $this->setMockAuthService(new AuthorisationServiceMock());
        $motTestDto = self::createMotTestDto();
        $motTestDto->setVehicleTestingStation(["id" => 123321]);

        $this->mockedTestService->expects($this->once())
            ->method('getMotTestData')
            ->willReturn($motTestDto);

        $this->mockedCertificateCreationService->expects($this->any())
            ->method('createFromMotTestNumber')
            ->willReturn($motTestDto);

        // dispatch a request
        $this->request->setHeaders(Headers::fromString('Accept: application/pdf'));
        $result = $this->getResultForAction('get', 'print', ['id' => 1]);
        $this->assertEquals($result, $result);
    }

    /**
     * @dataProvider dataProviderTestGetInvalidWatermark
     */
    public function testGetInvalidWatermark($configValues, $params, $expect)
    {
        $config = [];
        foreach (['invalidWatermark', 'invalidWatermarkText'] as $idx => $key) {
            $val = ArrayUtils::tryGet($configValues, $idx);
            $val = isset($val) ? $val : ArrayUtils::tryGet($configValues, $key);

            if (isset($val)) {
                $config[$key] = $val;
            }
        }

        $this->serviceManager->setService(
            'config',
            [
                'pdf' => $config,
            ]
        );

        $actualResult = XMock::invokeMethod($this->getController(), 'getInvalidWatermark', [$params]);

        $this->assertSame($expect, $actualResult);
    }

    public function dataProviderTestGetInvalidWatermark()
    {
        $motTestDto = self::createMotTestDto();
        $motTestDto->setTestType(new MotTestTypeDto());

        return [
            [
                'config' => [
                    'invalidWatermark' => true,
                    'invalidWatermarkText' => '1234',
                ],
                'params' => $motTestDto,
                'expect' => '1234',
            ],
            [[false, '1234'], $motTestDto, ''],
            [[null, '1234'], $motTestDto, ''],
            [[1, ''], $motTestDto, ''],
            [['a', '1234'], $motTestDto, '1234'],
            [null, $motTestDto, ''],
            [
                'config' => [true, '1234'],
                'params' => self::createMotTestDto()->setTestType(
                    (new MotTestTypeDto())->setCode(MotTestTypeCode::DEMONSTRATION_TEST_FOLLOWING_TRAINING)
                ),
                'expect' => '',
            ],
        ];
    }

    /**
     * @return MotTestDto
     */
    private static function createMotTestDto()
    {
        $motTestDto = (new MotTestDto())->setMotTestNumber(1)->setVehicleTestingStation(['id' => self::SITE_ID]);

        return $motTestDto;
    }
}
