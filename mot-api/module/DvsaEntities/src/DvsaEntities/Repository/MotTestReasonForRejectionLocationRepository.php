<?php
/**
 * This file is part of the DVSA MOT API project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace DvsaEntities\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;

class MotTestReasonForRejectionLocationRepository extends EntityRepository
{
    public function getLocation($lateral, $longitudinal, $vertical)
    {
        $qb = $this
            ->createQueryBuilder('l')
            ->setMaxResults(1);

        if (null === $lateral) {
            $qb->andWhere('l.lateral IS NULL');
        } else {
            $qb->andWhere('l.lateral = :LATERAL');
            $qb->setParameter('LATERAL', $lateral);
        }

        if (null === $longitudinal) {
            $qb->andWhere('l.longitudinal IS NULL');
        } else {
            $qb->andWhere('l.longitudinal = :LONGITUDINAL');
            $qb->setParameter('LONGITUDINAL', $longitudinal);
        }

        if (null === $vertical) {
            $qb->andWhere('l.vertical IS NULL');
        } else {
            $qb->andWhere('l.vertical = :VERTICAL');
            $qb->setParameter('VERTICAL', $vertical);
        }

        try {
            $location = $qb->getQuery()->getSingleResult();

            return $location;
        } catch (NoResultException $e) {
            return false;
        }
    }
}
