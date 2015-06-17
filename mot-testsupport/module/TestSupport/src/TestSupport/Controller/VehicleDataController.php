<?php

namespace TestSupport\Controller;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManager;
use DvsaCommon\Utility\ArrayUtils;
use TestSupport\Helper\TestDataResponseHelper;
use TestSupport\Service\VehicleService;
use Zend\Mvc\Controller\AbstractRestfulController;

/**
 * Vehicle related methods
 *
 * Should not be deployed in production.
 */
class VehicleDataController extends BaseTestSupportRestfulController
{
    public function v5cAddAction()
    {
        $data = get_object_vars(json_decode($this->getRequest()->getContent()));

        $vehicleId = ArrayUtils::get($data, 'vehicleId');
        $v5cRef = ArrayUtils::get($data, 'v5cRef');
        $firstSeen = ArrayUtils::get($data, 'firstSeen');
        $lastSeen = ArrayUtils::get($data, 'lastSeen');
        $mot1LegacyId = ArrayUtils::get($data, 'mot1LegacyId');

        /** @var EntityManager $entityManager */
        $entityManager = $this->getServiceLocator()->get(EntityManager::class);
        /** @var Connection $connection */
        $connection = $entityManager->getConnection();

        $connection->transactional(
            function () use ($vehicleId, $v5cRef, $firstSeen, $lastSeen, $mot1LegacyId, $connection) {
                $connection->executeUpdate(
                    'UPDATE vehicle_v5c SET last_seen = now() WHERE vehicle_id = :vehicleId',
                    ['vehicleId' => $vehicleId]
                );
                $date = date("Y-m-d h:i:s.u");
                $connection->executeUpdate(
                    'INSERT INTO vehicle_v5c(vehicle_id, v5c_ref, first_seen, last_seen, mot1_legacy_id, created_by)
                    VALUE(:vehicleId, :v5cRef, :firstSeen, :lastSeen, :mot1LegacyId, :createdBy)',
                    [
                        'vehicleId'    => $vehicleId,
                        'v5cRef'       => $v5cRef,
                        'firstSeen'    => $firstSeen === null ? $date : $firstSeen,
                        'lastSeen'     => $lastSeen,
                        'mot1LegacyId' => $mot1LegacyId,
                        'createdBy'    => 1
                    ]
                );
            }
        );

        return TestDataResponseHelper::jsonOk('Success');
    }

    public function createAction()
    {
        $data = get_object_vars(json_decode($this->getRequest()->getContent()));
        $vehicleService = $this->getServiceLocator()->get(VehicleService::class);

        try {
            $vehicleId = $vehicleService->save($data);
        } catch (\Exception $e) {
            return TestDataResponseHelper::jsonError('Failed');
        }

        return TestDataResponseHelper::jsonOk($vehicleId);
    }

}
