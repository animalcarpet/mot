<?php

namespace TestSupport\Service;

use Doctrine\ORM\EntityManager;
use DvsaEntities\Entity\MotTestSurveyResult;
use Doctrine\ORM\Query\ResultSetMapping;

class GdsSurveyService
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Generate 1 fewer test than is required to display the GDS survey upon completion of a normal MOT test
     */
    public function generateMotTestsToDisplaySurveyOnNextTest()
    {
        $dbConnection = $this->entityManager->getConnection();
        $storedProcedure = $dbConnection->prepare("CALL generate_mot_tests_for_survey()");
        $storedProcedure->execute();
    }

    /**
     * Return the number of surveys completed
     * @return int
     */
    public function getNumberOfSurveysCompleted()
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder->select('COUNT(sr)')
            ->from(MotTestSurveyResult::class, 'sr');

        return (int)$queryBuilder->getQuery()->getSingleScalarResult();
    }

    public function deleteAllSurveys()
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder->delete(MotTestSurveyResult::class, 'mt');

        $queryBuilder->getQuery()->execute();
    }
}
