<?php
namespace DvsaMotTestTest\Service;

use Application\Service\CatalogService;
use Application\Service\ContingencySessionManager;
use DvsaCommon\Dto\Common\MotTestDto;
use DvsaCommon\HttpRestJson\Client;
use DvsaCommon\Obfuscate\ParamObfuscator;
use Core\Service\LazyMotFrontendAuthorisationService;
use DvsaCommonTest\TestUtils\XMock;
use DvsaMotTest\Controller\VehicleSearchController;
use DvsaMotTest\Model\VehicleSearchResult;
use DvsaMotTest\Service\VehicleSearchService;
use DvsaCommon\Dto\Vehicle\VehicleDto;
use DvsaMotTest\View\VehicleSearchResult\CertificateUrlTemplate;
use DvsaMotTest\View\VehicleSearchResult\MotTestUrlTemplate;
use DvsaMotTest\View\VehicleSearchResult\NonMotTestUrlTemplate;
use DvsaMotTest\View\VehicleSearchResult\TrainingTestUrlTemplate;
use Zend\Mvc\Controller\Plugin\Url;

/**
 * Class VehicleSearchService
 */
class VehicleSearchServiceTest extends \PHPUnit_Framework_TestCase
{
    /** @var  VehicleSearchService */
    private $service;

    /** @var Client */
    private $client;

    /** @var ParamObfuscator */
    private $paramObfuscator;

    /** @var ContingencySessionManager */
    private $contingencySessionManager;

    /** @var VehicleSearchResult */
    private $vehicleSearchResultModel;

    /** @var CatalogService */
    private $catalogService;

    /** @var LazyMotFrontendAuthorisationService */
    private $authorisationService;

    public function setUp()
    {
        $this->client = XMock::of(Client::class);
        $this->paramObfuscator = XMock::of(ParamObfuscator::class);
        $this->contingencySessionManager = XMock::of(ContingencySessionManager::class);
        $this->vehicleSearchResultModel = XMock::of(VehicleSearchResult::class);
        $this->catalogService = XMock::of(CatalogService::class);
        $this->authorisationService = XMock::of(LazyMotFrontendAuthorisationService::class);

        $this->service = new VehicleSearchService(
            $this->client,
            $this->paramObfuscator,
            $this->contingencySessionManager,
            $this->vehicleSearchResultModel,
            $this->catalogService,
            $this->authorisationService
        );
    }

    public function testGetSearchResultMessageWithDifferentParametersReturnDifferentMessages()
    {
        // No VIN or VRM
        $searchResultMessage = $this->service->getSearchResultMessage(null, null, null);
        $this->assertEquals(
            'Enter the registration mark and Vehicle Identification Number (VIN) to search for a vehicle.',
            $searchResultMessage->getAdditionalMessage()
        );

        // VRM but partial VIN
        $searchResultMessage = $this->service->getSearchResultMessage(1234, 123456, null);
        $this->assertEquals(
            'Check the vehicle details are correct and try again.',
            $searchResultMessage->getAdditionalMessage()
        );

        // VRM and FULL VIN
        $searchResultMessage = $this->service->getSearchResultMessage(1234, 123456789, null);
        $this->assertEquals(
            'Only enter the last 6 digits of the VIN if you want to search for a partial match.',
            $searchResultMessage->getAdditionalMessage()
        );

        // VRM and no VIN
        $searchResultMessage = $this->service->getSearchResultMessage(1234, null, null);
        $this->assertEquals(
            'You must enter the VIN if the vehicle has one.',
            $searchResultMessage->getAdditionalMessage()
        );

        // VRM and no VIN
        $searchResultMessage = $this->service->getSearchResultMessage(null, 123456, null);
        $this->assertEquals(
            'You must enter the registration mark if the vehicle has one.',
            $searchResultMessage->getAdditionalMessage()
        );

        // VRM and no VIN
        $searchResultMessage = $this->service->getSearchResultMessage(null, 12345678, null);
        $this->assertEquals(
            'You must enter the registration mark if the vehicle has one. Only enter the last 6 digits of the VIN if you want to search for a partial match.',
            $searchResultMessage->getAdditionalMessage()
        );
    }

    public function testGetVehicleFromMotTestCertificateWithEmptyReturnsFalse()
    {
        $getVehicleWithNull = $this->service->getVehicleFromMotTestCertificate(null);
        $this->assertFalse($getVehicleWithNull);
    }

    public function testGetVehicleFromMotTestCertificateWithNumberFoundReturnsVehicleDto()
    {
        $this->setClientReponse($this->getMotDto());

        $getVehicle = $this->service->getVehicleFromMotTestCertificate(1);
        $this->assertInstanceOf(VehicleDto::class, $getVehicle);
    }

    public function testAreSlotsNeededNotTesterReturnFalse()
    {
        $this->setAuthorisationServiceResponse('isTester', false);

        $response = $this->service->areSlotsNeeded(VehicleSearchService::SEARCH_TYPE_TRAINING);
        $this->assertFalse($response);
    }

    public function testAreSlotsNeededForSearchTypeCertificateReturnFalse()
    {
        $this->setAuthorisationServiceResponse('isTester', true);

        $response = $this->service->areSlotsNeeded(VehicleSearchService::SEARCH_TYPE_CERTIFICATE);
        $this->assertFalse($response);
    }

    public function testAreSlotsNeededForSearchTypeTrainingReturnFalse()
    {
        $this->setAuthorisationServiceResponse('isTester', true);

        $response = $this->service->areSlotsNeeded(VehicleSearchService::SEARCH_TYPE_TRAINING);
        $this->assertFalse($response);
    }

    public function testAreSlotsNeededForTesterAndNotTrainingOrCertificateSearchReturnFalse()
    {
        $this->setAuthorisationServiceResponse('isTester', false);

        $response = $this->service->areSlotsNeeded(VehicleSearchService::SEARCH_TYPE_STANDARD);
        $this->assertFalse($response);
    }

    public function testVehicleSearchTypeReturnsSearchType()
    {
        $this->service->setSearchType(VehicleSearchService::SEARCH_TYPE_CERTIFICATE);

        $result = $this->service->getSearchType();

        $this->assertEquals(VehicleSearchService::SEARCH_TYPE_CERTIFICATE, $result);
    }

    public function urlTemplateMappingDataProvider()
    {
        return [
            [VehicleSearchService::SEARCH_TYPE_CERTIFICATE, CertificateUrlTemplate::class],
            [VehicleSearchService::SEARCH_TYPE_TRAINING, TrainingTestUrlTemplate::class],
            [VehicleSearchService::SEARCH_TYPE_STANDARD, MotTestUrlTemplate::class],
            [VehicleSearchService::SEARCH_TYPE_NON_MOT, NonMotTestUrlTemplate::class]
        ];
    }

    /**
     * @dataProvider urlTemplateMappingDataProvider
     */
    public function testUrlTemplateMapping($searchType, $urlTemplateClass)
    {
        $this->service->setSearchType($searchType);

        $urlTemplate = $this->service->getUrlTemplate('', 1, XMock::of(Url::class));

        $this->assertInstanceOf($urlTemplateClass, $urlTemplate);
    }

    private function setAuthorisationServiceResponse($method = 'isTester', $result)
    {
        $this->authorisationService->expects($this->any())
            ->method($method)
            ->willReturn($result);
    }

    private function setClientReponse($result)
    {
        $this->client->expects($this->any())
            ->method('get')
            ->willReturn($result);
    }

    private function getMotDto()
    {
        $object = (new MotTestDto())->setId(1)
                                    ->setVehicle(
                                        (new VehicleDto())->setId(1)
                                    );

        return [
            'data' => $object
        ];
    }

}
