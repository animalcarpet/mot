<?php

namespace DvsaMotTestTest\Controller;

use Application\Service\CatalogService;
use Core\Service\MotFrontendAuthorisationServiceInterface;
use Core\Service\MotFrontendIdentityProviderInterface;
use Dvsa\Mot\Frontend\AuthenticationModule\Model\Identity;
use Dvsa\Mot\Frontend\AuthenticationModule\Model\MotFrontendIdentityInterface;
use Dvsa\Mot\Frontend\AuthenticationModule\Model\VehicleTestingStation;
use DvsaClient\MapperFactory;
use DvsaClient\Mapper\VehicleMapper;
use DvsaCommon\Enum\MotTestTypeCode;
use DvsaCommon\Obfuscate\EncryptionKey;
use DvsaCommon\Obfuscate\ParamEncoder;
use DvsaCommon\Obfuscate\ParamEncrypter;
use DvsaCommon\Obfuscate\ParamObfuscator;
use DvsaCommon\Utility\DtoHydrator;
use \DvsaCommon\HttpRestJson\Client;
use DvsaCommonTest\TestUtils\XMock;
use Exception;
use PHPUnit_Framework_MockObject_MockObject;
use DvsaMotTest\Controller\RefuseToTestController;
use Dvsa\Mot\ApiClient\Service\VehicleService;
use Dvsa\Mot\Frontend\Test\StubIdentityAdapter;
use PHPUnit_Framework_MockObject_MockObject as MockObj;

class RefuseToTestControllerTest extends AbstractDvsaMotTestTestCase
{
    /** @var VehicleMapper|MockObj */
    private $mockVehicleMapper;

    private $vehicleService;

    private $restClient;

    private $authorisationService;

    private $catalogService;

    private $session;

    private $identityProvider;


    protected function setUp()
    {

        $this->vehicleService = new VehicleService('to be token');
        $this->restClient = XMock::of(Client::class);
        $this->authorisationService = XMock::of(MotFrontendAuthorisationServiceInterface::class);
        $this->catalogService = XMock::of(CatalogService::class);
        $this->session = XMock::of(\Zend\Session\Container::class);
        $this->identityProvider = XMock::of(MotFrontendIdentityProviderInterface::class);

        $this->setController(
            new RefuseToTestController(
                $this->createParamObfuscator(),
                $this->restClient,
                $this->vehicleService,
                $this->authorisationService,
                $this->catalogService,
                $this->session,
                $this->identityProvider
            )
        );

        parent::setUp();
    }

    public function testRefuseToTestSummaryActionWillRedirectForAuthenticatedRequest()
    {
        $identity = $this->getCurrentIdentity();
        $identity->setCurrentVts($this->getVtsData());

        $paramObfuscator = $this->createParamObfuscator();
        $vehicleId = 1;
        $obfuscatedVehicleId = $paramObfuscator->obfuscateEntry(ParamObfuscator::ENTRY_VEHICLE_ID, $vehicleId);
        $response = $this->getResponseForAction('refuseToTestSummary', ['id' => $obfuscatedVehicleId]);

        $this->assertEquals(302, $response->getStatusCode());
    }

    public function testRefuseToTestReasonActionWithPostAndRestException()
    {
        $this->markTestSkipped('BL-1164 is parked to investigate lifting vehicle\'s entity relationship. Talk to Ali');
        $this->mockAuthServiceAsserts();
        $this->getRestClientMockThrowingException('post', 'Some Error');

        $paramObfuscator = $this->createParamObfuscator();
        $vehicleId = 1;
        $obfuscatedVehicleId = $paramObfuscator->obfuscateEntry(ParamObfuscator::ENTRY_VEHICLE_ID, $vehicleId);

        $this->setPostAndPostParams(['refusal' => '1']);
        $this->getResultForAction(
            'refuseToTestReason',
            ['id' => $obfuscatedVehicleId, 'testTypeCode' => MotTestTypeCode::NORMAL_TEST]
        );

        $this->assertResponseStatus(self::HTTP_OK_CODE);
    }

    public function testRefuseToTestSummaryActionWithDocumentInSessionForAuthenticatedRequest()
    {
        $identity = new Identity();
        $identity->setCurrentVts($this->getVtsData());

        $result = [
            'data' => [
                'documentId' => 1,
                'documentName' => 'Foo.pdf',
            ],
        ];
        $paramObfuscator = $this->createParamObfuscator();
        $vehicleId = 1;
        $obfuscatedVehicleId = $paramObfuscator->obfuscateEntry(ParamObfuscator::ENTRY_VEHICLE_ID, $vehicleId);

        $this->session->expects($this->once())
            ->method('offsetGet')
            ->with('mot-test-refusal-'.$obfuscatedVehicleId)
            ->will($this->returnValue($result));



        $response = $this->getResponseForAction('refuseToTestSummary', ['id' => $obfuscatedVehicleId]);

        $this->assertResponseStatus(self::HTTP_OK_CODE, $response);
    }

    public function testRefuseToTestPrintActionWillRedirectForAuthenticatedRequest()
    {
        $identity = $this->getCurrentIdentity();
        $identity->setCurrentVts($this->getVtsData());

        $response = $this->getResponseForAction('refuseToTestPrint', ['id' => '1']);

        $this->assertResponseStatus(self::HTTP_REDIRECT_CODE, $response);
    }

    public function testRefuseToTestPrintActionWithDocumentInSessionForAuthenticatedRequest()
    {
        $identity = new Identity();
        $identity->setCurrentVts($this->getVtsData());

        $result = [
            'data' => [
                'documentId' => 1,
            ],
        ];
        $this->session->expects($this->once())
            ->method('offsetGet')
            ->with('mot-test-refusal-1')
            ->will($this->returnValue($result));

        $response = $this->getResponseForAction('refuseToTestPrint', ['id' => '1']);

        $this->assertResponseStatus(self::HTTP_OK_CODE, $response);
    }

    /**
     * @param array $params
     * @param bool  $asDto
     *
     * @return array|mixed
     */
    protected function getTestVehicleResult(array $params = [], $asDto = false)
    {
        $motTest = $this->jsonFixture('vehicle', __DIR__);

        $result = array_replace_recursive($motTest['data'], $params);

        if ($asDto) {
            return (new DtoHydrator())->doHydration($result);
        }

        return ['data' => $result];
    }

    /**
     * @return array
     */
    protected function getRetestEligibilityResult()
    {
        return [
            'data' => [
                'checkResult' => [0],
            ],
        ];
    }

    /**
     * @return array
     */
    protected function getCertificateExpiryResult()
    {
        return [
            'data' => [
                'checkResult' => [
                    'previousCertificateExists' => true,
                    'expiryDate' => '2014-05-10',
                ],
            ],
        ];
    }

    /**
     * @param int $motTestNumber
     *
     * @return array
     */
    protected function getSuccessfulMotTestPostResult($motTestNumber = 1)
    {
        return ['data' => ['motTestNumber' => $motTestNumber]];
    }

    /**
     * @return VehicleTestingStation
     */
    protected function getVtsData()
    {
        return StubIdentityAdapter::createStubVts();
    }

    /**
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    private function getMapperFactoryMock()
    {
        $factoryMapper = XMock::of(MapperFactory::class);
        $this->mockVehicleMapper = XMock::of(VehicleMapper::class);

        $map = [
            ['Vehicle', $this->mockVehicleMapper],
        ];

        $factoryMapper->expects($this->any())
            ->method('__get')
            ->will($this->returnValueMap($map));

        return $factoryMapper;
    }

    /**
     * @throws Exception
     */
    private function mockAuthServiceAsserts()
    {
        $authService = XMock::of(MotFrontendAuthorisationServiceInterface::class);
        $authService->expects($this->any())->method('isGranted')->willReturn(true);
        $authService->expects($this->any())->method('isGrantedAtSite')->willReturn(true);
        $this->getServiceManager()->setService('AuthorisationService', $authService);
    }

    /**
     * @return ParamObfuscator
     */
    protected function createParamObfuscator()
    {
        $config = ['security' => ['obfuscate' => ['key' => 'ggg', 'entries' => ['vehicleId' => true]]]];
        $paramEncrypter = new ParamEncrypter(new EncryptionKey($config['security']['obfuscate']['key']));
        $paramEncoder = new ParamEncoder();

        return new ParamObfuscator($paramEncrypter, $paramEncoder, $config);
    }
}
