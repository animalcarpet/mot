<?php

namespace TestSupport\Controller;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManager;
use DvsaCommon\Utility\ArrayUtils;
use TestSupport\Helper\TestDataResponseHelper;

/**
 * Vehicle related methods.
 *
 * Should not be deployed in production.
 */
class EventDataController extends BaseTestSupportRestfulController
{
    const EVENT_TYPE = 'type';
    const EVENT_TYPE_ID = 'type-id';
    const EVENT_DATE = 'event-date';
    const ENTITY_ID = 'entity-id';
    const SHORT_DESCRIPTION = 'short-description';
    const CREATED_BY = 'created_by';

    public function create($data)
    {
        $type = ArrayUtils::tryGet($data, self::EVENT_TYPE, 'ae');
        $typeId = ArrayUtils::tryGet($data, self::EVENT_TYPE_ID, 1);
        $entityId = ArrayUtils::tryGet($data, self::ENTITY_ID, 1);
        $description = ArrayUtils::tryGet($data, self::SHORT_DESCRIPTION, 'Short Description');
        $date = ArrayUtils::tryGet($data, self::EVENT_DATE, '2015-01-01 10:00:00.000000');
        $createdBy = ArrayUtils::tryGet($data, self::CREATED_BY, 1);

        /** @var EntityManager $entityManager */
        $entityManager = $this->getServiceLocator()->get(EntityManager::class);
        /** @var Connection $connection */
        $connection = $entityManager->getConnection();

        $connection->executeQuery(
            'INSERT INTO event(event_type_id, description, event_date, created_by)
              VALUE(:type_id, :description, :event_date, :created_by)',
            [
                'type_id' => $typeId,
                'description' => $description,
                'event_date' => $date,
                'created_by' => $createdBy,
            ]
        );
        $eventId = $connection->lastInsertId();
        $mapTable = $this->getMapTable($type);
        $colMapTable = $this->getColumnForMapTable($type);
        $connection->executeQuery(
            'INSERT INTO '.$mapTable.'(event_id, '.$colMapTable.', created_by)
              VALUE(:event_id, :entity_id, :created_by)',
            [
                'event_id' => $eventId,
                'entity_id' => $entityId,
                'created_by' => $createdBy,
            ]
        );

        return TestDataResponseHelper::jsonOk($eventId);
    }

    private function getMapTable($type)
    {
        switch ($type) {
            case 'person':
                return 'event_person_map';
            case 'site':
                return 'event_site_map';
            case 'ae':
            default:
                return 'event_organisation_map';
        }
    }

    private function getColumnForMapTable($type)
    {
        switch ($type) {
            case 'person':
                return 'person_id';
            case 'site':
                return 'site_id';
            case 'ae':
            default:
                return 'organisation_id';
        }
    }
}
