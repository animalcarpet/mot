<?php
namespace DvsaMotTest\Model;

use Application\Service\CatalogService;
use DvsaApplicationLogger\Log\Logger;
use DvsaCommon\Obfuscate\ParamObfuscator;
use DvsaCommonTest\Bootstrap;
use DvsaCommonTest\TestUtils\XMock;
use DvsaMotTest\Constants\VehicleSearchSource;
use DvsaMotTest\Model\VehicleSearchResult;
use PHPUnit_Framework_TestCase;
use Tree\Fixture\Transport\Vehicle;

/**
 * Class VehicleSearchResultTest
 *
 * @package DvsaMotTest\Model
 */
class VehicleSearchResultTest extends PHPUnit_Framework_TestCase
{

    /** @var VehicleSearchResult */
    private $vehicleSearchResultModel;

    /** @var CatalogService */
    private $catalogService;

    public function setUp()
    {
        $serviceManager = Bootstrap::getServiceManager();
        $serviceManager->setAllowOverride(true);

        $paramObfuscator = XMock::of(ParamObfuscator::class);
        $paramObfuscator->expects($this->any())
                        ->method('deobfuscateEntry')
                        ->willReturn('123obfuscated123');

        $this->catalogService = XMock::of(CatalogService::class);

        $serviceManager->setService(ParamObfuscator::class, $paramObfuscator);

        $logger = $this
            ->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceManager->setService('Application\Logger', $logger);

        $this->vehicleSearchResultModel = new VehicleSearchResult(
            $paramObfuscator,
            new VehicleSearchSource(),
            $logger
        );
    }

    public function testMultipleResultsFoundInExpectedFormatReturnsModelWithResults()
    {
        $results = $this->getDemoApiResults();

        $vehicleSearchModel = $this->vehicleSearchResultModel;
        $vehicleSearchModel->addResults($results['data']['vehicles']);

        $this->assertEquals(3, $vehicleSearchModel->getResultsCount());

        $i = 0;

        /** @var VehicleSearchResult $result */
        foreach ($vehicleSearchModel->getResults() as $result) {
            $apiVehicles = $results['data']['vehicles'];

            $this->assertInstanceOf(VehicleSearchResult::class, $result);
            $this->assertEquals($result->getId(), $apiVehicles[$i]['id']);
            $this->assertEquals($result->getRegistrationNumber(), $apiVehicles[$i]['registration']);
            $this->assertEquals($result->getVin(), $apiVehicles[$i]['vin']);
            $this->assertEquals($result->getMake(), $apiVehicles[$i]['make']);
            $this->assertEquals($result->getModel(), $apiVehicles[$i]['model']);
            $this->assertEquals($result->getMakeAndModel(), $apiVehicles[$i]['make'] . ' ' . $apiVehicles[$i]['model']);
            $this->assertEquals($result->getMotTestCount(), $apiVehicles[$i]['total_mot_tests']);

            if ($apiVehicles[$i]['total_mot_tests'] == 0) {
                $this->assertFalse($result->hasMotTests());
            } else {
                $this->assertTrue($result->hasMotTests());
            }

            $this->assertEquals($result->getLastMotTestDate(), $apiVehicles[$i]['mot_completed_date']);
            $this->assertEquals($result->isDvlaVehicle(), $apiVehicles[$i]['isDvla']);
            $this->assertEquals($result->getModelDetail(), $apiVehicles[$i]['modelDetail']);
            $this->assertEquals($result->getFuelType(), $apiVehicles[$i]['fuelType']['name']);
            $this->assertFalse($result->getRetestEligibility());
            $this->assertFalse($result->isRetest());
            $this->assertTrue($result->isNormalTest());
            $this->assertEquals($result->isIncognito(), $apiVehicles[$i]['isIncognito']);

            if ($result->isDvlaVehicle()) {
                $this->assertEquals($result->getSource(), VehicleSearchSource::DVLA);
            } else {
                $this->assertEquals($result->getSource(), VehicleSearchSource::VTR);
            }

            $i++;
        }
    }

    public function testNoResultsFoundWithNoFormatReturnsModelWithNoResults()
    {
        $vehicleSearchModel = $this->vehicleSearchResultModel;
        $vehicleSearchModel->addResults([]);

        $this->assertEquals(0, $vehicleSearchModel->getResultsCount());
    }

    public function testRetestEligibilitySetToTrueAvailableAsPartOfResults()
    {
        $results = $this->getDemoApiResults();

        foreach ($results['data']['vehicles'] as &$result) {
            $result['retest_eligibility'] = true;
        }

        $vehicleSearchModel = $this->vehicleSearchResultModel;
        $vehicleSearchModel->addResults($results['data']['vehicles']);

        $this->assertEquals(3, $vehicleSearchModel->getResultsCount());

        $i = 0;

        /** @var VehicleSearchResult $result */
        foreach ($vehicleSearchModel->getResults() as $result) {
            $this->assertInstanceOf(VehicleSearchResult::class, $result);
            $this->assertTrue($result->isRetest());
            $this->assertFalse($result->isNormalTest());

            $i++;
        }
    }

    public function testRetestEligibilitySetToFalseAvailableAsPartOfResults()
    {
        $results = $this->getDemoApiResults();

        foreach ($results['data']['vehicles'] as &$result) {
            $result['retest_eligibility'] = false;
        }

        $vehicleSearchModel = $this->vehicleSearchResultModel;
        $vehicleSearchModel->addResults($results['data']['vehicles']);

        $this->assertEquals(3, $vehicleSearchModel->getResultsCount());

        $i = 0;

        /** @var VehicleSearchResult $result */
        foreach ($vehicleSearchModel->getResults() as $result) {
            $this->assertInstanceOf(VehicleSearchResult::class, $result);
            $this->assertFalse($result->isRetest());
            $this->assertTrue($result->isNormalTest());

            $i++;
        }
    }

    private function getDemoApiResults()
    {
        $vehicleOne = [
            'id' => 2032,
            'registration' => null,
            'emptyRegistrationReason' => 'MISS',
            'vin' => 'N0989080980JOTSNA',
            'emptyVinReason' => null,
            'year' => 2004,
            'firstUsedDate' => '2004-01-02',
            'cylinderCapacity' => 1700,
            'make' => 'CH RACING',
            'model' => 'WXE50',
            'modelDetail' => null,
            'vehicleClass' => '4',
            'primaryColour' =>
                [
                    'id' => 2,
                    'name' => 'Black',
                ],
            'secondaryColour' =>
                [
                    'id' => 2,
                    'name' => 'Black',
                ],
            'fuelType' =>
                [
                    'id' => 1,
                    'name' => 'Petrol',
                ],
            'bodyType' => '2 Door Saloon',
            'transmissionType' => 'Manual',
            'weight' => null,
            'isDvla' => false,
            'creationDate' => '2015-04-16',
            'mot_id' => null,
            'mot_completed_date' => null,
            'total_mot_tests' => '0',
            'isIncognito' => false
        ];

        $vehicleTwo = [
            'id' => 2032,
            'registration' => 'Y712GOO',
            'emptyRegistrationReason' => '',
            'vin' => 'N0989080980JOTSNA',
            'emptyVinReason' => null,
            'year' => 2004,
            'firstUsedDate' => '2015-01-02',
            'cylinderCapacity' => 1700,
            'make' => 'FORD',
            'model' => 'Mondeo',
            'modelDetail' => null,
            'vehicleClass' => '4',
            'primaryColour' =>
                [
                    'id' => 2,
                    'name' => 'Black',
                ],
            'secondaryColour' =>
                [
                    'id' => 2,
                    'name' => 'YELLOW',
                ],
            'fuelType' =>
                [
                    'id' => 1,
                    'name' => 'Petrol',
                ],
            'bodyType' => '2 Door Saloon',
            'transmissionType' => 'Manual',
            'weight' => null,
            'isDvla' => false,
            'creationDate' => '2015-04-16',
            'mot_id' => null,
            'mot_completed_date' => null,
            'total_mot_tests' => '0',
            'isIncognito' => true
        ];

        $vehicleThree = [
            'id' => 2032,
            'registration' => 'DD33DD',
            'emptyRegistrationReason' => '',
            'vin' => 'N0989080213123TSNA',
            'emptyVinReason' => null,
            'year' => 2004,
            'firstUsedDate' => '2004-03-02',
            'cylinderCapacity' => 1700,
            'make' => 'CH RACING',
            'model' => 'WXE50',
            'modelDetail' => null,
            'vehicleClass' => '4',
            'primaryColour' =>
                [
                    'id' => 2,
                    'name' => 'Black',
                ],
            'secondaryColour' =>
                [
                    'id' => 2,
                    'name' => 'Black',
                ],
            'fuelType' =>
                [
                    'id' => 1,
                    'name' => 'Petrol',
                ],
            'bodyType' => '2 Door Saloon',
            'transmissionType' => 'Manual',
            'weight' => null,
            'isDvla' => true,
            'creationDate' => '2015-04-16',
            'mot_id' => null,
            'mot_completed_date' => '2015-01-02',
            'total_mot_tests' => '8',
            'isIncognito' => null
        ];

        return [
            'data' => [
                'vehicles' => [
                    $vehicleOne,
                    $vehicleTwo,
                    $vehicleThree
                ]
            ]
        ];
    }

}
