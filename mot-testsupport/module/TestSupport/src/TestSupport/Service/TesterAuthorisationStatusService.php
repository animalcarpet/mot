<?php

namespace TestSupport\Service;

use DvsaCommon\Enum\VehicleClassGroupCode;
use DvsaCommon\Model\VehicleClassGroup;
use TestSupport\Helper\TestDataResponseHelper;
use Zend\View\Model\JsonModel;
use TestSupport\Helper\TestSupportRestClientHelper;
use DvsaCommon\Enum\AuthorisationForTestingMotStatusCode;
use Doctrine\ORM\EntityManager;
use Doctrine\DBAL\Connection;

class TesterAuthorisationStatusService
{
    const DEFAULT_QUALIFICATION_STATUS = AuthorisationForTestingMotStatusCode::QUALIFIED;

    const CUSTOM_QUALIFICATIONS_KEY = 'qualifications';

    const RESULT_SUCCESS_KEY = 'success';

    const RESULT_FAILED_KEY = 'failed';

    /** @var EntityManager */
    protected $entityManager;

    /** @var array the default tester qualification status is set to qualified */
    private $defaultTestQualificationStatus = [
        VehicleClassGroupCode::BIKES => AuthorisationForTestingMotStatusCode::QUALIFIED,
        VehicleClassGroupCode::CARS_ETC => AuthorisationForTestingMotStatusCode::QUALIFIED,
    ];

    private $resultSet = [
        self::RESULT_FAILED_KEY => [],
        self::RESULT_SUCCESS_KEY => [],
    ];

    /**
     * @param TestSupportRestClientHelper $testSupportRestClientHelper
     * @param EntityManager               $entityManager
     */
    public function __construct(
        TestSupportRestClientHelper $testSupportRestClientHelper,
        EntityManager $entityManager
    ) {
        $this->testSupportRestClientHelper = $testSupportRestClientHelper;
        $this->entityManager = $entityManager;
    }

    /**
     * @param int   $testerId       a.k.a person id
     * @param array $qualifications e.g. ['A'=> 'QLFD' , 'B' => 'DMTN'] or just 'QLFD'
     *
     * @return JsonModel
     *
     * @throws \Exception
     */
    public function setTesterQualificationStatus($testerId, $qualifications)
    {
        if (!$this->validateQualifications($qualifications)) {
            throw new \Exception('Invalid qualification status provided');
        }

        $this->deleteTesterQualificationStatus($testerId);

        return $this->insert($testerId, $qualifications);
    }

    public function insertTesterQualificationStatus($testerId, $qualifications)
    {
        if (!$this->validateQualifications($qualifications)) {
            throw new \Exception('Invalid qualification status provided');
        }

        foreach ($qualifications as $group => $status) {
            $this->deleteTesterQualificationStatusForGroup($testerId, $group);
        }

        return $this->insert($testerId, $qualifications);
    }

    private function insert($testerId, $qualifications)
    {
        $qryInsertQualification = $this->entityManager->getConnection()->prepare(
            'INSERT INTO auth_for_testing_mot (person_id, status_id, vehicle_class_id, created_by)
             VALUES (:testerId, :statusId, :vehicleClassId, 1)'
        );

        $qryInsertQualification->bindValue(':testerId', $testerId);

        foreach ($qualifications as $group => $status) {
            $qryInsertQualification->bindValue(':statusId', $this->fetchQualificationStatusId($status));

            if (VehicleClassGroupCode::BIKES === $group) {
                $classes = VehicleClassGroup::getGroupAClasses();
            } elseif (VehicleClassGroupCode::CARS_ETC === $group) {
                $classes = VehicleClassGroup::getGroupBClasses();
            }

            foreach ($classes as $className) {
                $qryInsertQualification->bindValue(':vehicleClassId', str_replace('class', '', $className));
                if ($qryInsertQualification->execute()) {
                    $this->addSuccessResult($className, $status);
                } else {
                    $this->addFailedResult($className, $status);
                }
            }
        }

        return $this->getResultSet();
    }

    public function deleteTesterQualificationStatusForGroup($personId, $group)
    {
        $classes = VehicleClassGroup::getClassesForGroup($group);

        $conn = $this->entityManager->getConnection();
        $conn->executeQuery(
            'DELETE FROM auth_for_testing_mot WHERE person_id = :personId AND vehicle_class_id IN (:classes)',
            ['personId' => $personId, 'classes' => $classes],
            ['personId' => \Pdo::PARAM_INT, 'classes' => Connection::PARAM_STR_ARRAY]
        );
    }

    private function deleteTesterQualificationStatus($personId)
    {
        $qryFetchStatusId = $this->entityManager->getConnection()->prepare(
            'DELETE FROM auth_for_testing_mot WHERE person_id = :personId'
        );

        $qryFetchStatusId->execute([':personId' => $personId]);
    }

    /**
     * @param string $status e.g. QLFD or Qualified
     *
     * @return int
     */
    private function fetchQualificationStatusId($status)
    {
        $qryFetchStatusId = $this->entityManager->getConnection()->prepare(
            'SELECT id FROM auth_for_testing_mot_status WHERE code = :keyword OR name = :keyword'
        );

        $qryFetchStatusId->execute([':keyword' => $status]);
        $statusId = (int) $qryFetchStatusId->fetchColumn(0);

        return $statusId;
    }

    private function validateQualifications($qualifications)
    {
        if (count($qualifications) > 2) {
            return false;
        }

        foreach ($qualifications as $group => $status) {
            if (!in_array($group, VehicleClassGroupCode::getAll())) {
                return false;
            }

            if (!in_array($status, AuthorisationForTestingMotStatusCode::getAll())) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param string $className
     * @param string $status
     */
    private function addSuccessResult($className, $status)
    {
        $this->resultSet[self::RESULT_SUCCESS_KEY][$className] = $status;
    }

    /**
     * @param string $className
     * @param string $status
     */
    private function addFailedResult($className, $status)
    {
        $this->resultSet[self::RESULT_FAILED_KEY][$className] = $status;
    }

    /**
     * Construct respond's json model based on the cases of success or failure.
     *
     * @return JsonModel
     */
    private function getResultSet()
    {
        if (count($this->resultSet[self::RESULT_FAILED_KEY]) === 0) {
            $resultSet = TestDataResponseHelper::jsonOk(
                $this->resultSet[self::RESULT_SUCCESS_KEY]
            );
        } else {
            $resultSet = TestDataResponseHelper::jsonError(
                $this->resultSet
            );
        }

        return $resultSet;
    }
}
