<?php

namespace DvsaMotApi\Service;

use Doctrine\ORM\EntityManager;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject;
use Dvsa\Mot\ApiClient\Resource\Item\DvlaVehicle;
use DvsaAuthentication\Service\OtpService;
use DvsaAuthorisation\Service\AuthorisationServiceInterface;
use DvsaCommon\Dto\MotTesting\ContingencyTestDto;
use DvsaCommon\Utility\ArrayUtils;
use DvsaCommonApi\Transaction\TransactionAwareInterface;
use DvsaCommonApi\Transaction\TransactionAwareTrait;
use DvsaEntities\Entity\MotTest;
use DvsaEntities\Entity\Person;
use DvsaEntities\Repository\MotTestRepository;
use DvsaMotApi\Service\Validator\MotTestValidator;
use DvsaMotApi\Service\Validator\RetestEligibility\RetestEligibilityValidator;
use OrganisationApi\Service\OrganisationService;
use VehicleApi\Service\VehicleService as VehicleApiVehicleService;
use DvsaMotApi\Controller\Validator\CreateMotTestRequestValidator;
use DvsaCommon\Enum\MotTestTypeCode;
use DvsaCommon\Utility\DtoHydrator;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use DvsaEntities\Repository\PersonRepository;
use DvsaCommon\Constants\Network;
use Dvsa\Mot\ApiClient\Service\VehicleService as NewVehicleService;

class CreateMotTestService implements TransactionAwareInterface
{
    use TransactionAwareTrait;

    const FIELD_VEHICLE_ID = 'vehicleId';
    const FIELD_DVLA_VEHICLE_ID = 'dvlaVehicleId';
    const FIELD_VTS_ID = 'vehicleTestingStationId';
    const FIELD_HAS_REGISTRATION = 'hasRegistration';
    const FIELD_MOT_TEST_TYPE = 'motTestType';
    const FIELD_MOT_TEST_NUMBER_ORIGINAL = 'motTestNumberOriginal';
    const FIELD_ODOMETER_READING = 'odometerReading';
    const FIELD_MOT_TEST_COMPLAINT_REF = 'complaintRef';
    const FIELD_COLOURS = 'colours';
    const FIELD_COLOURS_PRIMARY = 'primaryColour';
    const FIELD_COLOURS_SECONDARY = 'secondaryColour';
    const FIELD_FUEL_TYPE_ID = 'fuelTypeId';
    const FIELD_VEHICLE_CLASS_CODE = "vehicleClassCode";
    const FIELD_REASON_DIFFERENT_TESTER_CODE = 'differentTesterReasonCode';
    const FIELD_SITEID = 'siteid';
    const FIELD_LOCATION = 'location';
    const FIELD_FLAG_PRIVATE = 'flagPrivate';
    const FIELD_ONE_PERSON_TEST = 'onePersonTest';
    const FIELD_ONE_PERSON_RE_INSPECTION = 'onePersonReInspection';
    const FIELD_ONE_TIME_PASSWORD = 'oneTimePassword';
    const FIELD_CONTINGENCY = 'contingencyId';
    const FIELD_CONTINGENCY_DTO = 'contingencyDto';
    const FIELD_CLIENT_IP = 'clientIp';

    /** @var EntityManager  */
    private $entityManager;
    /** @var MotTestRepository */
    private $motTestRepository;
    /** @var MotTestValidator */
    private $motTestValidator;
    /** @var AuthorisationServiceInterface */
    private $authService;
    /** @var TesterService */
    private $testerService;
    /** @var RetestEligibilityValidator */
    private $retestEligibilityValidator;

    /** @var OtpService */
    private $otpService;

    /** @var OrganisationService $organisationService */
    private $organisationService;

    /** @var VehicleApiVehicleService */
    private $vehicleService;

    /** @var NewVehicleService */
    private $newVehicleService;

    /** @var MotIdentityProviderInterface  */
    private $identityProvider;

    /** @var PersonRepository  */
    private $personRepository;

    /**
     * @param MotIdentityProviderInterface $identityProvider
     * @param PersonRepository $personRepository
     */
    public function __construct(
        EntityManager $entityManager,
        MotTestValidator $motTestValidator,
        AuthorisationServiceInterface $authService,
        TesterService $testerService,
        RetestEligibilityValidator $retestEligibilityValidator,
        OtpService $otpService,
        OrganisationService $organisationService,
        VehicleApiVehicleService $vehicleService,
        MotIdentityProviderInterface $identityProvider,
        NewVehicleService $newVehicleService,
        PersonRepository $personRepository,
        MotTestRepository $motTestRepository
    )
    {
        $this->entityManager = $entityManager;
        $this->motTestValidator = $motTestValidator;
        $this->authService = $authService;
        $this->testerService = $testerService;
        $this->retestEligibilityValidator = $retestEligibilityValidator;
        $this->otpService = $otpService;
        $this->organisationService = $organisationService;
        $this->vehicleService = $vehicleService;
        $this->newVehicleService = $newVehicleService;
        $this->identityProvider = $identityProvider;
        $this->motTestRepository = $motTestRepository;
        $this->personRepository = $personRepository;
    }

    /**
     * @param array $data
     * @return MotTest
     */
    public function create(array $data)
    {
        CreateMotTestRequestValidator::validate($data);
        $userId = $this->identityProvider->getIdentity()->getUserId();
        $person = $this->personRepository->get($userId);

        $dvlaVehicleId = ArrayUtils::tryGet($data, self::FIELD_DVLA_VEHICLE_ID);
        $vehicleId     = ArrayUtils::tryGet($data, self::FIELD_VEHICLE_ID);

        // Unless a new siteid has been specified (for a reinspection) we want to maintain the old value...
        $vehicleTestingStationId = (int)$data[self::FIELD_VTS_ID];
        $primaryColour           = $data[self::FIELD_COLOURS_PRIMARY];
        $secondaryColour         = ArrayUtils::tryGet($data, self::FIELD_COLOURS_SECONDARY);
        $fuelTypeCode            = ArrayUtils::tryGet($data, self::FIELD_FUEL_TYPE_ID);
        $vehicleClassCode        = ArrayUtils::tryGet($data, self::FIELD_VEHICLE_CLASS_CODE);
        $hasRegistration         = ArrayUtils::tryGet($data, self::FIELD_HAS_REGISTRATION, false);
        $motTestNumberOriginal   = ArrayUtils::tryGet($data, self::FIELD_MOT_TEST_NUMBER_ORIGINAL);
        $complaintRef            = ArrayUtils::tryGet($data, self::FIELD_MOT_TEST_COMPLAINT_REF);
        $motTestTypeCode         = ArrayUtils::tryGet($data, self::FIELD_MOT_TEST_TYPE, MotTestTypeCode::NORMAL_TEST);
        $flagPrivate             = ArrayUtils::tryGet($data, self::FIELD_FLAG_PRIVATE, false);
        $oneTimePassword         = ArrayUtils::tryGet($data, self::FIELD_ONE_TIME_PASSWORD);
        $contingencyId           = ArrayUtils::tryGet($data, self::FIELD_CONTINGENCY);
        $contingencyDto          = ArrayUtils::tryGet($data, self::FIELD_CONTINGENCY_DTO);
        $clientIp                = ArrayUtils::tryGet($data, self::FIELD_CLIENT_IP, Network::DEFAULT_CLIENT_IP);

        if (!is_null($contingencyDto)) {
            $contingencyDto = DtoHydrator::jsonToDto($contingencyDto);
        }

        /** @var MotTest $motTest */
        $motTest = $this->save(
            $person,
            $vehicleId,
            $vehicleTestingStationId,
            $primaryColour,
            $secondaryColour,
            $fuelTypeCode,
            $vehicleClassCode,
            $hasRegistration,
            $dvlaVehicleId,
            $motTestTypeCode,
            $clientIp,
            $motTestNumberOriginal,
            $complaintRef,
            $flagPrivate,
            $oneTimePassword,
            $contingencyId,
            $contingencyDto
        );

        /*
         * Update the slot count in a separate transaction to fix VM-3254
         */
        $this->updateSlotCount($motTest);

        return $motTest;
    }

    /**
     * @param Person                     $person
     * @param int|null                   $vehicleId               if not given, $dvlaVehicleId required
     * @param int                        $vehicleTestingStationId
     * @param string                     $primaryColourCode
     * @param string                     $secondaryColourCode
     * @param string                     $fuelTypeCode
     * @param int                        $vehicleClassCode
     * @param bool                       $hasRegistration
     * @param int|null                   $dvlaVehicleId           if given, vehicle is created from DVLA data
     * @param string                     $motTestTypeCode
     * @param string                     $clientIp
     * @param string|null                $motTestNumberOriginal
     * @param int|null                   $complaintRef
     * @param bool                       $flagPrivate
     * @param string|null                $oneTimePassword
     * @param int|null                   $contingencyId
     * @param ContingencyTestDto|null $contingencyDto
     *
     * @return MotTest
     */
    private function save(
        Person $person,
        $vehicleId,
        $vehicleTestingStationId,
        $primaryColourCode,
        $secondaryColourCode,
        $fuelTypeCode,
        $vehicleClassCode,
        $hasRegistration,
        $dvlaVehicleId,
        $motTestTypeCode,
        $clientIp,
        $motTestNumberOriginal = null,
        $complaintRef = null,
        $flagPrivate = false,
        $oneTimePassword = null,
        $contingencyId = null,
        ContingencyTestDto $contingencyDto = null
    )
    {
        if ((int) $dvlaVehicleId > 0) {
            $vehicleId = $this->vehicleService->getVehicleIdIfAlreadyImportedFromDvla($dvlaVehicleId);
            if (!$vehicleId) {
                $vehicle = $this->vehicleService->createVtrAndV5CFromDvlaVehicle($dvlaVehicleId, $vehicleClassCode);

                if (!$vehicle instanceof DvlaVehicle) {
                    throw new \RuntimeException(
                        sprintf(
                            'We have failed to import a dvla vehicle, attempted id: %s and vehicle class code: %s',
                            $dvlaVehicleId,
                            $vehicleClassCode
                        )
                    );
                }

                $vehicleId = $vehicle->getId();

                $this->vehicleService->logDvlaVehicleImportChanges(
                    $person,
                    $vehicle,
                    $vehicleClassCode,
                    $primaryColourCode,
                    $secondaryColourCode,
                    $fuelTypeCode
                );
            }
        }

        if (!isset($vehicleId)) {
            throw new \RuntimeException('At this point we should have a vehicle id');
        }

        return $this->inTransaction(
            function () use (
                $person,
                $vehicleId,
                $vehicleTestingStationId,
                $primaryColourCode,
                $secondaryColourCode,
                $fuelTypeCode,
                $vehicleClassCode,
                $hasRegistration,
                $dvlaVehicleId,
                $motTestTypeCode,
                $motTestNumberOriginal,
                $complaintRef,
                $flagPrivate,
                $oneTimePassword,
                $contingencyId,
                $contingencyDto,
                $clientIp
            ) {
                $motTestCreationHelper = (new MotTestCreationHelper(
                    $this->entityManager,
                    $this->authService,
                    $this->testerService,
                    $this->motTestRepository,
                    $this->motTestValidator,
                    $this->retestEligibilityValidator,
                    $this->otpService,
                    $this->newVehicleService
                ));


                return $motTestCreationHelper->createMotTest(
                    $person,
                    $vehicleId,
                    $vehicleTestingStationId,
                    $primaryColourCode,
                    $secondaryColourCode,
                    $fuelTypeCode,
                    $vehicleClassCode,
                    $hasRegistration,
                    $motTestTypeCode,
                    $motTestNumberOriginal,
                    $complaintRef,
                    $flagPrivate,
                    $oneTimePassword,
                    $contingencyId,
                    $clientIp,
                    $contingencyDto
                );
            }
        );
    }

    /**
     * @param MotTest $motTest
     */
    private function updateSlotCount(MotTest $motTest)
    {
        if ($motTest->getMotTestType()->getIsSlotConsuming()) {
            $this->inTransaction(
                function () use ($motTest) {
                    $this->organisationService->decrementSlotBalance(
                        $motTest->getVehicleTestingStation()->getOrganisation()
                    );
                }
            );
        }
    }
}
