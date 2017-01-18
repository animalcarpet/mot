<?php

namespace DvsaMotApi\Service;

use Doctrine\DBAL\Driver\Statement;
use Doctrine\ORM\EntityManager;
use DvsaAuthorisation\Service\AuthorisationServiceInterface;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Constants\ConfigParam;
use DvsaCommon\Date\DateTimeHolder;
use DvsaCommon\Date\DateUtils;
use DvsaCommon\Enum\AuthorisationForTestingMotStatusCode;
use DvsaCommon\Enum\VehicleClassGroupCode;
use DvsaCommonApi\Service\AbstractService;
use DvsaEntities\Repository\ConfigurationRepository;

/**
 * Class TesterExpiryService
 *
 * @package DvsaMotApi\Service
 */
class TesterExpiryService extends AbstractService
{
    const QUERY_UPDATE_STATUS_OF_INACTIVE_TESTERS
        = "
        update auth_for_testing_mot a
        set status_id = (select id from auth_for_testing_mot_status where code = :targetStatus)
        where status_id = (select id from auth_for_testing_mot_status where code = :fromStatus)
        and (select vcg.code
             from vehicle_class vc
             JOIN vehicle_class_group vcg ON (vc.vehicle_class_group_id = vcg.id)
             where vc.id = a.vehicle_class_id) = :vehicleClassGroup
        and  (select max(m.completed_date)
              from mot_test_current m
              LEFT JOIN vehicle ON (vehicle.id = m.vehicle_id) AND (vehicle.version = m.vehicle_version)
              LEFT JOIN vehicle_hist ON (vehicle_hist.id = m.vehicle_id) AND (vehicle_hist.version = m.vehicle_version)
              JOIN model_detail md ON md.id = COALESCE (vehicle.model_detail_id, vehicle_hist.model_detail_id)
              JOIN vehicle_class vc ON (md.vehicle_class_id = vc.id)
              JOIN mot_test_type tt ON (m.mot_test_type_id = tt.id)
              JOIN vehicle_class_group vcg ON (vc.vehicle_class_group_id = vcg.id)
              where
              tt.code not in ('DR', 'DT')
              and vcg.code = :vehicleClassGroup
              and m.person_id = a.person_id) <= :expiryDate";

    /**
     * @var \DvsaCommon\Date\DateTimeHolder
     */
    private $dateTime;

    /**
     * @var \DvsaAuthorisation\Service\AuthorisationServiceInterface $authService
     */
    protected $authService;

    /**
     * @var ConfigurationRepository
     */
    private $configurationRepository;

    public function __construct(
        EntityManager $entityManager,
        AuthorisationServiceInterface $authService,
        ConfigurationRepository $configurationRepository
    ) {
        parent::__construct($entityManager);
        $this->dateTime = new DateTimeHolder();
        $this->authService = $authService;
        $this->configurationRepository = $configurationRepository;
    }

    /**
     * This method changes the status of inactive testers.
     *
     */
    public function changeStatusOfInactiveTesters()
    {
        $this->authService->assertGranted(PermissionInSystem::TESTER_EXPIRY_JOB);

        $conn = $this->entityManager->getConnection();

        $today = $this->dateTime->getCurrentDate();

        $stmt = $conn->prepare(self::QUERY_UPDATE_STATUS_OF_INACTIVE_TESTERS);

        $this->processVehicleGroup($stmt, VehicleClassGroupCode::BIKES, $today);
        $this->processVehicleGroup($stmt, VehicleClassGroupCode::CARS_ETC, $today);

        $stmt->closeCursor();
        $conn->close();
    }

    /**
     * @param $stmt
     * @param $vehicleClassGroup
     * @param $today
     */
    protected function processVehicleGroup($stmt, $vehicleClassGroup, $today)
    {
        //1) Status reset from qualified to 'Demo required'.
        $this->executeUpdate(
            $stmt,
            AuthorisationForTestingMotStatusCode::QUALIFIED,
            AuthorisationForTestingMotStatusCode::DEMO_TEST_NEEDED,
            $vehicleClassGroup,
            $this->getDateLimitForDemoTestIsRequired($today)
        );

        //2) Status reset from 'Demo required' to 'refresher course required'.
        $this->executeUpdate(
            $stmt,
            AuthorisationForTestingMotStatusCode::DEMO_TEST_NEEDED,
            AuthorisationForTestingMotStatusCode::REFRESHER_NEEDED,
            $vehicleClassGroup,
            $this->getDateLimitForRefresherCourseRequired($today)
        );

        //3) Status reset from 'refresher course required' to 'initial training required'.
        $this->executeUpdate(
            $stmt,
            AuthorisationForTestingMotStatusCode::REFRESHER_NEEDED,
            AuthorisationForTestingMotStatusCode::INITIAL_TRAINING_NEEDED,
            $vehicleClassGroup,
            $this->getDateLimitForInitialCourseRequired($today)
        );
    }

    /**
     * @param $dateTime
     *
     * @return TesterExpiryService
     */
    public function setDateTime($dateTime)
    {
        $this->dateTime = $dateTime;

        return $this;
    }

    private function executeUpdate($stmt, $fromStatus, $targetStatus, $vehicleClassGroup, \DateTime $expiryDate)
    {
        /** @var Statement $stmt */
        $stmt->bindParam("fromStatus", $fromStatus);
        $stmt->bindParam("targetStatus", $targetStatus);
        $stmt->bindValue("vehicleClassGroup", $vehicleClassGroup);
        $stmt->bindValue("expiryDate", $expiryDate->format('Y-m-d'));
        $stmt->execute();
    }

    /**
     * Returns the date limit beyond which a demo test is required.
     *
     * @param \DateTime $date
     *
     * @return \DateTime
     */
    private function getDateLimitForDemoTestIsRequired(\DateTime $date)
    {
        $expiryDate = clone $date;
        $months = (int)$this->configurationRepository->getValue(ConfigParam::MONTHS_BEFORE_DEMO_TEST);

        return DateUtils::subtractCalendarMonths($expiryDate, $months);
    }

    /**
     * Returns the date limit beyond which a refresher course is required.
     *
     * @param \DateTime $date
     *
     * @return \DateTime
     */
    private function getDateLimitForRefresherCourseRequired(\DateTime $date)
    {
        $expiryDate = clone $date;
        $years = (int)$this->configurationRepository->getValue(ConfigParam::YEARS_BEFORE_REFRESHER_COURSE);

        return $expiryDate->sub(new \DateInterval('P' . $years . 'Y'));
    }

    /**
     * Returns the date limit beyond which an initial course is required.
     *
     * @param \DateTime $date
     *
     * @return \DateTime
     */
    private function getDateLimitForInitialCourseRequired(\DateTime $date)
    {
        $expiryDate = clone $date;
        $years = (int)$this->configurationRepository->getValue(ConfigParam::YEARS_BEFORE_INITIAL_COURSE);

        return $expiryDate->sub(new \DateInterval('P' . $years . 'Y'));
    }
}
