<?php

namespace DvsaEntities\Repository;

use DvsaEntities\Entity\Notification;
use DvsaCommonApi\Service\Exception\NotFoundException;

class NotificationRepository extends AbstractMutableRepository
{
    /**
     * @param int $id
     * @return Notification
     * @throws NotFoundException
     */
    public function get($id)
    {
        $result = $this->find($id);
        if (is_null($result)) {
            throw new NotFoundException("Notification", $id);
        }

        return $result;
    }

    /**
     * @param int $personId
     * @param int $templateId
     * @return Notification[]
     */
    public function findAllByTemplateId($personId, $templateId)
    {
        return $this
            ->createQueryBuilder("n")
            ->addSelect(["nt", "f"])
            ->innerJoin("n.notificationTemplate", "nt")
            ->leftjoin("n.fields", "f")
            ->where("n.recipient = :personId")
            ->andWhere("nt.id = :templateId")
            ->setParameter("personId", $personId)
            ->setParameter("templateId", $templateId)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param int $personId
     * @param bool $archived
     * @return \DvsaEntities\Entity\Notification[]
     */
    public function findAllByPersonId($personId, $archived = false)
    {
        return $this
            ->createQueryBuilder("n")
            ->where("n.recipient = :personId")
            ->andWhere("n.isArchived = :archived")
            ->addOrderBy("n.createdOn", "DESC")
            ->setParameters(["personId" => $personId, "archived" => $archived])
            ->getQuery()
            ->getResult();
    }

    /**
     * @param int $personId
     * @return \Doctrine\ORM\QueryBuilder
     */
    private function buildUnreadByPersonIdQueryBuilder($personId)
    {
        return $this
            ->createQueryBuilder("n")
            ->where("n.recipient = :personId")
            ->andWhere("n.readOn IS NULL")
            ->andWhere("n.isArchived = 0")
            ->setParameter("personId", $personId);
    }

    /**
     * @param int $personId
     * @param int $limit
     * @return Notification[]
     */
    public function findUnreadByPersonId($personId, $limit = null)
    {
        $qb = $this->buildUnreadByPersonIdQueryBuilder($personId)
            ->orderBy("n.createdOn", "DESC")
            ->addOrderBy("n.id", "DESC");

        if(is_int($limit)) {
            $qb->setMaxResults($limit);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @param int $personId
     * @return int
     */
    public function countUnreadByPersonId($personId)
    {
        $qb = $this->buildUnreadByPersonIdQueryBuilder($personId);
        $qb->select("count(n)");

        return $qb->getQuery()->getSingleScalarResult();
    }
}
