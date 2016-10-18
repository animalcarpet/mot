<?php

namespace VehicleApi\Service;

use DataCatalogApi\Service\VehicleCatalogService;
use Doctrine\ORM\EntityRepository;
use Dvsa\Mot\ApiClient\Request\CreateDvlaVehicleRequest;
use Dvsa\Mot\ApiClient\Request\CreateDvsaVehicleRequest;
use Dvsa\Mot\ApiClient\Resource\Item\DvlaVehicle as VehicleFromDvla;
use Dvsa\Mot\ApiClient\Resource\Item\DvsaVehicle;
use Dvsa\Mot\ApiClient\Service\VehicleService as NewVehicleService;
use DvsaAuthentication\Service\OtpService;
use DvsaAuthorisation\Service\AuthorisationServiceInterface;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Database\Transaction;
use DvsaCommon\Date\DateUtils;
use DvsaCommon\Dto\Vehicle\VehicleCreatedDto;
use DvsaCommon\Enum\MotTestTypeCode;
use DvsaCommon\Enum\WeightSourceCode;
use DvsaCommon\Model\FuelTypeAndCylinderCapacity;
use DvsaCommon\Obfuscate\ParamObfuscator;
use DvsaCommon\Utility\ArrayUtils;
use DvsaEntities\Entity\DvlaVehicle;
use DvsaEntities\Entity\DvlaVehicleImportChangeLog;
use DvsaEntities\Entity\ModelDetail;
use DvsaEntities\Entity\Person;
use DvsaEntities\Entity\Vehicle;
use DvsaEntities\Entity\VehicleClass;
use DvsaEntities\Entity\VehicleV5C;
use DvsaEntities\Repository\DvlaVehicleImportChangesRepository;
use DvsaEntities\Repository\DvlaVehicleRepository;
use DvsaEntities\Repository\PersonRepository;
use DvsaEntities\Repository\VehicleRepository;
use DvsaEntities\Repository\VehicleV5CRepository;
use DvsaMotApi\Service\CreateMotTestService;
use DvsaMotApi\Service\MotTestServiceProvider;
use DvsaMotApi\Service\Validator\VehicleValidator;
use VehicleApi\Service\Mapper\DvlaVehicleMapper;
use VehicleApi\Service\Mapper\VehicleMapper;

/**
 * Class VehicleService.
 */
class VehicleService
{
    const DEFAULT_COUNTRY_OF_REGISTRATION = 'GB';
    const DEFAULT_MAKE_MODEL_NAME = 'Unknown';
    const DEFAULT_BODY_TYPE_CODE = '0';
    const KEY_WIGHT = 'weight';
    const KEY_WIGHT_SOURCE_ID = 'weightSurce';

    /** @var  AuthorisationServiceInterface */
    private $authService;
    /** @var  VehicleRepository */
    private $vehicleRepository;
    /** @var  DvlaVehicleRepository */
    private $dvlaVehicleRepository;
    /** @var DvlaVehicleImportChangesRepository */
    private $dvlaVehicleImportChangesRepository;
    /** @var EntityRepository */
    private $dvlaMakeModelMapRepository;
    /** @var VehicleV5CRepository */
    private $vehicleV5CRepository;
    /** @var VehicleCatalogService */
    private $vehicleCatalog;
    /** @var  VehicleValidator */
    private $validator;
    /** @var  OtpService */
    private $otpService;

    private $motTestServiceProvider;

    private $identityProvider;

    private $personRepository;

    private $transaction;

    /**
     * @var NewVehicleService
     */
    private $newVehicleService;

    public function __construct(
        AuthorisationServiceInterface $authService,
        VehicleRepository $repository,
        VehicleV5CRepository $vehicleV5CRepository,

        DvlaVehicleRepository $dvlaVehicleRepository,
        DvlaVehicleImportChangesRepository $dvlaVehicleImportChangesRepository,
        EntityRepository $dvlaMakeModelMapRepository,
        VehicleCatalogService $vehicleCatalog,
        VehicleValidator $validator,
        OtpService $otpService,
        ParamObfuscator $paramObfuscator,
        MotTestServiceProvider $motTestServiceProvider,
        MotIdentityProviderInterface $identityProvider,
        PersonRepository $personRepository,
        Transaction $transaction,
        NewVehicleService $newVehicleService
    )
    {
        $this->authService = $authService;
        $this->vehicleRepository = $repository;
        $this->vehicleV5CRepository = $vehicleV5CRepository;
        $this->dvlaVehicleRepository = $dvlaVehicleRepository;
        $this->dvlaVehicleImportChangesRepository = $dvlaVehicleImportChangesRepository;
        $this->dvlaMakeModelMapRepository = $dvlaMakeModelMapRepository;
        $this->vehicleCatalog = $vehicleCatalog;
        $this->validator = $validator;
        $this->otpService = $otpService;
        $this->paramObfuscator = $paramObfuscator;
        $this->motTestServiceProvider = $motTestServiceProvider;
        $this->identityProvider = $identityProvider;
        $this->personRepository = $personRepository;
        $this->transaction = $transaction;
        $this->newVehicleService = $newVehicleService;
    }

    public function create($data)
    {
        $this->authService->assertGranted(PermissionInSystem::VEHICLE_CREATE);

        $this->validator->validate($data);

        if ($this->isPinRequired())
        {
            $token = ArrayUtils::tryGet($data, 'oneTimePassword');
            $this->otpService->authenticate($token);
        }

        $vehicle = $this->mapVehicle($data);

        $dvsaVehicleCreatedUsingJavaService = $this->createDvsaVehicleUsingJavaService($vehicle, $token);

        $this->transaction->begin();

        try {
            $data["fuelType"] = $data['fuelTypeCode'];
            $motTest = $this->startMotTest($data, $dvsaVehicleCreatedUsingJavaService->getId());

            $this->transaction->commit();
            $dto = new VehicleCreatedDto();
            $dto->setStartedMotTestNumber($motTest->getNumber());

            if (isset($data['returnOriginalId'])) {
                $dto->setVehicleId($dvsaVehicleCreatedUsingJavaService->getId());
            } else {
                $dto->setVehicleId(
                    $this->paramObfuscator->obfuscateEntry(ParamObfuscator::ENTRY_VEHICLE_ID, $dvsaVehicleCreatedUsingJavaService->getId())
                );
            }

            return $dto;
        } catch (\Exception $e) {
            $this->transaction->rollback();
            throw $e;
        }
    }

    /**
     * @param array $data
     * @param int $vehicleId
     * @return \DvsaEntities\Entity\MotTest
     */
    private function startMotTest(array $data, $vehicleId)
    {
        $motTestData = [];
        $motTestData[CreateMotTestService::FIELD_VEHICLE_ID] = $vehicleId;
        $motTestData[CreateMotTestService::FIELD_VTS_ID] = ArrayUtils::tryGet($data, 'vtsId');
        $motTestData[CreateMotTestService::FIELD_HAS_REGISTRATION] = true;
        $motTestData[CreateMotTestService::FIELD_COLOURS_PRIMARY] = ArrayUtils::tryGet($data, 'colour');
        $motTestData[CreateMotTestService::FIELD_COLOURS_SECONDARY] = ArrayUtils::tryGet($data, 'secondaryColour');
        $motTestData[CreateMotTestService::FIELD_VEHICLE_CLASS_CODE] = ArrayUtils::tryGet($data, 'testClass');
        $motTestData[CreateMotTestService::FIELD_MOT_TEST_TYPE] = MotTestTypeCode::NORMAL_TEST;
        $motTestData[CreateMotTestService::FIELD_FUEL_TYPE_CODE] = ArrayUtils::tryGet($data, 'fuelType');
        $motTestData[CreateMotTestService::FIELD_ONE_TIME_PASSWORD] = ArrayUtils::tryGet($data, 'oneTimePassword');
        $motTestData[CreateMotTestService::FIELD_CLIENT_IP] = ArrayUtils::tryGet($data, 'clientIp');

        // Contingency Data
        $motTestData[CreateMotTestService::FIELD_CONTINGENCY] = ArrayUtils::tryGet(
            $data,
            CreateMotTestService::FIELD_CONTINGENCY
        );

        $motTestData[CreateMotTestService::FIELD_CONTINGENCY_DTO] = ArrayUtils::tryGet(
            $data,
            CreateMotTestService::FIELD_CONTINGENCY_DTO
        );

        return $this->motTestServiceProvider->getService()->createMotTest($motTestData);
    }

    public function getVehicle($id)
    {
        $this->authService->assertGranted(PermissionInSystem::VEHICLE_READ);

        return $this->vehicleRepository->get($id);
    }

    private function getDvlaVehicle($id)
    {
        $this->authService->assertGranted(PermissionInSystem::VEHICLE_READ);

        return $this->dvlaVehicleRepository->get($id);
    }

    /**
     * @param $vehicleId
     *
     * @return \DvsaCommon\Dto\Vehicle\VehicleDto
     */
    public function getVehicleDto($vehicleId)
    {
        $v = $this->getVehicle($vehicleId);
        $m = (new VehicleMapper())->toDto($v)
            ->setId($vehicleId);

        return $m;
    }

    public function getDvlaVehicleData($vehicleId)
    {
        $dvlaVehicleMapper = new DvlaVehicleMapper($this->vehicleCatalog);

        return $dvlaVehicleMapper->toDto($this->getDvlaVehicle($vehicleId))
            ->setId($vehicleId);
    }

    /**
     * @param $dvlaVehicleId
     * @param $vehicleClassCode
     * @return DvsaVehicle
     */
    public function createVtrAndV5CFromDvlaVehicle($dvlaVehicleId, $vehicleClassCode)
    {
        $this->authService->assertGranted(PermissionInSystem::VEHICLE_READ);
        $this->authService->assertGranted(PermissionInSystem::VEHICLE_CREATE);

        $dvlaVehicle = $this->dvlaVehicleRepository->get($dvlaVehicleId);

        $vehicleCreatedUsingNewService = $this->createVehicleFromDvlaVehicleUsingJavaService(
            $dvlaVehicle,
            $vehicleClassCode
        );

        $dvlaVehicle->setVehicleId($vehicleCreatedUsingNewService->getId());

        $this->dvlaVehicleRepository->save($dvlaVehicle);

        $v5cDocumentNumber = $dvlaVehicle->getV5DocumentNumber();
        if (null !== $v5cDocumentNumber) {
            // VM-7220: Create corresponding V5C record.
            $vehicleV5C = (new VehicleV5C())
                ->setVehicleId($vehicleCreatedUsingNewService->getId())
                ->setV5cRef($dvlaVehicle->getV5DocumentNumber())
                ->setFirstSeen(new \DateTime());
            $this->vehicleV5CRepository->save($vehicleV5C);
        }

        return $vehicleCreatedUsingNewService;
    }

    /**
     * @param Person $person
     * @param VehicleFromDvla $vehicle
     * @param int $vehicleClassCode
     * @param int $primaryColourCode
     * @param int $secondaryColourCode
     * @param string $fuelTypeCode
     */
    public function logDvlaVehicleImportChanges(
        Person $person,
        VehicleFromDvla $vehicle,
        $vehicleClassCode,
        $primaryColourCode,
        $secondaryColourCode,
        $fuelTypeCode
    )
    {
        $vehicleClass = $this->vehicleCatalog->getVehicleClassByCode($vehicleClassCode);

        $importChanges = (new DvlaVehicleImportChangeLog())
            ->setTester($person)
            ->setVehicleId($vehicle->getId())
            ->setVehicleClass($vehicleClass)
            ->setColour($primaryColourCode)
            ->setSecondaryColour($secondaryColourCode)
            ->setFuelType($fuelTypeCode)
            ->setImported(new \DateTime());

        $this->dvlaVehicleImportChangesRepository->save($importChanges);
    }

    /**
     * @param array $data
     *
     * @return Vehicle
     */
    private function mapVehicle($data)
    {
        $vehDic = $this->vehicleCatalog;
        $model = $vehDic->findModel($data['make'], $data['model']);

        if (!ctype_digit($data['cylinderCapacity'])) {
            $data['cylinderCapacity'] = null;
        }


        $modelDetail = new ModelDetail();
        $modelDetail
            ->setCylinderCapacity($data['cylinderCapacity'])
            ->setFuelType($vehDic->getFuelType($data['fuelTypeCode']))
            ->setModel($model)
            ->setTransmissionType($vehDic->getTransmissionType($data['transmissionType']))
            ->setVehicleClass($vehDic->getVehicleClassByCode($data['testClass']));

        if (!empty($data['bodyType'])) {
            $modelDetail->setBodyType($vehDic->findBodyTypeByCode($data['bodyType']));
        }

        $vehicle = (new Vehicle())
            ->setVin($data['vin'])
            ->setRegistration($data['registrationNumber'])
            ->setColour($vehDic->getColourByCode($data['colour']))
            // next two fields MUST be set as they are not captured, this has
            // been done after P.O. (Simon Smith) consultation / discussion.
            ->setFirstUsedDate(DateUtils::toDate($data['dateOfFirstUse']))
            ->setManufactureDate(DateUtils::toDate($data['dateOfFirstUse']))
            ->setFirstRegistrationDate(DateUtils::toDate($data['dateOfFirstUse']))
            ->setCountryOfRegistration($vehDic->getCountryOfRegistration($data['countryOfRegistration'], true))
            ->setModelDetail($modelDetail);

        if (!empty($data['manufactureDate'])) {
            $vehicle->setManufactureDate(DateUtils::toDate($data['manufactureDate']));
        }
        if (!empty($data['firstRegistrationDate'])) {
            $vehicle->setFirstRegistrationDate(DateUtils::toDate($data['firstRegistrationDate']));
        }

        if (!empty($data['secondaryColour'])) {
            $vehicle->setSecondaryColour($vehDic->getColourByCode($data['secondaryColour'], true));
        }

        if (!empty($data['emptyVrmReason'])) {
            $vehicle->setEmptyVrmReason($this->vehicleCatalog->getEmptyVrmReasonByCode($data['emptyVrmReason']));
        }
        if (!empty($data['emptyVinReason'])) {
            $vehicle->setEmptyVinReason($this->vehicleCatalog->getEmptyVinReasonByCode($data['emptyVinReason']));
        }

        return $vehicle;
    }

    /**
     * @param DvlaVehicle $dvlaVehicle
     * @param $vehicleClassCode
     *
     * @return VehicleFromDvla
     */
    private function createVehicleFromDvlaVehicleUsingJavaService(
        DvlaVehicle $dvlaVehicle,
        $vehicleClassCode
    ) {
        $vehicleClass = $this->vehicleCatalog->getVehicleClassByCode($vehicleClassCode);

        $fuelType = $this->vehicleCatalog->findFuelTypeByPropulsionCode($dvlaVehicle->getFuelType());
        $fuelTypeCode = $fuelType ? $fuelType->getCode() : null;
        
        $bodyType = $this->vehicleCatalog->findBodyTypeByCode($dvlaVehicle->getBodyType());
        if (is_null($bodyType)) {
            $bodyType = $this->vehicleCatalog->findBodyTypeByCode(self::DEFAULT_BODY_TYPE_CODE);
        }

        $colourId = $this->vehicleCatalog->getColourByCode($dvlaVehicle->getPrimaryColour())->getId();

        $secondaryColourId = $dvlaVehicle->getSecondaryColour() ?
                $this->vehicleCatalog->getColourByCode($dvlaVehicle->getSecondaryColour())->getId() :
                null;

        $countryOfRegistrationId = $this->vehicleCatalog->getCountryOfRegistrationByCode(
            self::DEFAULT_COUNTRY_OF_REGISTRATION
        )->getId();

        $dvlaVehicleRequest = new CreateDvlaVehicleRequest();
        $dvlaVehicleRequest->setRegistration($dvlaVehicle->getRegistration())
            ->setId($dvlaVehicle->getId())
            ->setDvlaVehicleId($dvlaVehicle->getDvlaVehicleId())
            ->setV5cReference($dvlaVehicle->getV5DocumentNumber())
            ->setDvlaMakeCode($dvlaVehicle->getMakeCode())
            ->setDvlaModelCode($dvlaVehicle->getModelCode())
            ->setMakeInFull($dvlaVehicle->getMakeInFull())
            ->setBodyTypeId($bodyType->getId())
            ->setVehicleClassCode($vehicleClass->getCode())
            ->setFuelTypeCode($fuelTypeCode)
            ->setColourId($colourId)
            ->setSecondaryColourId($secondaryColourId)
            ->setCountryOfRegistrationId($countryOfRegistrationId)
            ->setFirstUsedDate($dvlaVehicle->getFirstUsedDate())
            ->setFirstRegistrationDate($dvlaVehicle->getFirstRegistrationDate())
            ->setIsNewAtFirstReg((bool)$dvlaVehicle->isVehicleNewAtFirstRegistration());

        if (FuelTypeAndCylinderCapacity::isCylinderCapacityCompulsoryForFuelTypeCode($fuelTypeCode)) {
            $dvlaVehicleRequest->setCylinderCapacity($dvlaVehicle->getCylinderCapacity());
        }

        if (!is_null($dvlaVehicle->getVin())) {
            $dvlaVehicleRequest->setVin($dvlaVehicle->getVin());
        }

        if (!is_null($dvlaVehicle->getManufactureDate())) {
            $dvlaVehicleRequest->setDateOfManufacture($dvlaVehicle->getManufactureDate());
        }

        $weightAndSource = $this->tryToGetWiethAndItsSource($dvlaVehicle, $vehicleClass);

        if (is_array($weightAndSource)) {
            $dvlaVehicleRequest->setWeight($weightAndSource[self::KEY_WIGHT]);
            $dvlaVehicleRequest->setWeightSourceId($weightAndSource[self::KEY_WIGHT_SOURCE_ID]);
        }

        $dvlaVehicle = $this->newVehicleService->createVehicleFromDvla($dvlaVehicleRequest);

        return $dvlaVehicle;
    }

    /**
     * @param Vehicle $dvsaVehicle
     * @param string $oneTimePassword
     *
     * @return DvsaVehicle
     */
    private function createDvsaVehicleUsingJavaService(
        Vehicle $dvsaVehicle,
        $oneTimePassword
    ) {
        $fuelTypeCode = $dvsaVehicle->getModelDetail()->getFuelType() ? $dvsaVehicle->getModelDetail()->getFuelType()->getCode() : null;

        $dvsaVehicleRequest = new CreateDvsaVehicleRequest();
        $dvsaVehicleRequest
            ->setOneTimePassword($oneTimePassword)
            ->setRegistration($dvsaVehicle->getRegistration())
            ->setVin($dvsaVehicle->getVin())
            ->setMakeId($dvsaVehicle->getModelDetail()->getModel()->getMake()->getId())
            ->setModelId($dvsaVehicle->getModelDetail()->getModel()->getId())
            ->setVehicleClassCode($dvsaVehicle->getModelDetail()->getVehicleClass()->getCode())
            ->setFuelTypeCode($fuelTypeCode)
            ->setTransmissionTypeId($dvsaVehicle->getModelDetail()->getTransmissionType()->getId())
            ->setColourId($dvsaVehicle->getColour()->getId())
            ->setSecondaryColourId($dvsaVehicle->getSecondaryColour()->getId())
            ->setCountryOfRegistrationId($dvsaVehicle->getCountryOfRegistration()->getId())
            ->setFirstUsedDate($dvsaVehicle->getFirstUsedDate());

        if($dvsaVehicle->getCylinderCapacity() != null) {
            $dvsaVehicleRequest->setCylinderCapacity($dvsaVehicle->getCylinderCapacity());
        } 

        $dvsaVehicle = $this->newVehicleService->createDvsaVehicle($dvsaVehicleRequest);

        return $dvsaVehicle;
    }


    /**
     * @param DvlaVehicle $dvlaVehicle
     * @param VehicleClass $vehicleClass
     * @return array|bool
     */
    private function tryToGetWiethAndItsSource(DvlaVehicle $dvlaVehicle, VehicleClass $vehicleClass)
    {
        $weightAndSource = false;

        if ($vehicleClass->getCode() === Vehicle::VEHICLE_CLASS_1 ||
            $vehicleClass->getCode() === Vehicle::VEHICLE_CLASS_2
        ) {
            // No weight expected to be carried forward for these vehicle classes.
            return $weightAndSource;
        }

        $massInServiceWeight = $dvlaVehicle->getMassInServiceWeight();

        if (!empty($massInServiceWeight)) {
            if ($vehicleClass->getCode() === Vehicle::VEHICLE_CLASS_3 ||
                $vehicleClass->getCode() === Vehicle::VEHICLE_CLASS_4
            ) {
                $weightAndSource = [
                    self::KEY_WIGHT => $massInServiceWeight,
                    self::KEY_WIGHT_SOURCE_ID => $this->vehicleCatalog->getWeightSourceByCode(WeightSourceCode::MISW)
                        ->getId(),
                ];
            } elseif ($vehicleClass->getCode() === Vehicle::VEHICLE_CLASS_5 ||
                $vehicleClass->getCode() === Vehicle::VEHICLE_CLASS_7
            ) {
                $weightAndSource = [
                    self::KEY_WIGHT => $massInServiceWeight,
                    self::KEY_WIGHT_SOURCE_ID => $this->vehicleCatalog->getWeightSourceByCode(WeightSourceCode::DGW)
                        ->getId(),
                ];
            }
        }

        return $weightAndSource;
    }

    /**
     * @param $dvlaVehicleId
     * @return int|null
     */
    public function getVehicleIdIfAlreadyImportedFromDvla($dvlaVehicleId)
    {
        return $this->dvlaVehicleRepository->findMatchingDvsaVehicleIdForDvlaVehicle($dvlaVehicleId);
    }

    private function isPinRequired()
    {
        if($this->authService->isGranted(PermissionInSystem::MOT_TEST_WITHOUT_OTP)) {
            return false;
        }

        if($this->identityProvider->getIdentity()->isSecondFactorRequired()) {
            return false;
        }

        return true;
    }
}
