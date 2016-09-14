<?php
/**
 * This file is part of the DVSA MOT API project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace DvsaEntities\Repository\Doctrine;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query\Expr\Join;
use DvsaCommonApi\Service\Exception\NotFoundException;
use DvsaEntities\Entity\MotTest;
use DvsaEntities\Entity\MotTestSurvey;
use DvsaEntities\Repository\MotTestSurveyRepository;

class DoctrineMotTestSurveyRepository extends EntityRepository implements MotTestSurveyRepository
{
    /**
     * @param string $token
     *
     * @return MotTestSurvey|null|object
     */
    public function findOneByToken($token)
    {
        return $this->findOneBy(['token' => $token]);
    }

    /**
     * @param string $personId
     *
     * @throws NotFoundException
     *
     * @return string
     */
    public function getLastUserSurveyDate($personId)
    {
        try {
            return $this
                ->getEntityManager()
                ->createQueryBuilder()
                ->select('COALESCE(mts.lastUpdatedOn, mts.createdOn) as updateDate')
                ->from(MotTestSurvey::class, 'mts')
                ->leftJoin('mts.motTest', 'mt')
                ->where('mt.tester = :personId')
                ->orderBy('updateDate', 'DESC')
                ->setMaxResults(1)
                ->setParameter('personId', $personId)
                ->getQuery()
                ->getSingleScalarResult();
        } catch (NoResultException $e) {
        }

        throw new NotFoundException(MotTestSurvey::class);
    }

    /**
     * @throws NotFoundException
     *
     * @return int
     */
    public function getLastSurveyMotTestId()
    {
        $result = $this
            ->getEntityManager()
            ->createQueryBuilder()
            ->select('MAX(mts.motTest)')
            ->from(MotTestSurvey::class, 'mts')
            ->setMaxResults(1)
            ->getQuery()
            ->getSingleScalarResult();

        if (!$result) {
            throw new NotFoundException(MotTestSurvey::class);
        }

        return (int) $result;
    }
}
