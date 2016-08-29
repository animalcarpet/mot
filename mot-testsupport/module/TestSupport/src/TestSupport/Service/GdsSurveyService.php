<?php

namespace TestSupport\Service;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManager;
use DvsaCommon\HttpRestJson\Client;
use TestSupport\Helper\TestSupportAccessTokenManager;
use DvsaEntities\Entity\MotTestSurvey;
use Doctrine\ORM\Query\ResultSetMapping;
use DvsaEntities\Entity\Survey;

class GdsSurveyService
{
    const SURVEY_REPORTS_GENERATE_PATH = 'survey/reports/generate';

    private $entityManager;
    private $cronUserService;
    private $accessTokenManager;
    private $client;

    public function __construct(EntityManager $entityManager,
        TestSupportAccessTokenManager $accessTokenManager,
        CronUserService $cronUserService,
        Client $client)
    {
        $this->entityManager = $entityManager;
        $this->cronUserService = $cronUserService;
        $this->accessTokenManager = $accessTokenManager;
        $this->client = $client;
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
            ->from(Survey::class, 'sr');

        return (int)$queryBuilder->getQuery()->getSingleScalarResult();
    }

    public function deleteAllSurveys()
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder->delete(MotTestSurvey::class, 'mt');

        $queryBuilder->getQuery()->execute();

        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder->delete(Survey::class, 'mt');

        $queryBuilder->getQuery()->execute();
    }

    /**
     * @param string $surveyToken
     * @param int    $motTestId
     */
    public function persistTokenToDb($surveyToken, $motTestId)
    {
        $sql = 'INSERT INTO mot_test_survey(mot_test_id, token, created_by, created_on)' .
            ' VALUES(?, ?, (SELECT `id` FROM `person` WHERE `username` = \'static data\' OR `user_reference` = \'Static Data\'), CURRENT_TIMESTAMP(6))';
        $query = $this->entityManager->getConnection();
        $query->executeUpdate($sql, [$motTestId, $surveyToken]);
    }

    /**
     * @param string $token
     * @param array  $motTestDetails
     * @return bool
     */
    public function tokenExistsForTest($token, $motTestDetails)
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder->select('mt.number')
            ->from(MotTestSurvey::class, 'mts')
            ->join('mts.motTest', 'mt')
            ->where('mts.token = :token');

        $queryBuilder->setParameter('token', $token);

        $result = $queryBuilder->getQuery()->getSingleScalarResult();
        return $result !== null && $motTestDetails['motTestNumber'] === $result;
    }

    public function getTokenForMot($motTestNumber)
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder->select('mts.token')
            ->from(MotTestSurvey::class, 'mts')
            ->join('mts.motTest', 'mt')
            ->where('mt.number = :motTestNumber');

        $queryBuilder->setParameter('motTestNumber', $motTestNumber);

        $result = $queryBuilder->getQuery()->getSingleScalarResult();
        return $result;
    }

    public function generateSurveyReports()
    {
        $cronUser = $this->cronUserService->create([]);
        $token = $this->accessTokenManager->getToken(
            $cronUser->data['username'],
            $cronUser->data['password']
        );

        $this->client->setAccessToken($token);

        return $this->client->get(self::SURVEY_REPORTS_GENERATE_PATH);
    }
}
