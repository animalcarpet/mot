<?php

namespace VehicleTest\Helper;

use Application\Service\CatalogService;
use Core\ViewModel\Gds\Table\GdsTable;
use Core\ViewModel\Header\HeaderTertiaryList;
use Dvsa\Mot\ApiClient\Resource\Item\DvsaVehicle;
use DvsaCommon\Dto\Vehicle\VehicleExpiryDto;
use DvsaCommonTest\TestUtils\XMock;
use Vehicle\Controller\VehicleController;
use Vehicle\Helper\VehicleInformationTableBuilder;
use Vehicle\Helper\VehiclePageTitleBuilder;
use Vehicle\Helper\VehicleSidebarBuilder;
use Vehicle\Helper\VehicleViewModelBuilder;
use Vehicle\ViewModel\Sidebar\VehicleSidebar;
use Vehicle\ViewModel\VehicleViewModel;
use Zend\Stdlib\Parameters;
use Zend\View\Helper\Url;

class VehicleViewModelBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider dataProviderTestBackUrlGeneration
     */
    public function testBackLink($searchData, $urlToReturn)
    {
        $url = XMock::of(Url::class);
        $url->expects($this->at(0))->method('__invoke')->willReturnCallback(function ($route, $params, $params2) use ($urlToReturn) {
            $this->assertEquals($urlToReturn, $route);
        });

        $helper = new VehicleViewModelBuilder(
            $url,
            XMock::of(VehicleInformationTableBuilder::class),
            XMock::of(VehiclePageTitleBuilder::class),
            XMock::of(VehicleSidebarBuilder::class)
        );

        $searchData = new Parameters($searchData);
        $helper->setSearchData($searchData);
        $vm = $helper->getViewModel();
    }

    public function testBreadcrumbs()
    {
        $url = XMock::of(Url::class);
        $urlToReturn = '';
        $url->expects($this->at(1))->method('__invoke')->willReturnCallback(function ($route, $params, $params2) use ($urlToReturn) {
            $this->assertEquals('vehicle/search', $route);
        });

        $params = new Parameters([]);

        $helper = new VehicleViewModelBuilder(
            $url,
            XMock::of(VehicleInformationTableBuilder::class),
            XMock::of(VehiclePageTitleBuilder::class),
            XMock::of(VehicleSidebarBuilder::class)
        );

        $helper->setSearchData($params);

        $vm = $helper->getViewModel();
    }

    public function testViewModelGeneration(){
        $catalogService = XMock::of(CatalogService::class);
        $catalogService->expects($this->any())->method('getCountriesOfRegistrationByCode')->willReturn([]);

        $url = XMock::of(Url::class);
        $backUrl = 'backUrl';
        $url->expects($this->any())->method('__invoke')->willReturn($backUrl);

        /** @var VehicleInformationTableBuilder | \PHPUnit_Framework_MockObject_MockObject $vehicleInformationTableBuilder */
        $vehicleInformationTableBuilder = XMock::of(VehicleInformationTableBuilder::class);
        $vehicleInformationTableBuilder->expects($this->once())
            ->method('getVehicleSpecificationGdsTable')
            ->willReturn(new GdsTable());
        $vehicleInformationTableBuilder->expects($this->once())
            ->method('getVehicleRegistrationGdsTable')
            ->willReturn(new GdsTable());

        $helper = new VehicleViewModelBuilder(
            $url,
            $vehicleInformationTableBuilder,
            new VehiclePageTitleBuilder,
            new VehicleSidebarBuilder(XMock::of(Url::class))
        );

        $helper->setSearchData(new Parameters([]));
        $helper->setVehicle(new DvsaVehicle($this->getVehicle()));
        $helper->setExpiryDateInformation(new VehicleExpiryDto());
        $helper->setObfuscatedVehicleId('asdasd');

        $vm = $helper->getViewModel();
        $this->assertInstanceOf(VehicleViewModel::class, $vm);
        $this->assertEquals($backUrl, $vm->getBackUrl());
        $this->assertEquals('Return to vehicle information search', $vm->getBackLinkText());
        $this->assertNotEmpty($vm->getBreadcrumbs());
        $this->assertEquals('Vehicle', $vm->getPageSecondaryTitle());
        $this->assertEquals('Renault, Clio', $vm->getPageTitle());
        $this->assertInstanceOf(HeaderTertiaryList::class, $vm->getPageTertiaryTitle());
        $this->assertInstanceOf(VehicleSidebar::class, $vm->getSidebar());
        $this->assertInstanceOf(GdsTable::class, $vm->getVehicleRegistrationGdsTable());
        $this->assertInstanceOf(GdsTable::class, $vm->getVehicleSpecificationGdsTable());
    }

    public function dataProviderTestBackUrlGeneration()
    {
        return [
            [[VehicleController::PARAM_BACK_TO => VehicleController::BACK_TO_SEARCH], 'vehicle/search'],
            [[VehicleController::PARAM_BACK_TO => 'somethingUserEntered'], 'vehicle/search'],
            [[], 'vehicle/search'],
            [[VehicleController::PARAM_BACK_TO => VehicleController::BACK_TO_RESULT], 'vehicle/result'],
        ];
    }

    private function getVehicle()
    {
        return json_decode(json_encode([
            'id' => 1,
            'amendedOn' => '2016-09-07',
            'registration' => 'FNZ610',
            'vin' => '18M234WET2523',
            'emptyVrmReason' => NULL,
            'emptyVinReason' => NULL,
            'make' => [
                'id' => 5,
                'name' => 'Renault',
            ],
            'model' => [
                'id' => 6,
                'name' => 'Clio',
            ],
            'colour' => 'Grey',
            'colourSecondary' => 'Not Stated',
            'countryOfRegistration' => 'GB, UK, ENG, CYM, SCO (UK) - Great Britain',
            'fuelType' => 'Petrol',
            'vehicleClass' => '4',
            'bodyType' => '2 Door Saloon',
            'cylinderCapacity' => 1700,
            'transmissionType' => 'Automatic',
            'firstRegistrationDate' => '2004-01-02',
            'firstUsedDate' => '2004-01-02',
            'manufactureDate' => '2004-01-02',
            'isNewAtFirstReg' => false,
            'weight' => 12467,
            'version' => 2,
        ]));
    }
}