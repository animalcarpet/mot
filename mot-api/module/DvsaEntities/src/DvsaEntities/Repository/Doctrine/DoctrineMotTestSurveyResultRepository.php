<?php

namespace DvsaEntities\Repository\Doctrine;

use Doctrine\ORM\Query\Expr\Join;
use DvsaCommonApi\Service\Exception\NotFoundException;
use DvsaEntities\Entity\MotTest;
use DvsaEntities\Entity\MotTestSurveyResult;
use DvsaEntities\Repository\MotTestSurveyResultRepository;
use Doctrine\ORM\EntityRepository;

class DoctrineMotTestSurveyResultRepository extends EntityRepository implements MotTestSurveyResultRepository
{
    /**
     * @param int|string $motTestId
     * @return MotTestSurveyResult
     */
    public function findByMotTestId($motTestId)
    {
        $motRepository = $this->_em->getRepository(MotTest::class);

        /** @var MotTest $motTest */
        $motTest = $motRepository->findById($motTestId);

        /** @var MotTestSurveyResult $motTestSurveyResult */
        $motTestSurveyResult = $this->findByMotTest($motTest);

        return $motTestSurveyResult->getSurveyResult();
    }

    /**
     * @param string $userId
     * @return string
     * @throws NotFoundException
     */
    public function getLastUserSurveyDate($userId)
    {
        $queryBuilder = $this->_em->createQueryBuilder();

        $queryBuilder->select('COALESCE(sr.lastUpdatedOn, sr.createdOn) as updateDate')
            ->from(MotTestSurveyResult::class, 'sr')
            ->where('sr.createdBy = :userId')
            ->orWhere('sr.lastUpdatedBy = :userId')
            ->orderBy('updateDate', 'DESC');

        $queryBuilder->setParameter('userId', $userId);
        $queryResults = $queryBuilder->getQuery()->getResult();

        if (!empty($queryResults)) {
            return $queryResults[0]['updateDate'];
        } else {
            throw new NotFoundException(MotTestSurveyResult::class);
        }
    }

    /**
     * @return MotTest
     * @throws NotFoundException
     */
    public function getLastUserSurveyTest()
    {
        $queryBuilder = $this->_em->createQueryBuilder();

        $queryBuilder->select('mt')
            ->from(MotTestSurveyResult::class, 'sr')
            ->join(MotTest::class, 'mt', Join::INNER_JOIN, 'sr.motTest = mt.id')
            ->orderBy('sr.id', 'DESC');

        $queryResults = $queryBuilder->getQuery()->getResult();

        if (!empty($queryResults)) {
            return $queryResults[0];
        } else {
            throw new NotFoundException(MotTestSurveyResult::class);
        }
    }
}
