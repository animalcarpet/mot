<?php

namespace DvsaEntities\Repository;

use Doctrine\ORM\Query\ResultSetMapping;
use DvsaEntities\Entity\Notification;
use DvsaCommonApi\Service\Exception\NotFoundException;

class NotificationRepository extends AbstractMutableRepository
{
    /**
     * @param int $id
     *
     * @return Notification
     *
     * @throws NotFoundException
     */
    public function get($id)
    {
        $result = $this->find($id);
        if (is_null($result)) {
            throw new NotFoundException('Notification', $id);
        }

        return $result;
    }

    /**
     * @param int $personId
     * @param int $templateId
     *
     * @return Notification[]
     */
    public function findAllByTemplateId($personId, $templateId)
    {
        return $this
            ->createQueryBuilder('n')
            ->addSelect(['nt', 'f'])
            ->innerJoin('n.notificationTemplate', 'nt')
            ->leftjoin('n.fields', 'f')
            ->where('n.recipient = :personId')
            ->andWhere('nt.id = :templateId')
            ->setParameter('personId', $personId)
            ->setParameter('templateId', $templateId)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param int  $personId
     * @param bool $archived
     *
     * @return \DvsaEntities\Entity\Notification[]
     */
    public function findAllByPersonId($personId, $archived = false)
    {
        return $this
            ->createQueryBuilder('n')
            ->where('n.recipient = :personId')
            ->andWhere('n.isArchived = :archived')
            ->addOrderBy('n.createdOn', 'DESC')
            ->setParameters(['personId' => $personId, 'archived' => $archived])
            ->getQuery()
            ->getResult();
    }

    /**
     * @param int $personId
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    private function buildUnreadByPersonIdQueryBuilder($personId)
    {
        return $this
            ->createQueryBuilder('n')
            ->where('n.recipient = :personId')
            ->andWhere('n.readOn IS NULL')
            ->andWhere('n.isArchived = 0')
            ->setParameter('personId', $personId);
    }

    /**
     * @param int $personId
     * @param int $limit
     *
     * @return Notification[]
     */
    public function findUnreadByPersonId($personId, $limit = null)
    {
        $qb = $this->buildUnreadByPersonIdQueryBuilder($personId)
            ->orderBy('n.createdOn', 'DESC')
            ->addOrderBy('n.id', 'DESC');

        if (is_int($limit)) {
            $qb->setMaxResults($limit);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @param int $personId
     *
     * @return int
     */
    public function countUnreadByPersonId($personId)
    {
        $qb = $this->buildUnreadByPersonIdQueryBuilder($personId);
        $qb->select('count(n)');

        return $qb->getQuery()->getSingleScalarResult();
    }

    public function checkIfNotificationsHasBeenSent(int $templateId, int $year, int $month): int
    {
        $sql = "
            SELECT EXISTS (
                SELECT 1
                FROM `notification`
                WHERE `notification`.`notification_template_id` = :TEMPLATE_ID
                    AND YEAR(`created_on`) = :YEAR
                    AND MONTH(`created_on`) = :MONTH
            )
        ";

        $resultSetMapping = new ResultSetMapping();
        $resultSetMapping->addScalarResult("found", "found", "integer");

        $conn = $this->getEntityManager()->getConnection();
        $stmt = $conn->prepare($sql);
        $stmt->bindValue("TEMPLATE_ID", $templateId);
        $stmt->bindValue("YEAR", $year);
        $stmt->bindValue("MONTH", $month);

        $stmt->execute();
        $exists = (int) $stmt->fetchColumn();

        return $exists === 1;
    }

    /**
     * @param int $templateId
     * @param int $recipientId
     * @param int $createdById
     * @param array $fieldData
     * @return int Notification id
     */
    public function saveNotificationWithFields(int $templateId, int $recipientId, int $createdById, array $fieldData):int
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = "
            INSERT INTO `notification`
                (`notification_template_id`, `recipient_id`, `created_by`)
            VALUES
	            (:templateId, :personId, :createdById)";

        $stmt = $conn->prepare($sql);
        $stmt->bindValue("personId", $recipientId);
        $stmt->bindValue("templateId", $templateId);
        $stmt->bindValue("createdById", $createdById);
        $stmt->execute();
        $notificationId = $conn->lastInsertId();

        $notificationFieldSql = "
            INSERT INTO `notification_field`
                (`notification_id`, `field`, `content`, `created_by`)
            VALUES
	            (:notificationId, :fieldName, :fieldContent, :createdById)";

        foreach ($fieldData as $field => $value) {
            $stmt = $conn->prepare($notificationFieldSql);
            $stmt->bindValue("notificationId", $notificationId);
            $stmt->bindValue("fieldName", $field);
            $stmt->bindValue("fieldContent", $value);
            $stmt->bindValue("createdById", $createdById);
            $stmt->execute();
        }

        return $notificationId;
    }
}
