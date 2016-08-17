<?php
/**
 * This file is part of the DVSA MOT API project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace DvsaMotApi\Service;

use DateTime;
use Doctrine\ORM\EntityManager;
use DvsaCommon\Enum\MotTestTypeCode;
use DvsaCommonApi\Service\Exception\BadRequestException;
use DvsaCommonApi\Service\Exception\NotFoundException;
use DvsaEntities\Entity\MotTest;
use DvsaEntities\Entity\MotTestSurvey;
use DvsaEntities\Entity\Survey;
use DvsaEntities\Repository\Doctrine\DoctrineMotTestSurveyRepository;
use DvsaEntities\Repository\MotTestRepository;
use DvsaEntities\Repository\MotTestSurveyRepository;
use DvsaEntities\Repository\SurveyRepository;
use DvsaMotApi\Domain\Survey\SurveyConfiguration;
use DvsaMotApi\Service\S3\FileStorageInterface;
use DvsaMotApi\Service\S3\S3CsvStore;
use InvalidArgumentException;
use Zend\Authentication\AuthenticationService;

/**
 * Class SurveyService.
 */
class SurveyService
{
    /**
     * @var AuthenticationService
     */
    private $authenticationService;

    /**
     * @var FileStorageInterface
     */
    private $surveyStore;

    /**
     * @var SurveyConfiguration
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

    private $validSurveyValues = [1, 2, 3, 4, 5];

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * SurveyService constructor.
     *
     * @param EntityManager         $entityManager
     * @param AuthenticationService $authService
     * @param S3CsvStore            $surveyStore
     * @param SurveyConfiguration   $surveyConfig
     */
    public function __construct(EntityManager $entityManager, AuthenticationService $authService,
                                S3CsvStore $surveyStore, SurveyConfiguration $surveyConfig)
    {
        $this->entityManager = $entityManager;
        $this->authenticationService = $authService;
        $this->surveyStore = $surveyStore;
        $this->surveyConfig = $surveyConfig;
    }

    /**
     * @param array $data
     *
     * $data contains 'token' and 'satisfaction_rating' keys
     *
     * @throws BadRequestException
     * @throws NotFoundException
     *
     * @return array
     */
    public function createSurveyResult(array $data)
    {
        if (!array_key_exists('token', $data)) {
            throw new BadRequestException('Survey token not provided', BadRequestException::ERROR_CODE_INVALID_SURVEY_TOKEN);
        }

        if (!array_key_exists('satisfaction_rating', $data)) {
            throw new BadRequestException('Satisfaction rating not provided', BadRequestException::ERROR_CODE_INVALID_SURVEY_TOKEN);
        }

        // validate token
        if (!$this->sessionTokenIsValid($data['token'])) {
            throw new BadRequestException('Survey token is not valid', BadRequestException::ERROR_CODE_INVALID_SURVEY_TOKEN);
        }

        /** @var DoctrineMotTestSurveyRepository $motTestSurveyRepository */
        $motTestSurveyRepository = $this->entityManager->getRepository(MotTestSurvey::class);

        /** @var MotTestSurvey $motTestSurvey */
        $motTestSurvey = $motTestSurveyRepository->findByToken($data['token']);

        if ($motTestSurvey === null) {
            throw new NotFoundException(MotTestSurvey::class);
        }
        $motTest = $motTestSurvey->getMotTest();

        if ($motTest !== null) {
            $rating = $data['satisfaction_rating'];
            if ($this->isValidRating($rating)) {
                $surveyResult = new Survey($data['satisfaction_rating']);
                $motTestSurvey->setSurvey($surveyResult);

                $this->entityManager->persist($surveyResult);
                $this->entityManager->persist($motTestSurvey);
                $this->entityManager->flush();

                return ['satisfaction_rating' => $rating];
            }
            throw new InvalidArgumentException('Invalid survey result provided');
        }
        throw new NotFoundException(MotTest::class);
    }

    /**
     * @param $motTestTypeCode
     * @param $testerId
     *
     * @return bool
     */
    public function shouldDisplaySurvey($motTestTypeCode, $testerId)
    {
        if ($motTestTypeCode !== MotTestTypeCode::NORMAL_TEST) {
            return false;
        } else {
            $displaySurvey = !$this->surveyHasBeenDoneBefore() || (
                $this->nextSurveyShouldBeDisplayed() &&
                !$this->userHasCompletedSurveyInExclusionPeriod($testerId)
            );

            return $displaySurvey;
        }
    }

    /**
     * @return bool
     */
    private function surveyHasBeenDoneBefore()
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('COUNT(sr)')
            ->from(MotTestSurvey::class, 'sr');

        $motTestSurveysCount = (int) $queryBuilder->getQuery()->getSingleScalarResult();

        return $motTestSurveysCount > 0;
    }

    /**
     * @return bool
     */
    private function nextSurveyShouldBeDisplayed()
    {
        $testsBetweenSurvey = $this->surveyConfig->getNumberOfTestsBetweenSurveys();

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
     * Return whether the user has completed a survey in the last $time.
     *
     * @param int $testerId
     *
     * @return bool
     */
    private function userHasCompletedSurveyInExclusionPeriod($testerId)
    {
        $timeBetweenSurveys = $this->surveyConfig->getTimeBeforeSurveyRedisplayed();

        /** @var MotTestSurveyRepository $motTestSurveyRepository */
        $motTestSurveyRepository = $this->entityManager->getRepository(MotTestSurvey::class);

        try {
            $lastSurveyDate = $motTestSurveyRepository->getLastUserSurveyDate($testerId);
            $latestSurvey = new DateTime($lastSurveyDate);
            $beginningOfWindow = new DateTime(date('Y-m-d', strtotime('-' . $timeBetweenSurveys)));

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
        /** @var SurveyRepository $surveyRepository */
        $surveyRepository = $this->entityManager->getRepository(Survey::class);

        return $surveyRepository->findByRating($rating);
    }

    /**
     * @param array $surveyData
     *
     * @return \Aws\Result
     */
    public function generateSurveyReports($surveyData)
    {
        $timeStamp = new DateTime();
        $row['timestamp'] = $timeStamp->format('Y-m-d-H-i-s');
        $row['period'] = 'month';
        $row['slug'] = 'https://mot-testing.i-env.net/';
        $row['rating_1'] = $surveyData['rating_1'];
        $row['rating_2'] = $surveyData['rating_2'];
        $row['rating_3'] = $surveyData['rating_3'];
        $row['rating_4'] = $surveyData['rating_4'];
        $row['rating_5'] = $surveyData['rating_5'];
        $row['total'] = $surveyData['total'];

        $result = $this->surveyStore->putFile(self::$CSV_COLUMNS, $row, $timeStamp->format('Y-m'));

        return $result;
    }

    /**
     * @return array containing keys 'month', 'size', and 'csv'
     */
    public function getSurveyReports()
    {
        $files = $this->surveyStore->getAllFiles();

        $reportAggregate = [];
        $reportData = [];

        foreach ($files as $file) {
            $reportData['month'] = $this->surveyStore->stripRootFolderFromKey($file['Key']);
            $reportData['size'] = $file['Size'];
            $reportData['csv'] = (string) $this->surveyStore->getFile($file['Key']);
            array_push($reportAggregate, $reportData);
        }

        return $reportAggregate;
    }

    /**
     * @param int $motTestNumber
     *
     * @return string
     */
    public function createSessionToken($motTestNumber)
    {
        $motTestRepository = $this->entityManager->getRepository(MotTest::class);
        $motTest = $motTestRepository->findOneByNumber($motTestNumber);

        $motTestSurvey = new MotTestSurvey($motTest);

        $this->entityManager->persist($motTestSurvey);
        $this->entityManager->flush();

        return $motTestSurvey->getToken();
    }

    /**
     * @param string $token
     * 
     * @return bool
     */
    public function sessionTokenIsValid($token)
    {
        $motTestSurveyRepository = $this->entityManager->getRepository(MotTestSurvey::class);

        /** @var MotTestSurvey $motTestSurvey */
        $motTestSurvey = $motTestSurveyRepository->findOneBy(['token' => $token]);

        // If there is no corresponding MotTestSurvey or it already has an associated Survey, token is invalid.
        if (null === $motTestSurvey || $motTestSurvey->getSurvey() !== null) {
            return false;
        }

        return true;
    }

    /**
     * @param int|null $rating
     *
     * @return bool
     */
    private function isValidRating($rating)
    {
        return $rating === null || in_array($rating, $this->validSurveyValues);
    }
}
