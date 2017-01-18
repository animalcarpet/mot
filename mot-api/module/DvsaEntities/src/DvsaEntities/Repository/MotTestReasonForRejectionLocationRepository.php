<?php
/**
 * This file is part of the DVSA MOT API project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace DvsaEntities\Repository;

use Doctrine\ORM\EntityNotFoundException;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;

class MotTestReasonForRejectionLocationRepository extends EntityRepository
{
    public function getLocation($lateral, $longitudinal, $vertical)
    {
        $qb = $this
            ->createQueryBuilder("l")
            ->where("l.lateral = :LATERAL")
            ->andWhere("l.longitudinal = :LONGITUDINAL")
            ->andWhere("l.vertical = :VERTICAL")
            ->setParameter("LATERAL", $lateral)
            ->setParameter("LONGITUDINAL", $longitudinal)
            ->setParameter("VERTICAL", $vertical)
            ->setMaxResults(1);

        try {
            $location = $qb->getQuery()->getSingleResult();
            return $location;
        } catch (NoResultException $e) {
            return false;
        }
    }
}
