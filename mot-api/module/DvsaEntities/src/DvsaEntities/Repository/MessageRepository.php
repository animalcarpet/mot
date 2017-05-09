<?php

namespace DvsaEntities\Repository;

use DvsaCommon\Date\DateTimeApiFormat;
use DvsaCommon\Date\DateTimeHolder;
use DvsaCommonApi\Service\Exception\NotFoundException;
use DvsaEntities\Entity\Message;
use DvsaEntities\Entity\MessageType;
use DvsaEntities\Entity\Person;

/**
 * Class MessageRepository.
 *
 * Custom Doctrine Repository for reusable DQL queries
 *
 * @codeCoverageIgnore
 */
class MessageRepository extends AbstractMutableRepository
{
    /**
     * @param Person      $person
     * @param MessageType $messageType
     * @param \DateTime   $date
     *
     * @return bool
     */
    public function hasAlreadyRequestedMessage(Person $person, MessageType $messageType, \DateTime $date)
    {
        $issueDate = DateTimeApiFormat::date($date);

        $qb = $this->createQueryBuilder('m');
        $qb
            ->select('COUNT(m.id)')
            ->where('m.person = :person')
            ->andWhere('m.messageType = :messageType')
            ->andWhere('m.issueDate >= :issueDateMin')
            ->andWhere('m.issueDate <= :issueDateMax')
            ->setParameter('person', $person)
            ->setParameter('messageType', $messageType)
            ->setParameter('issueDateMin', new \DateTime($issueDate.' 00:00:00'))
            ->setParameter('issueDateMax', new \DateTime($issueDate.' 23:59:59'));

        $number = (int) $qb->getQuery()->getSingleScalarResult();

        if ($number) {
            return true;
        }

        return false;
    }

    /**
     * @param string $token
     *
     * @return Message
     *
     * @throws NotFoundException
     */
    public function getHydratedMessageByToken($token, $isValid = false)
    {
        $qb = $this->createQueryBuilder('m')
            ->select('m, mt, p')
            ->join('m.messageType', 'mt')
            ->join('m.person', 'p')
            ->where('m.token = :TOKEN')
            ->setParameter('TOKEN', $token)
            ->setMaxResults(1);

        if ($isValid) {
            $qb
                ->andWhere('m.expiryDate > :EXPIRY_DATETIME ')
                ->andWhere('m.isAcknowledged = FALSE')
                ->setParameter('EXPIRY_DATETIME', (new DateTimeHolder())->getCurrent());
        }

        $result = $qb->getQuery()->getResult();
        if (count($result) == 0) {
            throw new NotFoundException('Message by Token', $token);
        }

        return current($result);
    }
}
