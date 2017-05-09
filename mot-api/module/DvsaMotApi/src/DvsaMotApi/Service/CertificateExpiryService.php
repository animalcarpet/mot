<?php

namespace DvsaMotApi\Service;

use DvsaAuthorisation\Service\AuthorisationServiceInterface;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Date\DateTimeApiFormat;
use DvsaCommon\Date\DateTimeHolder;
use DvsaCommonApi\Service\Exception\NotFoundException;
use DvsaEntities\Repository\ConfigurationRepository;
use DvsaEntities\Repository\MotTestRepository;
use DvsaEntities\Repository\VehicleRepository;

/**
 * Contains services related to certificate expiry feature.
 */
class CertificateExpiryService
{
    const CALENDAR_MONTHS_ALLOWED_TO_POST_DATE = 'maxCalendarMthsForPostdatingExpiryDte';
    const YEARS_BEFORE_FIRST_TEST_IS_DUE = 'yearsBeforeFirstMotTestIsDue';
    const YEARS_BEFORE_FIRST_TEST_IS_DUE_CLASS_5 = 'yearsBeforeFirstMotTestIsDueClass5';

    /** @var \DvsaCommon\Date\DateTimeHolder */
    private $dateTime;
    /** @var MotTestRepository $motTestRepository */
    private $motTestRepository;
    /** @var VehicleRepository $vehicleRepository */
    private $vehicleRepository;
    /** @var ConfigurationRepository */
    private $configurationRepository;
    /** @var AuthorisationServiceInterface $authService */
    private $authService;

    /**
     * @param DateTimeHolder          $dateTimeHolder
     * @param MotTestRepository       $motTestRepository
     * @param VehicleRepository       $vehicleRepository
     * @param ConfigurationRepository $configurationRepository
     */
    public function __construct(
        DateTimeHolder $dateTimeHolder,
        MotTestRepository $motTestRepository,
        VehicleRepository $vehicleRepository,
        ConfigurationRepository $configurationRepository,
        AuthorisationServiceInterface $authService
    ) {
        $this->dateTime = $dateTimeHolder;
        $this->motTestRepository = $motTestRepository;
        $this->vehicleRepository = $vehicleRepository;
        $this->configurationRepository = $configurationRepository;
        $this->authService = $authService;
    }

    /**
     * Method retrieves the previous certificate expiry data for a vehicle database ID.
     *
     * @param string              $vehicleId Database identifier of vehicle
     * @param bool                $isDvla    Search in the DVLA vehicle table if true or the standard one if false
     * @param DateTimeHolder|null $testDate  Date of the test
     *
     * @return array If the vehicle cannot be found
     *
     * @throws \Exception
     */
    public function getExpiryDetailsForVehicle($vehicleId, $isDvla = false, $testDate = null)
    {
        $this->authService->assertGranted(PermissionInSystem::TESTER_READ);

        $checkExpiryResults = [
            'previousCertificateExists' => true,
        ];

        if ($testDate === null) {
            $testDate = $this->dateTime->getCurrentDate();
        }

        if (true === $isDvla) {
            $expiryDate = null;
            $checkExpiryResults['previousCertificateExists'] = false;
            $earliestTestDateForPostdatingExpiryDate = null;
            $isEarlierThanTestDateLimit = false;
        } else {
            $expiryDate = $this->motTestRepository->findLastCertificateExpiryDate($vehicleId);

            if ($expiryDate === null) {
                // Use a notional expiry date based on the first use / manufacture date instead.
                $vehicle = $this->vehicleRepository->find($vehicleId);

                if (!$vehicle) {
                    throw new \Exception('Vehicle');
                }

                $expiryDate = MotTestDate::getNotionalExpiryDateForVehicle($vehicle);
                $checkExpiryResults['previousCertificateExists'] = false;
            }
            $earliestTestDateForPostdatingExpiryDate = MotTestDate::preservationDate($expiryDate);
            $isEarlierThanTestDateLimit = $testDate < $earliestTestDateForPostdatingExpiryDate;
        }

        $checkExpiryResults['expiryDate'] = DateTimeApiFormat::date($expiryDate);
        $checkExpiryResults['earliestTestDateForPostdatingExpiryDate']
            = DateTimeApiFormat::date($earliestTestDateForPostdatingExpiryDate);
        $checkExpiryResults['isEarlierThanTestDateLimit'] = $isEarlierThanTestDateLimit;

        return $checkExpiryResults;
    }

    /**
     * Returns the notional previous expiry date for a first certificate based on first use date.
     *
     * if it is not declared as new at first registration
     *     its first MOT test will be due 3 years less 1 day from the date of manufacture
     *
     * If a class 5 vehicle is declared as new at first registration
     *     its first MOT test will be due 1 year less 1 day from the date of registration,
     *
     * if it is not declared as new at first registration
     *     its first MOT test will be due 1 year less 1 day from the date of manufacture.
     *
     * @param $vehicleClass
     * @param $declaredAsNew
     * @param \DateTime $dateManufactured
     * @param \DateTime $dateRegistered
     *
     * @return \DateTime
     *
     * @throws NotFoundException
     */
    public function getInitialClassAwareExpiryDate(
        $vehicleClass,
        $declaredAsNew,
        \DateTime $dateManufactured,
        \DateTime $dateRegistered
    ) {
        $date = (true === $declaredAsNew)
            ? $dateRegistered
            : $dateManufactured;

        $key = (5 === (int) $vehicleClass)
            ? self::YEARS_BEFORE_FIRST_TEST_IS_DUE_CLASS_5
            : self::YEARS_BEFORE_FIRST_TEST_IS_DUE;

        $years = (int) $this->configurationRepository->getValue($key);
        $expiryDate = $date
            ->add(new \DateInterval('P'.$years.'Y'))
            ->sub(new \DateInterval('P1D'));

        return $expiryDate;
    }
}
