<?php

namespace DvsaEntities\Repository;

use Doctrine\ORM\EntityRepository;
use DvsaEntities\Entity\EventCategory;
use DvsaEntities\Entity\EventOutcome;
use DvsaEntities\Entity\EventTypeOutcomeCategoryMap;
use DvsaEntities\Entity\EventType;
use Doctrine\ORM\Query\Expr\Join;

class EventTypeOutcomeCategoryMapRepository extends EntityRepository
{
    public function getEventTypeWithOutcomes()
    {
        $qb = $this->getEntityManager()->createQueryBuilder();

        $qb->select(['ec.code AS categoryCode', 'ec.shortName', 'et.code as typeCode', 'et.description as typeName', 'eo.code as outcomeCode', 'eo.description as outcomeName'])
            ->from(EventTypeOutcomeCategoryMap::class, 'etocm')
            ->join('etocm.eventCategory', 'ec')
            ->join('etocm.eventOutcome', 'eo')
            ->join('etocm.eventType', 'et')
            ->orderBy('ec.id, et.description, eo.description');

        return $qb->getQuery()->getArrayResult();
    }

    /**
     * Validates if outcome is valid for the chosen category and type. Outcome is valid when its associated with a
     * specific type and category.
     *
     * @param int $eventCategoryId
     * @param int $eventTypeId
     * @param int $eventOutcomeId
     *
     * @return bool
     */
    public function isOutcomeAssociatedWithCategoryAndType($eventCategoryId, $eventTypeId, $eventOutcomeId)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();

        $qb
            ->select('COUNT(eventMap.id)')
            ->from(EventTypeOutcomeCategoryMap::class, 'eventMap')
            ->where('eventMap.eventCategory = :eventCategoryID')
            ->andWhere('eventMap.eventType = :eventTypeID')
            ->andWhere('eventMap.eventOutcome = :eventOutcomeID')
            ->setParameters(
                [
                    'eventCategoryID' => $eventCategoryId,
                    'eventTypeID' => $eventTypeId,
                    'eventOutcomeID' => $eventOutcomeId,
                ]
            );

        return (bool) $qb->getQuery()->getSingleScalarResult();
    }
}
