<?php

namespace DvsaEntities\Repository\Query;

use DateInterval;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use DvsaCommon\Domain\MotTestType as DomainMotTestType;
use DvsaEntities\DqlBuilder\SearchParam\MotTestSearchParam;
use DvsaEntities\Entity\Make;
use DvsaEntities\Entity\Model;
use DvsaEntities\Entity\ModelDetail;
use DvsaEntities\Entity\MotTest;
use DvsaEntities\Entity\MotTestStatus;
use DvsaEntities\Entity\MotTestType;
use DvsaEntities\Entity\Person;
use DvsaEntities\Entity\Site;
use DvsaEntities\Entity\Vehicle;
use DvsaEntities\Entity\VehicleHistory;
use DvsaEntities\Repository\MotTestHistoryRepository;

class MotTestSearchQuery
{
    const MOT_TEST_CURRENT_INDEX = 'ix_mot_test_current_site_id_started_date_completed_date';
    const MOT_TEST_HISTORY_INDEX = 'ix_mot_test_history_site_id_started_date_completed_date';

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var string
     */
    private $historyTableName;

    /**
     * @param EntityManagerInterface $entityManager
     * @param string                 $historyTableName
     */
    public function __construct(EntityManagerInterface $entityManager, $historyTableName)
    {
        $this->entityManager = $entityManager;
        $this->historyTableName = $historyTableName;
    }

    /**
     * @param MotTestSearchParam $searchParam
     * @param array              $optionalMotTestTypes
     *
     * @return MotTest[]
     */
    public function getResult(MotTestSearchParam $searchParam, array $optionalMotTestTypes)
    {
        $parameters = $this->getParameters($searchParam, $optionalMotTestTypes);
        $rsm = $this->createResultSetMapper();

        $sql = $this->getSql($parameters, $searchParam, $rsm->generateSelectClause());
        $sql .= $this->getOrderBySql($searchParam);
        $sql .= $this->getLimitSql($searchParam);

        $query = $this->entityManager->createNativeQuery($sql, $rsm);
        $query->setParameters($parameters);

        return $query->getResult();
    }

    /**
     * @param MotTestSearchParam $searchParam
     * @param array              $optionalMotTestTypes
     *
     * @return int
     */
    public function countResult(MotTestSearchParam $searchParam, array $optionalMotTestTypes)
    {
        $parameters = $this->getParameters($searchParam, $optionalMotTestTypes);
        $sql = $this->getCountSql($parameters, $searchParam);
        $statement = $this->entityManager->getConnection()->executeQuery($sql, $parameters);

        return (int) $statement->fetchColumn(0);
    }

    /**
     * @return ResultSetMappingBuilder
     */
    private function createResultSetMapper()
    {
        $rsm = new ResultSetMappingBuilder($this->entityManager, ResultSetMappingBuilder::COLUMN_RENAMING_INCREMENT);

        // We're forced to add each field of the root entity manually as addRootEntityFromClassMetadata() doesn't handle
        // primary keys which are foreign keys in the same time (like $motTestCancelled, $complaintRef, $motTestEmergencyReason).
        $rsm->addEntityResult(MotTest::class, 'test');
        $rsm->addFieldResult('test', 'id', 'id', MotTest::class);
        $rsm->addFieldResult('test', 'number', 'number', MotTest::class);
        $rsm->addFieldResult('test', 'vehicle_version', 'vehicleVersion', MotTest::class);
        $rsm->addFieldResult('test', 'has_registration', 'hasRegistration', MotTest::class);
        $rsm->addFieldResult('test', 'started_date', 'startedDate', MotTest::class);
        $rsm->addFieldResult('test', 'completed_date', 'completedDate', MotTest::class);
        $rsm->addFieldResult('test', 'issued_date', 'issuedDate', MotTest::class);
        $rsm->addFieldResult('test', 'expiry_date', 'expiryDate', MotTest::class);
        $rsm->addFieldResult('test', 'vehicle_weight', 'vehicleWeight', MotTest::class);
        $rsm->addFieldResult('test', 'submitted_date', 'submittedDate', MotTest::class);
        $rsm->addFieldResult('test', 'version', 'version', MotTest::class);

        // Adding known aliases for the fields we need to sort with.
        $rsm->addMetaResult('status', 'status_name', 'name');
        $rsm->addMetaResult('vehicle', 'vehicle_vin', 'vin');
        $rsm->addMetaResult('vehicle', 'vehicle_registration', 'registration');
        $rsm->addMetaResult('vehicle_hist', 'vehicle_hist_vin', 'vin');
        $rsm->addMetaResult('vehicle_hist', 'vehicle_hist_registration', 'registration');
        $rsm->addMetaResult('model', 'model_name', 'name');
        $rsm->addMetaResult('make', 'make_name', 'name');
        $rsm->addMetaResult('mot_test_type', 'mot_test_type_description', 'description');
        $rsm->addMetaResult('site', 'site_number', 'site_number');
        $rsm->addMetaResult('person', 'tester_username', 'username');
        $rsm->addMetaResult('model_hist', 'model_hist_name', 'name');
        $rsm->addMetaResult('make_hist', 'make_hist_name', 'name');

        // Since we added the root entity manually, we also need to add its relationships.
        $rsm->addJoinedEntityFromClassMetadata(Person::class, 'person', 'test', 'tester');
        $rsm->addJoinedEntityFromClassMetadata(Vehicle::class, 'vehicle', 'test', 'vehicle');
        $rsm->addJoinedEntityFromClassMetadata(VehicleHistory::class, 'vehicle_hist', 'vehicle', 'vehicleHistory');
        $rsm->addJoinedEntityFromClassMetadata(ModelDetail::class, 'model_detail', 'vehicle', 'modelDetail');
        $rsm->addJoinedEntityFromClassMetadata(Model::class, 'model', 'model_detail', 'model');
        $rsm->addJoinedEntityFromClassMetadata(Model::class, 'model_hist', 'model_detail_hist', 'model');
        $rsm->addJoinedEntityFromClassMetadata(ModelDetail::class, 'model_detail_hist', 'vehicle_hist', 'modelDetail');
        $rsm->addJoinedEntityFromClassMetadata(Make::class, 'make', 'model', 'make');
        $rsm->addJoinedEntityFromClassMetadata(Make::class, 'make_hist', 'model_hist', 'make');
        $rsm->addJoinedEntityFromClassMetadata(Site::class, 'site', 'test', 'vehicleTestingStation');
        $rsm->addJoinedEntityFromClassMetadata(MotTestType::class, 'mot_test_type', 'test', 'motTestType');
        $rsm->addJoinedEntityFromClassMetadata(MotTestStatus::class, 'status', 'test', 'status');

        return $rsm;
    }

    /**
     * @param MotTestSearchParam $searchParam
     * @param array $optionalMotTestTypes
     *
     * @return array
     */
    private function getParameters(MotTestSearchParam $searchParam, array $optionalMotTestTypes)
    {
        $parameters = [];

        foreach ($this->getMotTestHistoryTestTypes($optionalMotTestTypes) as $i => $type) {
            $parameters['testType'.$i] = $type;
        }

        if ($searchParam->getDateFrom()) {
            $parameters['DATE_FROM'] = $searchParam->getDateFrom()->format('Y-m-d H:i:s');
        }

        if ($searchParam->getDateTo()) {
            $endDate = clone $searchParam->getDateTo();
            $parameters['DATE_TO'] = $endDate->add(new DateInterval('P1M'))->format('Y-m-d H:i:s');
        }

        if ($searchParam->getSiteNumber()) {
            $parameters['SITE_NR'] = $searchParam->getSiteNumber();
        }

        if ($searchParam->getTesterId()) {
            $parameters['TESTER_ID'] = $searchParam->getTesterId();
        }

        if ($searchParam->getRegistration()) {
            $parameters['VRM'] = $searchParam->getRegistration();
        }

        if ($searchParam->getVin()) {
            $parameters['VIN'] = $searchParam->getVin();
        }

        if ($searchParam->getVehicleId()) {
            $parameters['VEHICLE_ID'] = $searchParam->getVehicleId();
        }

        if ($searchParam->getTestNumber()) {
            $parameters['TEST_NUMBER'] = $searchParam->getTestNumber();
        }

        return $parameters;
    }

    /**
     * @param array $optionalMotTestTypes
     *
     * @return array
     */
    private function getMotTestHistoryTestTypes(array $optionalMotTestTypes)
    {
        return array_merge(DomainMotTestType::getMotTestHistoryTypes(), $optionalMotTestTypes);
    }

    /**
     * @param array              $parameters
     * @param MotTestSearchParam $searchParam
     * @param string             $selectClause
     *
     * @return string
     */
    private function getSql(array $parameters, MotTestSearchParam $searchParam, $selectClause)
    {
        // This query depends on two indexes in the mot database:
        // ix_mot_test_current_site_id_started_date_completed_date on mot_test_current and
        // ix_mot_test_history_site_id_started_date_completed_date on mot_test_history.

        $sql = 'SELECT '.$selectClause.'
                FROM mot_test_current test FORCE INDEX ('.self::MOT_TEST_CURRENT_INDEX.')
                LEFT JOIN site ON test.site_id = site.id
                LEFT JOIN vehicle ON test.vehicle_id = vehicle.id
                LEFT JOIN vehicle_hist ON vehicle.id = vehicle_hist.id AND vehicle_hist.version = test.vehicle_version
                LEFT JOIN model_detail ON vehicle.model_detail_id = model_detail.id
                LEFT JOIN model ON model_detail.model_id = model.id
                LEFT JOIN make ON model.make_id = make.id
                LEFT JOIN model_detail AS model_detail_hist ON vehicle_hist.model_detail_id = model_detail_hist.id
                LEFT JOIN model AS model_hist ON model_detail_hist.model_id = model_hist.id
                LEFT JOIN make AS make_hist ON model_hist.make_id = make_hist.id
                INNER JOIN mot_test_type ON test.mot_test_type_id = mot_test_type.id
                INNER JOIN mot_test_status status ON test.status_id = status.id
                INNER JOIN person ON test.person_id = person.id
        ';

        // Test types need to be split into multiple placeholders instead of one
        // in order for the raw sql count query to work
        // (PDO cannot bind arrays, while Doctrine would do it behind the scenes).
        $testTypes = array_filter($parameters, function ($name) {
            return 0 === strpos($name, 'testType');
        }, ARRAY_FILTER_USE_KEY);

        $sql .= 'WHERE mot_test_type.code IN (\''.implode('\', \'', $testTypes).'\')';

        if (isset($parameters['DATE_FROM'])) {
            $sql .= ' AND test.started_date >= :DATE_FROM';
        }

        if (isset($parameters['DATE_TO'])) {
            $sql .= ' AND test.started_date <= :DATE_TO';
        }

        if (isset($parameters['SITE_NR'])) {
            $sql .= ' AND site.site_number = :SITE_NR';
        }

        if (isset($parameters['TESTER_ID'])) {
            $sql .= ' AND test.person_id = :TESTER_ID';
        }

        if (isset($parameters['VRM'])) {
            $sql .= ' AND vehicle.registration = :VRM';
        }

        if (isset($parameters['VIN'])) {
            $sql .= ' AND vehicle.vin = :VIN';
        }

        if (isset($parameters['VEHICLE_ID'])) {
            $sql .= ' AND test.vehicle_id = :VEHICLE_ID';
        }

        if (isset($parameters['TEST_NUMBER'])) {
            $sql .= ' AND test.number = :TEST_NUMBER';
        }

        if ($this->needsHistory($searchParam)) {
            $tableName = $this->historyTableName;
            $sqlCurrent = $sql;
            $sqlHistory = str_replace($tableName, str_replace(MotTestHistoryRepository::SUFFIX_CURRENT, MotTestHistoryRepository::SUFFIX_HISTORY, $tableName), $sqlCurrent);
            $sqlHistory = str_replace(self::MOT_TEST_CURRENT_INDEX, self::MOT_TEST_HISTORY_INDEX, $sqlHistory);
            $sql = $sqlCurrent.' UNION '.$sqlHistory;
        }

        return $sql;
    }

    /**
     * @param array              $parameters
     * @param MotTestSearchParam $searchParam
     *
     * @return string
     */
    private function getCountSql(array $parameters, MotTestSearchParam $searchParam)
    {
        if (!$this->needsHistory($searchParam)) {
            return $this->getSql($parameters, $searchParam, 'count(*) as cnt');
        }

        $sql = $this->getSql($parameters, $searchParam, 'test.id');
        $sql = sprintf('SELECT count(*) as cnt FROM (%s) t', $sql);

        return $sql;
    }

    /**
     * @param MotTestSearchParam $searchParam
     *
     * @return string
     */
    private function getOrderBySql(MotTestSearchParam $searchParam)
    {
        $sql = '';

        $orderBy = $searchParam->getSortColumnNameDatabase();
        if (!empty($orderBy)) {
            if (!is_array($orderBy)) {
                $orderBy = [$orderBy];
            }

            $sql .= ' ORDER BY ';
            $sql .= implode(', ', array_map(function ($order) use ($searchParam) {
                // @todo getSortColumnNameDatabase should probably return the db value
                $columns = [
                    'test.completedDate, test.startedDate' => 'completed_date, started_date',
                    'test.completedDate' => 'completed_date',
                    'test.startedDate' => 'started_date',
                    'test.status' => 'status_name',
                    'vehicle.vin' => 'IFNULL(vehicle_hist_vin, vehicle_vin)',
                    'vehicle.registration' => 'IFNULL(vehicle_hist_registration, vehicle_registration)',
                    'make.name' => 'IFNULL(make_hist_name, make_name)',
                    'model.name' => 'IFNULL(model_hist_name, model_name)',
                    'testType.description' => 'mot_test_type_description',
                    'site.siteNumber' => 'site_number',
                    'tester.username' => 'tester_username',
                ];
                $column = isset($columns[$order]) ? $columns[$order] : $order;

                return sprintf('%s %s', $column, $searchParam->getSortDirection());
            }, $orderBy));
        }

        return $sql;
    }

    /**
     * @param MotTestSearchParam $searchParam
     *
     * @return string
     */
    private function getLimitSql(MotTestSearchParam $searchParam)
    {
        $sql = '';

        if ($searchParam->getRowCount() > 0) {
            $sql .= sprintf(' LIMIT %d', $searchParam->getRowCount());
            if ($searchParam->getStart() > 0) {
                $sql .= sprintf(' OFFSET %d', $searchParam->getStart());
            }
        }

        return $sql;
    }

    /**
     * @param MotTestSearchParam $searchParam
     *
     * @return bool
     */
    private function needsHistory(MotTestSearchParam $searchParam)
    {
        if ($searchParam->getDateFrom()) {
            $historyDate = new DateTime();
            $historyDate->sub(new DateInterval('P4Y'));

            return $searchParam->getDateFrom() < $historyDate;
        }

        return true;
    }
}
