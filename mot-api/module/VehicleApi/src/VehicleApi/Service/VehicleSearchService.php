<?php

namespace VehicleApi\Service;

use DataCatalogApi\Service\VehicleCatalogService;
use DvsaAuthorisation\Service\AuthorisationServiceInterface;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Obfuscate\ParamObfuscator;
use DvsaCommon\Utility\ArrayUtils;
use DvsaEntities\DataConversion\AbstractStringConverter;
use DvsaEntities\DqlBuilder\SearchParam\VehicleSearchParam;
use DvsaEntities\Entity\MotTest;
use DvsaEntities\Entity\Vehicle;
use DvsaEntities\Repository\DvlaVehicleImportChangesRepository;
use DvsaEntities\Repository\DvlaVehicleRepository;
use DvsaEntities\Repository\MotTestRepository;
use DvsaEntities\Repository\VehicleRepository;
use DvsaMotApi\Helper\MysteryShopperHelper;
use DvsaMotApi\Service\TesterService;
use DvsaMotApi\Service\Validator\RetestEligibility\RetestEligibilityValidator;
use DvsaCommonApi\Service\Exception\BadRequestException;
use Dvsa\Mot\ApiClient\Service\VehicleService as NewVehicleService;

/**
 * Class VehicleService.
 */
class VehicleSearchService
{
    /** @var VehicleRepository */
    protected $vehicleRepository;
    /** @var DvlaVehicleRepository */
    protected $dvlaVehicleRepository;
    /** @var MotTestRepository */
    protected $motTestRepository;
    /** @var AuthorisationServiceInterface */
    protected $authService;
    /** @var \DvsaEntities\Repository\DvlaVehicleImportChangesRepository */
    private $dvlaVehicleImportChangesRepository;
    /** @var TesterService */
    private $testerService;
    /** @var VehicleCatalogService */
    private $vehicleCatalog;
    /** @var ParamObfuscator */
    private $paramObfuscator;
    /** @var RetestEligibilityValidator */
    private $retestEligibilityValidator;
    /** @var AbstractStringConverter */
    private $searchStringConverter;
    /** @var AbstractStringConverter */
    private $enforcementSearchStringConverter;
    /** @var NewVehicleService */
    private $vehicleService;
    /** @var MysteryShopperHelper */
    private $mysteryShopperHelper;

    /**
     * @param AuthorisationServiceInterface      $authService
     * @param VehicleRepository                  $vehicleRepository
     * @param DvlaVehicleRepository              $dvlaVehicleRepository
     * @param DvlaVehicleImportChangesRepository $dvlaVehicleImportChangesRepository
     * @param MotTestRepository                  $motTestRepository
     * @param TesterService                      $testerService
     * @param VehicleCatalogService              $vehicleCatalog
     * @param ParamObfuscator                    $paramObfuscator
     * @param RetestEligibilityValidator         $retestEligibilityValidator
     * @param AbstractStringConverter            $searchStringConverter
     * @param AbstractStringConverter            $enforcementSearchStringConverter
     * @param NewVehicleService                  $vehicleService
     * @param MysteryShopperHelper               $mysteryShopperHelper
     */
    public function __construct(
        AuthorisationServiceInterface $authService,
        VehicleRepository $vehicleRepository,
        DvlaVehicleRepository $dvlaVehicleRepository,
        DvlaVehicleImportChangesRepository $dvlaVehicleImportChangesRepository,
        MotTestRepository $motTestRepository,
        TesterService $testerService,
        VehicleCatalogService $vehicleCatalog,
        ParamObfuscator $paramObfuscator,
        RetestEligibilityValidator $retestEligibilityValidator,
        AbstractStringConverter $searchStringConverter,
        AbstractStringConverter $enforcementSearchStringConverter,
        NewVehicleService $vehicleService,
        MysteryShopperHelper $mysteryShopperHelper
    ) {
        $this->vehicleRepository = $vehicleRepository;
        $this->dvlaVehicleRepository = $dvlaVehicleRepository;
        $this->authService = $authService;
        $this->vehicleCatalog = $vehicleCatalog;
        $this->dvlaVehicleImportChangesRepository = $dvlaVehicleImportChangesRepository;
        $this->testerService = $testerService;
        $this->paramObfuscator = $paramObfuscator;
        $this->motTestRepository = $motTestRepository;
        $this->retestEligibilityValidator = $retestEligibilityValidator;
        $this->searchStringConverter = $searchStringConverter;
        $this->enforcementSearchStringConverter = $enforcementSearchStringConverter;
        $this->vehicleService = $vehicleService;
        $this->mysteryShopperHelper = $mysteryShopperHelper;
    }

    /**
     * @param string $vin        VIN number
     * @param string $reg        Registration number
     * @param bool   $isFullVin  Indicates whether passed VIN number is full
     * @param bool   $searchDvla True to search DVLA data source as well
     * @param int    $limit      Max records to search for in the query
     *
     * @return array
     */
    public function search($vin = null, $reg = null, $isFullVin = null, $searchDvla = null, $limit = null)
    {
        $exactMatch = false;
        $this->authService->assertGranted(PermissionInSystem::VEHICLE_READ);

        $vehicles = $this->searchAndExtractVehicle($vin, $reg, $isFullVin, $searchDvla, $limit);

        if (empty($vehicles)) {
            $vin = $this->searchStringConverter->convert($vin);
            $reg = $this->searchStringConverter->convert($reg);
            $vehicles = $this->searchAndExtractVehicle($vin, $reg, $isFullVin, $searchDvla, $limit);
        } else {
            $exactMatch = true;
        }

        if (empty($vehicles)) {
            $vehicles = [];
        }

        return [$vehicles, $exactMatch];
    }

    /**
     * @param VehicleSearchParam $searchParam
     *
     * @return mixed
     */
    public function searchVehicleWithAdditionalData(VehicleSearchParam $searchParam)
    {
        $searchParam->process();

        $vin = $this->enforcementSearchStringConverter->convert($searchParam->getVin());
        $reg = $this->enforcementSearchStringConverter->convert($searchParam->getRegistration());
        $vehicles = $this->vehicleRepository->search($vin, $reg);

        if ($vehicles) {
            $vehicles = $this->extractEnforcementVehicles($vehicles);
        } else {
            $vehicles = [];
        }

        $search['resultCount'] = count($vehicles);
        $search['totalResultCount'] = count($vehicles);
        $search['data'] = $vehicles;
        $search['searched'] = ['isElasticSearch' => false] + $searchParam->toArray();

        return $search;
    }

    /**
     * @param Vehicle[] $vehicles
     *
     * @return array
     */
    private function extractEnforcementVehicles($vehicles)
    {
        $results = [];
        foreach ($vehicles as $vehicle) {
            $results[$vehicle->getId()] = [
                'id' => $vehicle->getId(),
                'vin' => $vehicle->getVin(),
                'registration' => $vehicle->getRegistration(),
                'make' => $vehicle->getMakeName(),
                'model' => $vehicle->getModelName(),
                'displayDate' => $vehicle->getLastUpdatedOn() !== null ?
                    $vehicle->getLastUpdatedOn()->format('d M Y') :
                    null,
            ];
        }

        return $results;
    }

    public function searchVehicleWithMotData(
        $vin = null,
        $reg = null,
        $excludeDvla = null,
        $vtsId = false,
        $contingencyDto = null
    ) {
        $this->authService->assertGranted(PermissionInSystem::VEHICLE_READ);

        $vehicles = $this->searchAndExtractVehicle($vin, $reg, $excludeDvla);

        if (!empty($vehicles)) {
            $vehicles = $this->mergeMotDataToVehicles($vehicles, $vtsId, $contingencyDto);
        }

        return !empty($vehicles) ? $vehicles : [];
    }

    /**
     * @param string|null $vin
     * @param string|null $reg
     * @param bool|null   $excludeDvla
     *
     * @return array
     */
    private function searchAndExtractVehicle($vin = null, $reg = null, $excludeDvla = null)
    {
        $result = $this->vehicleService->tradeSearch($reg, $vin, $excludeDvla);

        return $result->getAll();
    }

    /**
     * @param array $vehicles
     * @param bool  $vtsId
     *
     * @return array
     */
    private function mergeMotDataToVehicles($vehicles, $vtsId = false, $contingencyDto = null)
    {
        foreach ($vehicles as &$vehicle) {
            $vehicle = (array) $vehicle->getData();
            $vehicle = $this->mergeMotDataToVehicle($vehicle, $vtsId, $contingencyDto);
        }

        $vehicleData = ArrayUtils::sortByDesc($vehicles, 'mot_completed_date');

        return $vehicleData;
    }

    /**
     * @param array $vehicle
     *
     * @return array
     */
    private function mergeMotDataToVehicle(array $vehicle, $vtsId = false, $contingencyDto = null)
    {
        $vehicle = array_merge($vehicle, [
            'mot_id' => '',
            'mot_completed_date' => '',
            'total_mot_tests' => '0',
        ]);

        if (!$vehicle['isDvla']) {
            $motTestData = $this->motTestRepository->findTestsForVehicle($vehicle['id'], null, $this->mysteryShopperHelper);
            if ($motTestData) {
                /** @var MotTest $latestMotTest */
                $latestMotTest = current($motTestData);
                $vehicle['mot_id'] = $latestMotTest->getNumber();
                $completedDate = $latestMotTest->getIssuedDate() ? $latestMotTest->getIssuedDate() : $latestMotTest->getStartedDate();
                $vehicle['mot_completed_date'] = $completedDate->format('Y-m-d');
                $vehicle['total_mot_tests'] = count($motTestData);
            }
        }

        if ($vtsId) {
            try {
                $this->retestEligibilityValidator->checkEligibilityForRetest($vehicle['id'], $vtsId, $contingencyDto);
                $vehicle['retest_eligibility'] = true;
            } catch (BadRequestException $exception) {
                $vehicle['retest_eligibility'] = false;
            }
        }

        return $vehicle;
    }
}
