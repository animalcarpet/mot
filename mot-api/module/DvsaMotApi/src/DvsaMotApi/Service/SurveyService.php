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
use DvsaMotApi\Domain\Survey\SurveyConfiguration;
use DvsaMotApi\Domain\Survey\SurveyToken;
use DvsaMotApi\Service\S3\FileStorageInterface;
use DvsaMotApi\Service\S3\S3CsvStore;
use InvalidArgumentException;
use Zend\Authentication\AuthenticationService;

/**
 * Survey Service.
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

    /**
     * @var array
     */
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

    /**
     * @var array
     */
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
     * Flag that a specific Survey has been presented to the user.
     *
     * @param string $surveyToken
     *
     * @throws BadRequestException
     * @throws NotFoundException
     */
    public function markSurveyAsPresented($surveyToken)
    {
        if (empty($surveyToken) || !SurveyToken::isValid($surveyToken)) {
            throw new BadRequestException('Survey token is not valid', BadRequestException::ERROR_CODE_INVALID_SURVEY_TOKEN);
        }

        /** @var DoctrineMotTestSurveyRepository $motTestSurveyRepository */
        $motTestSurveyRepository = $this->entityManager->getRepository(MotTestSurvey::class);

        /** @var MotTestSurvey $motTestSurvey */
        $motTestSurvey = $motTestSurveyRepository->findOneByToken($surveyToken);
        if ($motTestSurvey === null) {
            throw new NotFoundException(MotTestSurvey::class);
        }

        $motTestSurvey->setHasBeenPresented(true);
        $this->entityManager->flush();
    }

    /**
     * @param string $surveyToken
     *
     * @throws BadRequestException
     * @throws NotFoundException
     *
     * @return bool
     */
    public function hasBeenPresented($surveyToken)
    {
        if (empty($surveyToken) || !SurveyToken::isValid($surveyToken)) {
            throw new BadRequestException('Survey token is not valid', BadRequestException::ERROR_CODE_INVALID_SURVEY_TOKEN);
        }

        /** @var DoctrineMotTestSurveyRepository $motTestSurveyRepository */
        $motTestSurveyRepository = $this->entityManager->getRepository(MotTestSurvey::class);

        /** @var MotTestSurvey $motTestSurvey */
        $motTestSurvey = $motTestSurveyRepository->findOneByToken($surveyToken);
        if ($motTestSurvey === null) {
            throw new NotFoundException(MotTestSurvey::class);
        }

        return $motTestSurvey->hasBeenPresented();
    }

    /**
     * @param array $data Contains 'token' and 'satisfaction_rating' keys.
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

        // Validate token.
        if (!$this->sessionTokenIsValid($data['token'])) {
            throw new BadRequestException('Survey token is not valid', BadRequestException::ERROR_CODE_INVALID_SURVEY_TOKEN);
        }

        /** @var DoctrineMotTestSurveyRepository $motTestSurveyRepository */
        $motTestSurveyRepository = $this->entityManager->getRepository(MotTestSurvey::class);

        /** @var MotTestSurvey $motTestSurvey */
        $motTestSurvey = $motTestSurveyRepository->findOneByToken($data['token']);
        $motTest = $motTestSurvey->getMotTest();

        if ($motTest !== null) {
            $rating = $data['satisfaction_rating'];
            if ($this->isValidRating($rating)) {
                $surveyResult = new Survey($data['satisfaction_rating']);
                $motTestSurvey->setHasBeenPresented(true);
                $motTestSurvey->setHasBeenSubmitted(true);

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
     * @param int    $motTestId
     * @param string $motTestTypeCode
     * @param int    $testerId
     *
     * @return bool
     */
    public function shouldDisplaySurvey($motTestId, $motTestTypeCode, $testerId)
    {
        if (!is_int($motTestId) || !is_string($motTestTypeCode) || !is_int($testerId)) {
            return false;
        }

        if ($motTestTypeCode !== MotTestTypeCode::NORMAL_TEST) {
            return false;
        }

        /** @var MotTestSurveyRepository $motTestSurveyRepository */
        $motTestSurveyRepository = $this->entityManager->getRepository(MotTestSurvey::class);
        try {
            $lastSurveyMotTestId = $motTestSurveyRepository->getLastSurveyMotTestId();
        } catch (NotFoundException $e) {
            return true;
        }

        return $this->nextSurveyShouldBeDisplayed($motTestId, $lastSurveyMotTestId) &&
                !$this->userHasCompletedSurveyInExclusionPeriod($testerId);
    }

    /**
     * @param int $motTestId
     * @param int $lastSurveyMotTestId
     *
     * @return bool
     */
    private function nextSurveyShouldBeDisplayed($motTestId, $lastSurveyMotTestId)
    {
        $testsBetweenSurvey = $this->surveyConfig->getNumberOfTestsBetweenSurveys();
        $dbAutoIncrementIncrement = $this->surveyConfig->getDbAutoIncrementIncrement();

        /*
         * If the difference between $lastSurveyMotTestId and (current) $motTestId is less than $testsBetweenSurvey
         * don't bother asking the DB for the number of Normal Tests since the last recorded Mot Test Id in Survey
         * table.
         */
        if (($motTestId / $dbAutoIncrementIncrement) < ($lastSurveyMotTestId / $dbAutoIncrementIncrement) + $testsBetweenSurvey) {
            return false;
        }

        /** @var MotTestRepository $motTestRepository */
        $motTestRepository = $this->entityManager->getRepository(MotTest::class);
        try {
            $testCount = $motTestRepository->getNormalMotTestCountSinceLastSurvey($lastSurveyMotTestId);

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
        $surveyRepository = $this->entityManager->getRepository(Survey::class);

        return $surveyRepository->findBy(['rating' => $rating]);
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
        $motTest = $motTestRepository->findOneBy(['number' => $motTestNumber]);

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
        if (true !== SurveyToken::isValid($token)) {
            return false;
        }

        /** @var MotTestSurveyRepository $motTestSurveyRepository */
        $motTestSurveyRepository = $this->entityManager->getRepository(MotTestSurvey::class);

        /** @var MotTestSurvey $motTestSurvey */
        $motTestSurvey = $motTestSurveyRepository->findOneByToken($token);

        // If there is no corresponding MotTestSurvey or it already has an associated Survey, token is invalid.
        if (null === $motTestSurvey || true === $motTestSurvey->hasBeenSubmitted()) {
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
