<?php

namespace DvsaEntities\Repository;

class PasswordDetailRepository extends AbstractMutableRepository
{
    /**
     * @param int $personId
     *
     * @return mixed
     */
    public function findByPersonId($personId)
    {
        return $this
            ->createQueryBuilder('pd')
            ->where('pd.person = :person')
            ->setParameter('person', $personId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param int $personId
     *
     * @return \DateTime
     */
    public function findPasswordNotificationSentDateByPersonId($personId)
    {
        $passwordDetail = $this
            ->createQueryBuilder('pd')
            ->where('pd.person = :person')
            ->setParameter('person', $personId)
            ->getQuery()
            ->getOneOrNullResult();

        if (!is_null($passwordDetail)) {
            return $passwordDetail->getPasswordNotificationSentDate();
        }

        return null;
    }
}
