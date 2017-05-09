<?php

namespace DvsaMotApiTest\Controller;

use DataCatalogApi\Service\DataCatalogService;
use DvsaAuthorisation\Service\AuthorisationServiceInterface;
use DvsaCommon\Dto\Common\MotTestDto;
use DvsaCommon\Dto\Common\ReasonForRefusalDto;
use DvsaCommon\Dto\Contact\AddressDto;
use DvsaCommon\Dto\Site\SiteContactDto;
use DvsaCommon\Dto\Site\VehicleTestingStationDto;
use DvsaCommon\Dto\Vehicle\VehicleDto;
use DvsaCommon\Enum\SiteBusinessRoleCode;
use DvsaCommon\Enum\SiteContactTypeCode;
use DvsaCommonApiTest\Transaction\TestTransactionExecutor;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Entity\Person;
use DvsaMotApi\Controller\MotTestRefusalController;
use DvsaMotApi\Service\CertificateCreationService;
use DvsaMotApiTest\Traits\MockTestTypeTrait;
use PHPUnit_Framework_MockObject_MockObject as MockObj;
use SiteApi\Service\SiteService;
use VehicleApi\Service\VehicleService;

/**
 * Class MotTestRefusalControllerTest.
 */
class MotTestRefusalControllerTest extends AbstractMotApiControllerTestCase
{
    use MockTestTypeTrait;

    private static $rfrId = 999;

    /** @var VehicleService|MockObj */
    private $mockVehicleService;
    /** @var SiteService|MockObj */
    private $mockSiteService;
    /** @var DataCatalogService|MockObj */
    private $mockCatalogService;
    private $mockCertCreationService;

    public function setUp()
    {
        $this->setController(new MotTestRefusalController());

        TestTransactionExecutor::inject($this->controller);

        parent::setUp();

        $person = new Person();
        $person->setId(5);
        $this->mockValidAuthorization([SiteBusinessRoleCode::TESTER, 'TESTER-CLASS-4'], null, $person);
        $this->mockAuthServiceAsserts();
        $this->request->setMethod('post');

        $this->getMockCatalogService();
    }

    /**
     * Test that the current vehicle testing station id in the post data is
     * being used to lookup in the entity in the service.
     */
    public function testVtsIdFromRequestIsUsedToLookupInService()
    {
        $expectedVtsId = 1;

        $this->setJsonRequestContent(
            [
                'vehicleId' => 7,
                'siteId' => $expectedVtsId,
                'rfrId' => self::$rfrId,
            ]
        );

        //  --  mock    --
        $this->getMockSiteService();
        $this->getMockVehicleService();
        $this->getMockCerfCreationService();

        $contact = (new SiteContactDto())
            ->setType(SiteContactTypeCode::BUSINESS)
            ->setAddress(new AddressDto());
        $site = (new VehicleTestingStationDto())
            ->setId($expectedVtsId)
            ->addContact($contact);

        $this->mockSiteService->expects($this->once())
             ->method('getSite')
             ->with($this->equalTo($expectedVtsId))
            ->willReturn($site);

        //  --  request --
        $this->controller->dispatch($this->request);
    }

    /**
     * Test that the vehicle id in the post data is used to look up the entity
     * in the service.
     */
    public function testVehicleIdFromRequestIsUsedToLookupVehicleInService()
    {
        $expectedVehicleId = 7;

        $this->setJsonRequestContent(
            [
                'vehicleId' => $expectedVehicleId,
                'siteId' => 1,
                'rfrId' => self::$rfrId,
            ]
        );

        //  --  mock    --
        $this->getMockSiteService();
        $this->getMockVehicleService();
        $this->getMockCerfCreationService();

        $this->mockVehicleService
            ->expects($this->once())
            ->method('getVehicleDto')
            ->with($expectedVehicleId)
            ->willReturn(
                (new VehicleDto())
                    ->setId($expectedVehicleId)
            );

        $contact = (new SiteContactDto())
            ->setType(SiteContactTypeCode::BUSINESS)
            ->setAddress(new AddressDto());
        $site = (new VehicleTestingStationDto())
            ->setId(1)
            ->addContact($contact);

        $this->mockSiteService->expects($this->once())
            ->method('getSite')
            ->willReturn($site);

        //  --  request --
        $this->controller->dispatch($this->request);
    }

    /**
     * A JSON model should be returned on success.
     */
    public function testAnInstanceOfJsonModelIsReturned()
    {
        $this->setJsonRequestContent(
            [
                'vehicleId' => 1,
                'siteId' => 1,
                'rfrId' => self::$rfrId,
            ]
        );

        //  --  mock    --
        $this->getMockCerfCreationService();
        $this->getMockSiteService();
        $this->getMockVehicleService();

        $contact = (new SiteContactDto())
            ->setType(SiteContactTypeCode::BUSINESS)
            ->setAddress(new AddressDto());
        $site = (new VehicleTestingStationDto())
            ->setId(1)
            ->addContact($contact);

        $this->mockSiteService->expects($this->once())
            ->method('getSite')
            ->willReturn($site);

        $result = $this->controller->dispatch($this->request);

        $this->assertInstanceOf(\Zend\View\Model\JsonModel::class, $result);
    }

    /**
     * The JSON body should return a document id and name.
     */
    public function testForCorrectPropertiesInResponseBody()
    {
        $expectedDocumentId = 123;

        $this->setJsonRequestContent(
            [
                'vehicleId' => 1,
                'siteId' => 1,
                'rfrId' => self::$rfrId,
            ]
        );

        $this->getMockSiteService();
        $this->getMockVehicleService();
        $this->getMockCerfCreationService($expectedDocumentId);

        $contact = (new SiteContactDto())
            ->setType(SiteContactTypeCode::BUSINESS)
            ->setAddress(new AddressDto());
        $site = (new VehicleTestingStationDto())
            ->setId(1)
            ->addContact($contact);

        $this->mockSiteService->expects($this->once())
            ->method('getSite')
            ->willReturn($site);

        $result = $this->controller->dispatch($this->request);
        $expectedData = [
            'data' => [
                'documentId' => $expectedDocumentId,
            ],
        ];
        $this->assertEquals($expectedData, $result->getVariables());
    }

    /**
     * If CurrentVts is empty in the json body then the site service should
     * not make a call.
     */
    public function testSiteServiceIsNotCalledWhenCurrentVtsIsEmpty()
    {
        $this->setJsonRequestContent(
            [
                'vehicleId' => 1,
                'siteId' => '',
                'rfrId' => self::$rfrId,
            ]
        );

        //  --  mock    --
        $this->getMockVehicleService();
        $this->getMockCerfCreationService();

        // site service should not be called
        $this->mockSiteService = $this->getMockServiceManagerClass(
            SiteService::class,
            SiteService::class,
            ['getSite']
        );

        //  --  request and check   --
        $this->controller->dispatch($this->request);

        $this->assertResponseStatus(self::HTTP_OK_CODE);
    }

    /**
     * A stub of \DvsaMotApi\Service\VehicleService.
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockVehicleService()
    {
        $this->mockVehicleService = $this->getMockServiceManagerClass(
            VehicleService::class,
            VehicleService::class,
            ['getVehicleDto']
        );

        $this->mockVehicleService
            ->expects($this->once())
            ->method('getVehicleDto')
            ->willReturn(new VehicleDto());
    }

    /**
     * Stubs \SiteApi\Service\SiteService.
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockSiteService()
    {
        $this->mockSiteService = $this->getMockServiceManagerClass(
            SiteService::class,
            SiteService::class,
            ['getSite']
        );
    }

    /**
     * Stubs DataCatalogApi\Service\DataCatalogService.
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockCatalogService()
    {
        $this->mockCatalogService = $this->getMockServiceManagerClass(
            DataCatalogService::class,
            DataCatalogService::class,
            ['getReasonsForRefusal']
        );

        $this->mockCatalogService->expects($this->once())
            ->method('getReasonsForRefusal')
            ->willReturn([new ReasonForRefusalDto()]);
    }

    /**
     * Stubs DvsaMotApi\Service\CertificateCreationService.
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockCerfCreationService($expectedDocumentId = null)
    {
        $this->mockCertCreationService = $this->getMockServiceManagerClass(
            CertificateCreationService::class,
            CertificateCreationService::class,
            ['createFailCertificate']
        );

        $this->mockCertCreationService->expects($this->once())
            ->method('createFailCertificate')
            ->willReturn((new MotTestDto())->setDocument($expectedDocumentId ?: 1));
    }

    private function mockAuthServiceAsserts()
    {
        $authService = XMock::of(AuthorisationServiceInterface::class);
        $authService->expects($this->any())->method('isGranted')->willReturn(true);
        $authService->expects($this->any())->method('isGrantedAtSite')->willReturn(true);
        $this->serviceManager->setService('DvsaAuthorisationService', $authService);
    }
}
