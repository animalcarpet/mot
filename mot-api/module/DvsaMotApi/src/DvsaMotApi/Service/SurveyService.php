<?php

namespace DvsaMotApi\Service;

use Aws\S3\S3Client;
use Doctrine\ORM\EntityManager;
use DvsaCommon\Enum\MotTestTypeCode;
use DvsaCommonApi\Service\AbstractService;
use DvsaCommonApi\Service\Exception\NotFoundException;
use DvsaEntities\Entity\MotTestSurveyResult;
use \DvsaEntities\Entity\MotTest;
use DvsaEntities\Repository\MotTestRepository;
use DvsaEntities\Repository\MotTestSurveyResultRepository;
use Zend\Authentication\AuthenticationService;

/**
 * Class SurveyService.
 */
class SurveyService extends AbstractService
{
    /**
     * @var AuthenticationService
     */
    private $authenticationService;

    /**
     * @var S3Client
     */
    private $s3Client;

    /**
     * @var string
     */
    private $bucket;

    /**
     * @var array
     */
    private $surveyConfig;

    public static $CSV_COLUMNS = [
        'timestamp',
        'period',
        'slug',
        'rating_1',
        'rating_2',
        'rating_3',
        'rating_4',
        'rating_5',
        'total',
    ];

    private $valid_survey_values = [1, 2, 3, 4, 5];

    /**
     * SurveyService constructor.
     *
     * @param EntityManager         $entityManager
     * @param AuthenticationService $authService
     * @param S3Client              $s3Client
     * @param string                $bucket
     * @param array                 $surveyConfig
     */
    public function __construct(
        EntityManager $entityManager,
        AuthenticationService $authService,
        S3Client $s3Client,
        $bucket,
        array $surveyConfig
    ) {
        parent::__construct($entityManager);
        $this->authenticationService = $authService;
        $this->s3Client = $s3Client;
        $this->bucket = $bucket;
        $this->surveyConfig = $surveyConfig;
    }

    /**
     * @param array $data
     *
     * @return array
     */
    public function createSurveyResult(array $data)
    {
        $surveyResult = new MotTestSurveyResult();

        /** @var MotTestRepository $motTestRepository */
        $motTestRepository = $this->entityManager->getRepository(MotTest::class);
        
        /** @var MotTest $motTest */
        $motTest = $motTestRepository->findByNumber($data['mot_test_number'])[0];

        $currentUserId = $this->authenticationService->getIdentity()->getUserId();

        if ($motTest !== null && $motTest->getTester()->getId() === $currentUserId) {

            $surveyResult->setMotTest($motTest);
            if (in_array($data['satisfaction_rating'], $this->valid_survey_values)) {
                $surveyResult->setSurveyResult($data['satisfaction_rating']);
            }
            $this->entityManager->persist($surveyResult);
            $this->entityManager->flush();

            return ['satisfaction_rating' => $surveyResult->getSurveyResult()];
        }
        throw new NotFoundException(MotTest::class);
    }

    /**
     * @param \StdClass $motTestDetails
     * @return bool
     */
    public function shouldDisplaySurvey($motTestDetails)
    {
        $motTestTypeCode = $motTestDetails->testType->code;

        if ($motTestTypeCode !== MotTestTypeCode::NORMAL_TEST) {
            return false;
        } else {
            $displaySurvey = !$this->surveyHasBeenDoneBefore() ||
                $this->nextSurveyShouldBeDisplayed() &&
                !$this->userHasCompletedSurveyInExclusionPeriod($motTestDetails->tester->id);

            return $displaySurvey;
        }
    }

    /**
     * @return bool
     */
    private function surveyHasBeenDoneBefore()
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();

        $queryBuilder->select('COUNT(sr)')
            ->from(MotTestSurveyResult::class, 'sr');

        return $queryBuilder->getQuery()->getSingleScalarResult() > 0;
    }

    /**
     * @param int $completedTestId
     * @return bool
     */
    private function nextSurveyShouldBeDisplayed()
    {
        $testsBetweenSurvey = $this->surveyConfig['numberOfTestsBetweenSurveys'];

        /** @var MotTestRepository $motTestRepository */
        $motTestRepository = $this->entityManager->getRepository(MotTest::class);

        try {
            $testCount = $motTestRepository->getNormalMotTestCountSinceLastSurvey();

            return $testCount >= $testsBetweenSurvey;
        } catch (NotFoundException $e) {
            return false;
        }
    }

    /**
     * Return whether the user has completed a survey in the last $time
     *
     * @param string $testerId
     * @return bool
     */
    private function userHasCompletedSurveyInExclusionPeriod($testerId)
    {
        $timeBetweenSurveys = $this->surveyConfig['timeBeforeSurveyRedisplayed'];

        /** @var MotTestSurveyResultRepository $motTestSurveyRepository */
        $motTestSurveyRepository = $this->entityManager->getRepository(MotTestSurveyResult::class);

        try {
            $lastSurveyDate = $motTestSurveyRepository->getLastUserSurveyDate($testerId);
            $latestSurvey = new \DateTime($lastSurveyDate);
            $beginningOfWindow = new \DateTime(
                date('Y-m-d', strtotime('-' . $timeBetweenSurveys))
            );

            return $latestSurvey >= $beginningOfWindow;
        } catch (NotFoundException $e) {
            return false;
        }
    }

    /**
     * @param $rating
     *
     * @return array
     */
    public function getSurveyResultSatisfactionRatingsCount($rating)
    {
        $surveyRepository = $this->entityManager->getRepository(MotTestSurveyResult::class);

        return $surveyRepository->findBySurveyResult($rating);
    }

    /**
     * @param array $surveyData
     *
     * @return \Aws\Result
     */
    public function generateSurveyReports($surveyData)
    {
        $csvHandle = fopen('php://memory', 'r+');

        if (!empty(self::$CSV_COLUMNS)) {
            fputcsv($csvHandle, self::$CSV_COLUMNS);
        }

        $timeStamp = new \DateTime();
        $row['timestamp'] = $timeStamp->format('Y-m-d-H-i-s');
        $row['period'] = 'month';
        $row['slug'] = 'https://mot-testing.i-env.net/';
        $row['rating_1'] = $surveyData['rating_1'];
        $row['rating_2'] = $surveyData['rating_2'];
        $row['rating_3'] = $surveyData['rating_3'];
        $row['rating_4'] = $surveyData['rating_4'];
        $row['rating_5'] = $surveyData['rating_5'];
        $row['total'] = $surveyData['total'];

        fputcsv($csvHandle, $row);
        rewind($csvHandle);

        $result = $this->s3Client->putObject(
            [
                'Bucket' => $this->bucket,
                'Key' => $timeStamp->format('Y-m'),
                'Body' => stream_get_contents($csvHandle),
                'ContentType' => 'text/csv',
            ]
        );

        fclose($csvHandle);

        return $result;
    }

    /**
     * @return array containing keys 'month', 'size', and 'csv'
     */
    public function getSurveyReports()
    {
        $objects = $this->s3Client->getIterator(
            'ListObjects', [
                'Bucket' => $this->bucket,
            ]
        );

        $results = [];

        foreach ($objects as $object) {
            $result['month'] = $object['Key'];
            $result['size'] = $object['Size'];
            $result['csv'] = (string) $this->s3Client->getObject(
                [
                    'Bucket' => $this->bucket,
                    'Key' => $object['Key'],
                ]
            )['Body'];
            $results[] = $result;
        }

        return $results;
    }
}
