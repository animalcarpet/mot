<?php
/**
 * This file is part of the DVSA MOT API project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace DvsaMotApiTest\Service;

use DateTime;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
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
use DvsaMotApi\Service\SurveyService;
use InvalidArgumentException;
use PHPUnit_Framework_MockObject_MockObject;
use PHPUnit_Framework_TestCase;
use Zend\Authentication\AuthenticationService;

class SurveyServiceTest extends PHPUnit_Framework_TestCase
{
    const TOKEN = '123e4567-e89b-12d3-a456-426655440000';

    /**
     * @var EntityManager|PHPUnit_Framework_MockObject_MockObject
     */
    private $entityManager;

    /**
     * @var FileStorageInterface|PHPUnit_Framework_MockObject_MockObject
     */
    private $fileStorageMock;

    private $surveyResults;

    /**
     * @var MotTestRepository|PHPUnit_Framework_MockObject_MockObject
     */
    private $motRepository;

    /**
     * @var MotTestSurveyRepository|PHPUnit_Framework_MockObject_MockObject
     */
    private $motTestSurveyRepository;

    /**
     * @var AuthenticationService|PHPUnit_Framework_MockObject_MockObject
     */
    private $authenticationServiceMock;

    /**
     * @var MotTest|PHPUnit_Framework_MockObject_MockObject
     */
    private $motTestMock;

    /**
     * @var MotTestSurvey|PHPUnit_Framework_MockObject_MockObject
     */
    private $motTestSurveyMock;

    /**
     * @var EntityRepository|PHPUnit_Framework_MockObject_MockObject
     */
    private $surveyRepository;

    /**
     * @var Survey|PHPUnit_Framework_MockObject_MockObject
     */
    private $surveyMock;

    /**
     * @var SurveyConfiguration
     */
    private $surveyConfig;

    public function setUp()
    {
        $this->entityManager = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'createQueryBuilder', 'getQuery', 'getSingleScalarResult',
                    'getResult', 'persist', 'flush', 'select', 'from', 'where',
                    'andWhere', 'orWhere', 'orderBy', 'getRepository',
                    'setParameter',
                ]
            )
            ->getMock();

        $this->fileStorageMock = $this->getMockBuilder(S3CsvStore::class)
            ->disableOriginalConstructor()
            ->setMethods(['getFile', 'getAllFiles', 'putFile'])
            ->getMock();

        $this->surveyConfig = new SurveyConfiguration([
            'dbAutoIncrementIncrement' => 2,
            'numberOfTestsBetweenSurveys' => 1,
            'timeBeforeSurveyRedisplayed' => '1 second',
        ]);

        $this->motRepository = $this
            ->getMockBuilder(MotTestRepository::class)
            ->disableOriginalConstructor()
            ->setMethods(['getNormalMotTestCountSinceLastSurvey', 'findOneBy'])
            ->getMock();

        $this->motTestSurveyRepository = $this
            ->getMockBuilder(DoctrineMotTestSurveyRepository::class)
            ->disableOriginalConstructor()
            ->setMethods(['getLastSurveyMotTestId', 'getLastUserSurveyDate', 'findOneByToken'])
            ->getMock();

        $this->authenticationServiceMock = $this
            ->getMockBuilder(AuthenticationService::class)
            ->disableOriginalConstructor()
            ->setMethods(['getIdentity', 'getUserId'])
            ->getMock();

        $this->motTestMock = $this
            ->getMockBuilder(MotTest::class)
            ->disableOriginalConstructor()
            ->setMethods(['getTester', 'getId'])
            ->getMock();

        $this->motTestSurveyMock = $this
            ->getMockBuilder(MotTestSurvey::class)
            ->disableOriginalConstructor()
            ->setMethods(['hasBeenPresented', 'hasBeenSubmitted', 'getMotTest'])
            ->getMock();

        $this->surveyRepository = $this
            ->getMockBuilder(EntityRepository::class)
            ->disableOriginalConstructor()
            ->setMethods(['findBy'])
            ->getMock();

        $this->surveyMock = $this
            ->getMockBuilder(Survey::class)
            ->disableOriginalConstructor()
            ->setMethods(['getRating'])
            ->getMock();
    }

    public function testMarkSurveyAsBeenPresentedWithMissingTokenThrowsBadRequest()
    {
        $this->setExpectedException(BadRequestException::class);

        $surveyService = $this->createSurveyService();
        $surveyService->markSurveyAsPresented([]);
    }

    public function testMarkSurveyAsBeenPresentedWithInvalidTokenThrowsBadRequest()
    {
        $this->setExpectedException(BadRequestException::class);

        $surveyService = $this->createSurveyService();
        $surveyService->markSurveyAsPresented('not-a-token');
    }

    public function testMarkSurveyAsBeenPresentedWithTokenMissingFromDbThrowsException()
    {
        $this->setExpectedException(NotFoundException::class);

        $this
            ->motTestSurveyRepository
            ->expects($this->once())
            ->method('findOneByToken')
            ->willReturn(null);

        $this
            ->entityManager
            ->expects($this->once())
            ->method('getRepository')
            ->will($this->returnValueMap([
                [MotTestSurvey::class, $this->motTestSurveyRepository],
            ]));

        $surveyService = $this->createSurveyService();
        $surveyService->markSurveyAsPresented(self::TOKEN);
    }

    public function testMarkSurveyAsBeenPresented()
    {
        $this
            ->motTestSurveyRepository
            ->expects($this->once())
            ->method('findOneByToken')
            ->willReturn($this->motTestSurveyMock);

        $this
            ->entityManager
            ->expects($this->once())
            ->method('getRepository')
            ->will($this->returnValueMap([
                [MotTestSurvey::class, $this->motTestSurveyRepository],
            ]));

        $surveyService = $this->createSurveyService();
        $surveyService->markSurveyAsPresented(self::TOKEN);
    }

    /**
     * @return array
     */
    public function testCreateSurveyResultProvider()
    {
        return [
            [null],
            [1],
            [2],
            [3],
            [4],
            [5],
            [6],
        ];
    }

    /**
     * @dataProvider testCreateSurveyResultProvider
     *
     * @param $satisfactionRating
     */
    public function testCreateSurveyResult($satisfactionRating)
    {
        $validSurveyValues = [null, 1, 2, 3, 4, 5];
        if (!in_array($satisfactionRating, $validSurveyValues)) {
            $this->setExpectedException(InvalidArgumentException::class);
        }

        $this
            ->entityManager->expects($this->any())
            ->method('getRepository')
            ->will($this->returnValueMap([
                [MotTest::class, $this->motRepository],
                [MotTestSurvey::class, $this->motTestSurveyRepository],
            ]));

        $this->motTestSurveyRepository
            ->expects($this->atLeastOnce())
            ->method('findOneByToken')
            ->willReturn($this->motTestSurveyMock);

        $this->motTestSurveyMock
            ->expects($this->once())
            ->method('getMotTest')
            ->willReturn($this->motTestMock);

        $this->motTestSurveyMock
            ->expects($this->any())
            ->method('hasBeenPresented')
            ->willReturn(false);

        $this->surveyMock
            ->expects($this->any())
            ->method('getRating')
            ->willReturn($satisfactionRating);

        $service = $this->createSurveyService();

        if (!in_array($satisfactionRating, $validSurveyValues)) {
            $this->setExpectedException('InvalidArgumentException');
        }

        $surveyResult = $service->createSurveyResult([
            'token' => self::TOKEN,
            'satisfaction_rating' => $satisfactionRating,
        ]);

        $this->assertEquals($satisfactionRating, $surveyResult['satisfaction_rating']);
    }

    public function testCreateSurveyResultWithMissingMotTestThrowsException()
    {
        $this->setExpectedException(NotFoundException::class);

        $satisfactionRating = 1;

        $this
            ->entityManager->expects($this->any())
            ->method('getRepository')
            ->will($this->returnValueMap([
                [MotTest::class, $this->motRepository],
                [MotTestSurvey::class, $this->motTestSurveyRepository],
            ]));

        $this->motTestSurveyRepository
            ->expects($this->atLeastOnce())
            ->method('findOneByToken')
            ->willReturn($this->motTestSurveyMock);

        $this->motTestSurveyMock
            ->expects($this->once())
            ->method('getMotTest')
            ->willReturn(null);

        $this->motTestSurveyMock
            ->expects($this->any())
            ->method('hasBeenPresented')
            ->willReturn(false);

        $this->surveyMock
            ->expects($this->any())
            ->method('getRating')
            ->willReturn($satisfactionRating);

        $service = $this->createSurveyService();

        $surveyResult = $service->createSurveyResult([
            'token' => self::TOKEN,
            'satisfaction_rating' => $satisfactionRating,
        ]);
    }

    public function testCreateSurveyResultWithMissingTokenThrowsException()
    {
        $this->setExpectedException(BadRequestException::class);

        $service = $this->createSurveyService();

        $surveyResult = $service->createSurveyResult([
            'satisfaction_rating' => 1,
        ]);
    }

    public function testCreateSurveyResultWithMissingSatisfactionRatingThrowsException()
    {
        $this->setExpectedException(BadRequestException::class);

        $service = $this->createSurveyService();

        $surveyResult = $service->createSurveyResult([
            'token' => self::TOKEN,
        ]);
    }

    public function testCreateSurveyResultWithInvalidTokenThrowsException()
    {
        $this->setExpectedException(BadRequestException::class);

        $service = $this->createSurveyService();

        $surveyResult = $service->createSurveyResult([
            'token' => 'not-a-token',
            'satisfaction_rating' => 1,
        ]);
    }

    public function testGetSurveyResultSatisfactionRatingsCount()
    {
        $this
            ->surveyRepository
            ->expects($this->atLeastOnce())
            ->method('findBy')
            ->willReturn(666);

        $this
            ->entityManager->expects($this->any())
            ->method('getRepository')
            ->will($this->returnValueMap([
                [Survey::class, $this->surveyRepository],
            ]));

        $this->assertEquals(666, $this->createSurveyService()->getSurveyResultSatisfactionRatingsCount(5));
    }

    /**
     * @group survey_report_generation
     * @group integration
     */
    public function testGeneratingSurveyReports()
    {
        $this->markTestSkipped('To be updated post BL-3741');

        $datetime = new DateTime();

        $row = [
            'timestamp' => $datetime->format('Y-m'),
            'period' => 'month',
            'slug' => 'https://mot-testing.i-env.net/',
            'rating_1' => 1,
            'rating_2' => 2,
            'rating_3' => 3,
            'rating_4' => 4,
            'rating_5' => 5,
            'total' => 15,
        ];

        $this
            ->fileStorageMock
            ->expects($this->once())
            ->method('putFile')
            ->with(SurveyService::$CSV_COLUMNS, $row, sprintf('%s.csv', $datetime->format('Y-m')));

        $service = $this->withSurveyResults()->createSurveyService();

        $service->generateSurveyReports($datetime->format('Y'), $datetime->format('m'));
    }

    public function testGetSurveyReports()
    {
        $this->markTestSkipped('To be updated post BL-3741');

        $key = '2016-04';
        $size = '100kb';
        $csvData = 'csvData';

        $mockAwsResult = [
            ['Key' => $key, 'Size' => $size],
        ];
        $fileMock = $csvData;

        $this
            ->fileStorageMock
            ->expects($this->once())
            ->method('getAllFiles')
            ->will($this->returnValue($mockAwsResult));

        $this
            ->fileStorageMock
            ->expects($this->once())
            ->method('getFile')
            ->willReturn($fileMock);

        $service = $this->withSurveyResults()->createSurveyService();
        $result = $service->getSurveyReports();

        $this->assertEquals($key, $result[0]['month']);
        $this->assertEquals($size, $result[0]['size']);
        $this->assertEquals($csvData, $result[0]['csv']);
    }

    /**
     * @group display_survey_page
     */
    public function testShouldDisplaySurveyWithNoSurveysCompleted()
    {
        $notFoundException = $this->getMockBuilder(NotFoundException::class)->disableOriginalConstructor()->getMock();

        $this
            ->motTestSurveyRepository
            ->expects($this->atLeastOnce())
            ->method('getLastSurveyMotTestId')
            ->willThrowException($notFoundException);

        $this
            ->entityManager
            ->expects($this->any())
            ->method('getRepository')
            ->will($this->returnValueMap([
                [MotTestSurvey::class, $this->motTestSurveyRepository],
            ]));

        $motTestId = 1;
        $testerId = 105;

        $result = $this->createSurveyService()->shouldDisplaySurvey($motTestId, MotTestTypeCode::NORMAL_TEST, $testerId);

        $this->assertTrue($result);
    }

    /**
     * @group display_survey_page
     */
    public function testShouldDisplaySurveyWithReTest()
    {
        $motTestId = 1;
        $testerId = 105;

        $result = $this->createSurveyService()->shouldDisplaySurvey($motTestId, MotTestTypeCode::RE_TEST, $testerId);

        $this->assertFalse($result);
    }

    /**
     * @group display_survey_page
     */
    public function testShouldNotDisplaySurveyWithOneCompletedSurveyOutsideExclusionPeriod()
    {
        $motTestId = 2;
        $testerId = 105;

        $this->motTestSurveyRepository
            ->expects($this->atLeastOnce())
            ->method('getLastSurveyMotTestId')
            ->willReturn(1);

        $this->motTestSurveyRepository
            ->expects($this->never())
            ->method('getLastUserSurveyDate');

        $this->motRepository
            ->expects($this->never())
            ->method('getNormalMotTestCountSinceLastSurvey');

        $this->entityManager
            ->expects($this->any())
            ->method('getRepository')
            ->will($this->returnValueMap([
                [MotTest::class, $this->motRepository],
                [MotTestSurvey::class, $this->motTestSurveyRepository],
            ]));

        $service = $this->createSurveyService();

        $result = $service->shouldDisplaySurvey($motTestId, MotTestTypeCode::NORMAL_TEST, $testerId);

        $this->assertFalse($result);
    }

    /**
     * @group display_survey_page
     */
    public function testShouldDisplaySurveyWithOneCompletedSurveyOutsideExclusionPeriod()
    {
        $motTestId = 3;
        $testerId = 105;

        $this->motTestSurveyRepository
            ->expects($this->atLeastOnce())
            ->method('getLastSurveyMotTestId')
            ->willReturn(1);

        $this->motTestSurveyRepository
            ->expects($this->atLeastOnce())
            ->method('getLastUserSurveyDate')
            ->willReturn((new DateTime('1 year ago'))->format('Y-m-d'));

        $this->motRepository
            ->expects($this->atLeastOnce())
            ->method('getNormalMotTestCountSinceLastSurvey')
            ->willReturn(1);

        $this->entityManager
            ->expects($this->any())
            ->method('getRepository')
            ->will($this->returnValueMap([
                [MotTest::class, $this->motRepository],
                [MotTestSurvey::class, $this->motTestSurveyRepository],
            ]));

        $service = $this->createSurveyService();

        $result = $service->shouldDisplaySurvey($motTestId, MotTestTypeCode::NORMAL_TEST, $testerId);

        $this->assertTrue($result);
    }

    /**
     * @group display_survey_page
     */
    public function testShouldDisplaySurveyWithOneCompletedSurveyInsideExclusionPeriod()
    {
        $motTestId = 3;
        $testerId = 105;

        $this->motTestSurveyRepository
            ->expects($this->atLeastOnce())
            ->method('getLastSurveyMotTestId')
            ->willReturn(1);

        $this->motTestSurveyRepository
            ->expects($this->any())
            ->method('getLastUserSurveyDate')
            ->willReturn(date('Y-m-d', time()));

        $this->entityManager
            ->expects($this->any())
            ->method('getRepository')
            ->will($this->returnValueMap([
                [MotTest::class, $this->motRepository],
                [MotTestSurvey::class, $this->motTestSurveyRepository],
            ]));

        $service = $this->createSurveyService();

        $result = $service->shouldDisplaySurvey($motTestId, MotTestTypeCode::NORMAL_TEST, $testerId);

        $this->assertFalse($result);
    }

    /**
     * @group display_survey_page
     */
    public function testShouldDisplaySurveyWithSurveysCompletedByOtherUsers()
    {
        $motTestId = 3;
        $testerId = 105;

        $this->motRepository->expects($this->atLeastOnce())
            ->method('getNormalMotTestCountSinceLastSurvey')
            ->willReturn(1);

        $this->motTestSurveyRepository
            ->expects($this->atLeastOnce())
            ->method('getLastUserSurveyDate')
            ->willThrowException(new NotFoundException(MotTestSurvey::class));

        $this->entityManager
            ->expects($this->any())
            ->method('getRepository')
            ->will($this->returnValueMap([
                [MotTest::class, $this->motRepository],
                [MotTestSurvey::class, $this->motTestSurveyRepository],
            ]));

        $result = $this->createSurveyService()->shouldDisplaySurvey($motTestId, MotTestTypeCode::NORMAL_TEST, $testerId);

        $this->assertTrue($result);
    }

    public function testCreationOfSessionToken()
    {
        $this
            ->motRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->willReturn($this->motTestMock);

        $this
            ->entityManager
            ->expects($this->any())
            ->method('getRepository')
            ->will($this->returnValueMap([
                [MotTest::class, $this->motRepository],
            ]));

        $service = $this->createSurveyService();

        $motTestNumber = 123456789;
        $token = $service->createSessionToken($motTestNumber);
        $this->assertInternalType('string', $token);
        $this->assertTrue(SurveyToken::isValid($token));
    }

    /**
     * @param bool $tokenExistsInDb
     * @param bool $tokenUsed
     * @param bool $validUuid
     * @param bool $shouldValidate
     *
     * @dataProvider sessionTokenIsValidProvider
     */
    public function testSessionTokenIsValid($tokenExistsInDb, $tokenUsed, $validUuid, $shouldValidate)
    {
        $this
            ->motTestSurveyMock
            ->expects($this->any())
            ->method('hasBeenSubmitted')
            ->willReturn($tokenUsed);

        $this
            ->motTestSurveyRepository
            ->expects($this->any())
            ->method('findOneByToken')
            ->willReturn($tokenExistsInDb ? $this->motTestSurveyMock : null);

        $this
            ->entityManager
            ->expects($this->any())
            ->method('getRepository')
            ->will($this->returnValueMap([
                [MotTestSurvey::class, $this->motTestSurveyRepository],
            ]));

        $surveyService = $this->createSurveyService();

        $result = $surveyService->sessionTokenIsValid($validUuid ? self::TOKEN : 'not-a-valid-uuid');

        $this->assertEquals($result, $shouldValidate);
    }

    /**
     * @return array
     */
    public function sessionTokenIsValidProvider()
    {
        return [
            [true, false, true, true],
            [true, true, true, false],
            [true, true, false, false],
        ];
    }

    /**
     * @return $this
     */
    private function withSurveyResults()
    {
        $this->surveyResults = [
            'rating_1' => 1,
            'rating_2' => 2,
            'rating_3' => 3,
            'rating_4' => 4,
            'rating_5' => 5,
            'total' => 15,
        ];

        return $this;
    }

    /**
     * @return SurveyService
     */
    private function createSurveyService()
    {
        return new SurveyService(
            $this->entityManager,
            $this->authenticationServiceMock,
            $this->fileStorageMock,
            $this->surveyConfig
        );
    }
}
