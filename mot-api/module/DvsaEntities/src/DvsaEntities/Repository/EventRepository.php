<?php

namespace DvsaEntities\Repository;

use Doctrine\ORM\NoResultException;
use Doctrine\ORM\QueryBuilder;
use DvsaCommon\Dto\Event\EventFormDto;
use DvsaEntities\Entity\Event;
use DvsaCommonApi\Service\Exception\NotFoundException;
use DvsaEntities\Entity\Person;

/**
 * Class EventRepository.
 */
class EventRepository extends AbstractMutableRepository
{
    /**
     * This function build the common query builder for the event search.
     *
     * @param int    $id
     * @param string $type
     *
     * @return QueryBuilder
     */
    private function getQueryBuilder($id = null, $type = null)
    {
        $qb = $this
            ->createQueryBuilder('e')
            ->addSelect('et')
            ->innerJoin('e.eventType', 'et');

        switch (strtoupper($type)) {
            case 'AE':
                $qb = $this->joinEventOrganisationMap($qb, $id);
                break;
            case 'SITE':
                $qb = $this->joinEventSiteMap($qb, $id);
                break;
            case 'PERSON':
                $qb = $this->joinEventPersonMap($qb, $id);
                break;
        }

        return $qb;
    }

    /**
     * This function join EventOrganisationMap.
     *
     * @param QueryBuilder $qb
     * @param int          $organisationId
     *
     * @return QueryBuilder
     */
    private function joinEventOrganisationMap(QueryBuilder $qb, $organisationId)
    {
        $qb
            ->innerJoin('e.eventOrganisationMaps', 'eom')
            ->where('eom.organisation = :ORGANISATION_ID')
            ->setParameter('ORGANISATION_ID', $organisationId);

        return $qb;
    }

    /**
     * This function join EventSiteMaps.
     *
     * @param QueryBuilder $qb
     * @param int          $siteId
     *
     * @return QueryBuilder
     */
    private function joinEventSiteMap(QueryBuilder $qb, $siteId)
    {
        $qb
            ->innerJoin('e.eventSiteMaps', 'esm')
            ->where('esm.site = :SITE_ID')
            ->setParameter('SITE_ID', $siteId);

        return $qb;
    }

    /**
     * This function join EventPersonMap.

     * @param QueryBuilder $qb
     * @param int          $personId
     *
     * @return QueryBuilder
     */
    private function joinEventPersonMap(QueryBuilder $qb, $personId)
    {
        $qb
            ->innerJoin('e.eventPersonMaps', 'epm')
            ->where('epm.person = :PERSON_ID')
            ->setParameter('PERSON_ID', $personId);

        return $qb;
    }

    /**
     * This function allow us to get a list of the events related to an entity.
     *
     * @param int          $id
     * @param EventFormDto $dto
     * @param string       $type
     *
     * @return Event[]
     */
    public function findEvents($id, EventFormDto $dto, $type)
    {
        $qb = $this->getQueryBuilder($id, $type);
        $qb = $this->filterResults($qb, $dto, false);

        return $qb->getQuery()->getResult();
    }

    /**
     * This function allow us to get the total count of the events related to an entity.
     *
     * @param int          $id
     * @param EventFormDto $dto
     * @param string       $type
     *
     * @return int
     */
    public function findEventsCount($id, EventFormDto $dto, $type)
    {
        $qb = $this->getQueryBuilder($id, $type);
        $qb->select('DISTINCT COUNT(e)');
        $qb = $this->filterResults($qb, $dto, true);

        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Filter event results based on the params passed in
     * via the DTO.
     *
     * @param QueryBuilder $qb
     * @param EventFormDto $dto
     * @param bool         $isCount
     *
     * @return QueryBuilder
     */
    private function filterResults(QueryBuilder $qb, EventFormDto $dto, $isCount)
    {
        if (!empty($dto->getSearch())) {
            $qb->andWhere('et.description LIKE :SEARCH_EVENT OR e.shortDescription LIKE :SEARCH_EVENT')
                ->setParameter('SEARCH_EVENT', trim($dto->getSearch()).'%');
        }

        if ($this->isDateRangeIsValid($dto)) {
            $qb->andwhere(
                '((e.eventDate BETWEEN :DATE_FROM AND :DATE_TO)
                OR (e.eventDate BETWEEN :DATE_FROM AND :DATE_TO))'
            )
                ->setParameter('DATE_FROM', $dto->getDateFrom()->getDate())
                ->setParameter('DATE_TO', $dto->getDateTo()->getDate()->modify('+1 day'));
        }
        if ($isCount === false) {
            $qb->orderBy(EventFormDto::$dbSortByColumns[$dto->getSortCol()], $dto->getSortDir());
            $qb->setFirstResult($dto->getDisplayStart());
            $qb->setMaxResults($dto->getDisplayLength());
        }

        return $qb;
    }

    /**
     * This function check if we attempt to search by date range and if the date are valid.
     *
     * @param EventFormDto $dto
     *
     * @return bool
     */
    private function isDateRangeIsValid(EventFormDto $dto)
    {
        if ($dto->isShowDate() === true
            && $dto->getDateFrom()->getDate() !== null
            && $dto->getDateTo()->getDate() !== null) {
            return true;
        }

        return false;
    }

    /**
     * @param $eventId
     *
     * @return Event
     *
     * @throws NotFoundException
     */
    public function findEvent($eventId)
    {
        $qb = $this->getQueryBuilder();
        $qb->where('e.id = :EVENT_ID');

        $qb->setParameter('EVENT_ID', $eventId);

        try {
            $event = $qb->getQuery()->getSingleResult();
        } catch (NoResultException $e) {
            throw new NotFoundException('Event not found');
        }

        return $event;
    }

    /**
     * This function test if the user have already an event register with a specific code.
     *
     * @param Person $person
     * @param string $code
     *
     * @return bool
     */
    public function isEventCreatedBy(Person $person, $code)
    {
        $qb = $this
            ->createQueryBuilder('e')
            ->select('count(e)')
            ->innerJoin('e.eventType', 'et')
            ->innerJoin('e.eventPersonMaps', 'epm')
            ->where('epm.person = :PERSON_ID')
            ->andWhere('et.code = :CODE')
            ->setParameter('PERSON_ID', $person)
            ->setParameter('CODE', $code);

        return $qb->getQuery()->getSingleScalarResult() != 0;
    }
}
