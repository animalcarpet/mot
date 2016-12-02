<?php
namespace DvsaMotTestTest\Action;

use Core\Action\FlashMessage;
use Core\Action\NotFoundActionResult;
use Core\Action\RedirectToRoute;
use Core\Action\ViewActionResult;
use Core\Service\MotFrontendIdentityProviderInterface;
use Dvsa\Mot\ApiClient\Resource\Collection;
use Dvsa\Mot\ApiClient\Resource\Item\SearchVehicle;
use Dvsa\Mot\ApiClient\Service\VehicleService;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Dto\Vehicle\History\VehicleHistoryItemDto;
use DvsaCommon\Enum\MotTestStatusName;
use DvsaCommon\Exception\UnauthorisedException;
use DvsaCommon\HttpRestJson\Client;
use DvsaCommon\Utility\ArrayUtils;
use DvsaCommonTest\TestUtils\Auth\AuthorisationServiceMock;
use DvsaCommonTest\TestUtils\MethodSpy;
use DvsaCommonTest\TestUtils\XMock;
use DvsaMotTest\Action\VehicleCertificatesAction;
use DvsaMotTest\Flash\VehicleCertificateSearchFlashMessage;
use DvsaMotTest\ViewModel\MotTestCertificate\MotTestCertificateListViewModel;

class VehicleCertificatesActionTest extends \PHPUnit_Framework_TestCase
{
    /** @var VehicleCertificatesAction */
    private $action;

    private $vehicleService;

    /** @var MethodSpy */
    private $searchSpy;

    /** @var  Client| \PHPUnit_Framework_MockObject_MockObject */
    private $httpClient;

    /** @var MethodSpy */
    private $httpClientGetSpy;

    /** @var  AuthorisationServiceMock */
    private $authorisationService;

    /** @var  MotFrontendIdentityProviderInterface| \PHPUnit_Framework_MockObject_MockObject */
    private $motFrontendIdentityProviderInterface;

    private $vehicleId = 10853;
    private $vehicleMake = "Sabre";
    private $vehicleModel = "Turbo";
    private $vehicleVin = "12VIN21";
    private $vehicleVrm = "12REG56";
    private $certificateSiteName = "Site-name";
    private $certificateStatus = MotTestStatusName::PASSED;
    private $issuedDate = "2010-12-13";
    private $siteAddress = "London, Jump Street 12";
    private $testNumber = "984309183";
    private $expectedNumberOfVehicles = 2;
    private $expectedNumberOfCertificates = 2;

    protected function setUp()
    {
        $this->vehicleService = XMock::of(VehicleService::class);
        $this->httpClient = XMock::of(Client::class);
        $this->authorisationService = new AuthorisationServiceMock();
        $this->authorisationService->granted(PermissionInSystem::CERTIFICATE_SEARCH);
        $this->motFrontendIdentityProviderInterface = XMock::of(MotFrontendIdentityProviderInterface::class);

        $this->searchSpy = new MethodSpy($this->vehicleService, 'search');
        $this->httpClientGetSpy = new MethodSpy($this->httpClient, 'get');

        $this->action = new VehicleCertificatesAction($this->vehicleService, $this->httpClient, $this->authorisationService, $this->motFrontendIdentityProviderInterface);
    }

    /**
     * @dataProvider testListCertificatesProvider
     * @param $vrm
     * @param $vin
     * @param $cleanedVrm
     * @param $cleanedVin
     */
    public function testListCertificates($vrm, $vin, $cleanedVrm, $cleanedVin)
    {
        // SCENARIO Happy path when we search for a vehicle and get a couple of results viewed

        // GIVEN I want to search for a vehicle with correct parameters
        $vehicles = $this->prepareVehicles();

        $this->searchSpy->mock()->willReturn($vehicles);

        $certificateData = $this->preperaCertificateData();

        $this->httpClientGetSpy->mock()->willReturn($certificateData);

        // WHEN I enter the search page
        /** @var ViewActionResult $actionResult */
        $actionResult = $this->action->execute($vrm, $vin, []);

        // THEN a call to API is made for vehicles
        $this->assertEquals(1, $this->searchSpy->invocationCount(), "There was supposed to be one call to API for vehicle search");

        // with correct, cleaned parameters
        $this->assertSame($cleanedVrm, $this->searchSpy->paramsForLastInvocation()[0], "The search parameter VRM is incorrect");
        $this->assertSame($cleanedVin, $this->searchSpy->paramsForLastInvocation()[1], "The search parameter VIN is incorrect");

        // AND a call to API is made for certificates per each vehicle
        $this->assertEquals($this->expectedNumberOfCertificates, $this->httpClientGetSpy->invocationCount(), "We expect two calls to API for each of the 2 vehicle in test set ups");

        // AND the correct ID of a vehicle is used for the call
        $actualApiUrl = "vehicle/" . $this->vehicleId . "/test-history";
        $this->assertEquals($actualApiUrl, $this->httpClientGetSpy->paramsForInvocation(0)[0]->toString(), "We expect to call API for a specific vehicle");

        // AND the result of action is to view page
        $this->assertInstanceOf(ViewActionResult::class, $actionResult, "The happy path of this test case assumes the user will be shown a page, not redirected etc.");

        /** @var MotTestCertificateListViewModel $viewModel */
        $viewModel = $actionResult->getViewModel();

        // with correct view model
        $this->assertInstanceOf(MotTestCertificateListViewModel::class, $viewModel, "The view expects a certain view model class");

        // AND a properly mapped vehicle
        $this->assertVehicleCorrectness($viewModel);

        // AND correct certificate
        $this->assertCertificateCorrectness($viewModel);

        // AND layout template is set
        $this->assertEquals('layout/layout-govuk.phtml', $actionResult->layout()->getTemplate(), "Proper layout is selected");
        $this->assertEquals('MOT test certificates', $actionResult->layout()->getPageTitle(), "Check if page title is set");
        $this->assertEquals('Duplicate or replacement certificate', $actionResult->layout()->getPageSubTitle(), "Check if subtitle is set");
    }

    private function assertVehicleCorrectness(MotTestCertificateListViewModel $viewModel)
    {
        // with two vehicles
        $this->assertEquals($this->expectedNumberOfVehicles, $viewModel->getFoundVehiclesCount(), "Two vehicles returned from API shouls be mapped to two view tables");

        $vehicleTable = $viewModel->getTables()[0];
        // AND the vehicle has correct values
        $this->assertEquals($this->vehicleMake, $vehicleTable->getMake(), "Vehicle make needs to be mapped.");
        $this->assertEquals($this->vehicleModel, $vehicleTable->getModel(), "Vehicle model needs to be mapped.");
        $this->assertEquals($this->vehicleVin, $vehicleTable->getVin(), "Vehicle VIN needs to be mapped.");
        $this->assertEquals($this->vehicleVrm, $vehicleTable->getRegistration(), "Vehicle registration needs to be mapped.");
    }

    private function assertCertificateCorrectness(MotTestCertificateListViewModel $viewModel)
    {
        $vehicleTable = $viewModel->getTables()[0];

        // AND vehicle has proper number of certificates
        $this->assertEquals($this->expectedNumberOfVehicles, $vehicleTable->getTotalTestCount(), "As two certificates have been returned by API, the view should contain both");

        // AND certificates have correct values
        $certificate = $vehicleTable->getFirstTest();
        $this->assertEquals($this->certificateSiteName, $certificate->getSiteName(), "Site name is mapped");
        $this->assertEquals('Pass', $certificate->getStatus(), "Test status is mapped");
        $expectedDate = (new \DateTime($this->issuedDate))->format(VehicleHistoryItemDto::HISTORY_ITEM_DATE_FORMAT);
        $this->assertEquals($expectedDate, $certificate->getDateOfTest(), "Date of test is mapped");
        $this->assertEquals($this->siteAddress, $certificate->getSiteAddress(), "Address of test is mapped");
        $this->assertEquals($this->testNumber, $certificate->getTestNumber(), "Test number is mapped");
    }

    private function prepareVehicles()
    {
        $vehicle1Std = new \stdClass();
        $vehicle1Std->id = $this->vehicleId;
        $vehicle1Std->make = $this->vehicleMake;
        $vehicle1Std->model = $this->vehicleModel;
        $vehicle1Std->vin = $this->vehicleVin;
        $vehicle1Std->registration = $this->vehicleVrm;

        $vehicle2Std = new \stdClass();
        $vehicle2Std->id = null;
        $vehicle2Std->make = null;
        $vehicle2Std->model = null;
        $vehicle2Std->vin = null;
        $vehicle2Std->registration = null;

        return new Collection([$vehicle1Std, $vehicle2Std], SearchVehicle::class);
    }

    private function preperaCertificateData()
    {
        $certificate1Data = [
            'id'            => null,
            'status'        => $this->certificateStatus,
            'issuedDate'    => $this->issuedDate,
            'motTestNumber' => $this->testNumber,
            'testType'      => null,
            'allowEdit'     => null,
            'site'          => [
                'id'      => null,
                'name'    => $this->certificateSiteName,
                'address' => $this->siteAddress,
            ],
        ];

        $certificate2Data = [
            'id'            => null,
            'status'        => null,
            'issuedDate'    => null,
            'motTestNumber' => null,
            'testType'      => null,
            'allowEdit'     => null,
            'site'          => [
                'id'      => null,
                'name'    => null,
                'address' => null,
            ],
        ];

        $certificateData = ['data' => [$certificate1Data, $certificate2Data]];

        return $certificateData;
    }

    public function testListCertificatesProvider()
    {
        return [
            ['vrm' => '  12VRM34 ', 'vin' => '', 'cleanedVrm' => '12VRM34', 'cleanedVin' => null,],
            ['vrm' => '', 'vin' => ' 12VIN34  ', 'cleanedVrm' => null, 'cleanedVin' => '12VIN34',],
        ];
    }

    public function testGivingBothVinAndVrnReturnsPageNotFoundResult()
    {
        // SCENARIO Someone manually added both vin and reg into query params (we never have both);

        // GIVEN I provide both VIN and VRM
        $vin = "ANY";
        $vrm = "THING";

        // WHEN I search for vehicles
        $actionResult = $this->action->execute($vrm, $vin ,[]);

        // I get Page Not Found
        $this->assertInstanceOf(NotFoundActionResult::class, $actionResult);
    }

    public function testGivingNoVinOrVrnReturnsPageNotFoundResult()
    {
        // SCENARIO all the links that go to this page have either vin or vrm in query params

        // GIVEN I have no VIN and VRM provided
        $vin = "";
        $vrm = "";

        // WHEN I search for vehicles
        $actionResult = $this->action->execute($vrm, $vin, []);

        // I get Page Not Found
        $this->assertInstanceOf(NotFoundActionResult::class, $actionResult);
    }

    public function testNoResultsByVrmRedirectsBackToSearch()
    {
        // SCENARIO when we find no vehicles in search by VVRNIN we want to redirect back to the search by VIN form

        // GIVEN I use search params of unexisting vehicle
        $vrm = 'ABC';
        $emptySearchResult = new Collection([], SearchVehicle::class);
        $this->searchSpy->mock()->willReturn($emptySearchResult);

        // WHEN I search for vehicles
        /** @var RedirectToRoute $actionResult */
        $actionResult = $this->action->execute($vrm, '', []);

        // THEN I'm being redirected
        $this->assertInstanceOf(RedirectToRoute::class, $actionResult);

        // to search by VRM page
        $this->assertEquals('replacement-certificate-vehicle-search', $actionResult->getRouteName());

        // with search value is passed in query param
        $this->assertEquals($vrm, $actionResult->getQueryParams()['vrm']);

        // AND a flash message is set
        $this->assertTrue( ArrayUtils::anyMatch(
            $actionResult->getFlashMessages(),
            function (FlashMessage $message) {
                return $message->getContent() == VehicleCertificateSearchFlashMessage::NOT_FOUND;
            }
        ) , "It's expected that a flash message will be set");
    }

    public function testNoResultsByVinRedirectsBackToSearch()
    {
        // SCENARIO when we find no vehicles in search by VIN we want to redirect back to the search by VIN form

        // GIVEN I use search params of unexisting vehicle
        $vin = 'ABC';
        $emptySearchResult = new Collection([], SearchVehicle::class);
        $this->searchSpy->mock()->willReturn($emptySearchResult);

        // WHEN I search for vehicles
        /** @var RedirectToRoute $actionResult */
        $actionResult = $this->action->execute('', $vin, []);

        // THEN I'm being redirected
        $this->assertInstanceOf(RedirectToRoute::class, $actionResult);

        // to search by VIN page
        $this->assertEquals('replacement-certificate-vehicle-search-vin', $actionResult->getRouteName());

        // with search value is passed in query param
        $this->assertEquals($vin, $actionResult->getQueryParams()['vin']);

        // AND a flash message is set
        $this->assertTrue( ArrayUtils::anyMatch(
            $actionResult->getFlashMessages(),
            function (FlashMessage $message) {
                return $message->getContent() == VehicleCertificateSearchFlashMessage::NOT_FOUND;
            }
        ) , "It's expected that a flash message will be set");
    }

    /**
     * @expectedException \DvsaCommon\Exception\UnauthorisedException
     */
    public function testAccessDeniedWithoutPermissions()
    {
        // GIVEN I have no permission to search for certificate
        $this->authorisationService->clearAll();

        // WHEN I perform search
        $this->action->execute("ABC", "DEF", []);

        // THEN I get an authorisation exception
    }
}
