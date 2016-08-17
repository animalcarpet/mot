<?php
/**
 * This file is part of the DVSA MOT API project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace DvsaMotApiTest\Service;

use Doctrine\ORM\EntityManager;
use DvsaCommon\Enum\MotTestTypeCode;
use DvsaCommonApi\Service\Exception\NotFoundException;
use DvsaEntities\Entity\MotTest;
use DvsaEntities\Entity\MotTestSurvey;
use DvsaEntities\Entity\Survey;
use DvsaEntities\Repository\Doctrine\DoctrineMotTestSurveyRepository;
use DvsaEntities\Repository\MotTestRepository;
use DvsaEntities\Repository\MotTestSurveyRepository;
use DvsaMotApi\Domain\Survey\SurveyConfiguration;
use DvsaMotApi\Service\S3\FileStorageInterface;
use DvsaMotApi\Service\S3\S3CsvStore;
use DvsaMotApi\Service\SurveyService;
use Zend\Authentication\AuthenticationService;

class SurveyServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var EntityManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $entityManagerMock;

    /** @var FileStorageInterface|\PHPUnit_Framework_MockObject_MockObject $fileStorageMock */
    private $fileStorageMock;

    private $surveyResults;

    /** @var MotTestRepository|\PHPUnit_Framework_MockObject_MockObject */
    private $motRepositoryMock;

    /** @var MotTestSurveyRepository|\PHPUnit_Framework_MockObject_MockObject */
    private $motTestSurveyRepositoryMock;

    /** @var AuthenticationService|\PHPUnit_Framework_MockObject_MockObject */
    private $authenticationServiceMock;

    /** @var MotTest|\PHPUnit_Framework_MockObject_MockObject */
    private $motTestMock;

    /** @var MotTestSurvey|\PHPUnit_Framework_MockObject_MockObject */
    private $motTestSurveyMock;

    /** @var Survey|\PHPUnit_Framework_MockObject_MockObject */
    private $surveyMock;

    /**
     * @var SurveyConfiguration
     */
    private $surveyConfig;

    public function setUp()
    {
        $this->entityManagerMock = $this->getMockBuilder(EntityManager::class)
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
            'numberOfTestsBetweenSurveys' => 1,
            'timeBeforeSurveyRedisplayed' => '1 second',
        ]);

        $this->motRepositoryMock = $this->getMockBuilder(MotTestRepository::class)
            ->disableOriginalConstructor()
            ->setMethods(['getNormalMotTestCountSinceLastSurvey'])
            ->getMock();

        $this->motTestSurveyRepositoryMock = $this->getMockBuilder(DoctrineMotTestSurveyRepository::class)
            ->disableOriginalConstructor()
            ->setMethods(['getLastUserSurveyDate', 'findByToken', 'findOneBy'])
            ->getMock();

        $this->authenticationServiceMock = $this->getMockBuilder(AuthenticationService::class)
            ->disableOriginalConstructor()
            ->setMethods(['getIdentity', 'getUserId'])
            ->getMock();

        $this->motTestMock = $this->getMockBuilder(MotTest::class)
            ->disableOriginalConstructor()
            ->setMethods(['getTester', 'getId'])
            ->getMock();

        $this->motTestSurveyMock = $this->getMockBuilder(MotTestSurvey::class)
            ->disableOriginalConstructor()
            ->setMethods(['getSurvey', 'getMotTest'])
            ->getMock();

        $this->surveyMock = $this->getMockBuilder(Survey::class)
            ->disableOriginalConstructor()
            ->setMethods(['getRating'])
            ->getMock();
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

        $this->entityManagerMock->expects($this->any())
            ->method('getRepository')
            ->will(
                $this->returnValueMap(
                    [
                        [MotTest::class, $this->motRepositoryMock],
                        [MotTestSurvey::class, $this->motTestSurveyRepositoryMock],
                    ]
                )
            );

        $this->motTestSurveyRepositoryMock->expects($this->once())
           ->method('findByToken')
           ->willReturn($this->motTestSurveyMock);

        $this->motTestSurveyRepositoryMock->expects($this->once())
            ->method('findOneBy')
            ->willReturn($this->motTestSurveyMock);

        $this->motTestSurveyMock->expects($this->once())
            ->method('getMotTest')
            ->willReturn($this->motTestMock);

        $this->motTestSurveyMock->expects($this->any())
            ->method('getSurvey')
            ->willReturn(null);

        $this->surveyMock->expects($this->any())
            ->method('getRating')
            ->willReturn($satisfactionRating);

        $service = $this->createSurveyService();

        if (!in_array($satisfactionRating, $validSurveyValues)) {
            $this->setExpectedException('InvalidArgumentException');
        }

        $surveyResult = $service->createSurveyResult([
            'token' => 'testToken',
            'satisfaction_rating' => $satisfactionRating,
        ]);

        $this->assertEquals($satisfactionRating, $surveyResult['satisfaction_rating']);
    }

    /**
     * @group survey_report_generation
     * @group integration
     */
    public function testGeneratingSurveyReports()
    {
        $timeStamp = new \DateTime();
        $row['timestamp'] = $timeStamp->format('Y-m-d-H-i-s');
        $row['period'] = 'month';
        $row['slug'] = 'https://mot-testing.i-env.net/';
        $row['rating_1'] = 1;
        $row['rating_2'] = 2;
        $row['rating_3'] = 3;
        $row['rating_4'] = 4;
        $row['rating_5'] = 5;
        $row['total'] = 15;

        $this->fileStorageMock->expects($this->once())
            ->method('putFile')
            ->with(SurveyService::$CSV_COLUMNS, $row, $timeStamp->format('Y-m'));

        $service = $this->withSurveyResults()->createSurveyService();

        $service->generateSurveyReports($this->surveyResults);
    }

    public function testGetSurveyReports()
    {
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
        $this->setupQueryBuilderMockMethods(0);

        $this->entityManagerMock
            ->expects($this->atLeastOnce())
            ->method('getSingleScalarResult')
            ->willReturn(0);

        $this->motRepositoryMock->expects($this->any())
            ->method('getNormalMotTestCountSinceLastSurvey')
            ->willReturn(0);

        $service = $this->createSurveyService();

        $result = $service->shouldDisplaySurvey(MotTestTypeCode::NORMAL_TEST, 1);

        $this->assertTrue($result);
    }

    /**
     * @group display_survey_page
     */
    public function testShouldDisplaySurveyWithReTest()
    {
        $service = $this->createSurveyService();

        $result = $service->shouldDisplaySurvey(MotTestTypeCode::RE_TEST, 1);

        $this->assertFalse($result);
    }

    /**
     * @group display_survey_page
     */
    public function testShouldDisplaySurveyWithOneCompletedSurveyOutsideExclusionPeriod()
    {
        $this->setupQueryBuilderMockMethods(1);

        $this->motRepositoryMock->expects($this->atLeastOnce())
            ->method('getNormalMotTestCountSinceLastSurvey')
            ->willReturn(1);

        $this->motTestSurveyRepositoryMock->expects($this->atLeastOnce())
            ->method('getLastUserSurveyDate')
            ->willReturn('2015-04-20');

        $valueMap = [
            [MotTest::class, $this->motRepositoryMock],
            [MotTestSurvey::class, $this->motTestSurveyRepositoryMock],
        ];

        // repository mocked method
        $this->entityManagerMock
            ->expects($this->any())
            ->method('getRepository')
            ->will($this->returnValueMap($valueMap));

        $service = $this->createSurveyService();

        $result = $service->shouldDisplaySurvey(MotTestTypeCode::NORMAL_TEST, 1);

        $this->assertTrue($result);
    }

    /**
     * @group display_survey_page
     */
    public function testShouldDisplaySurveyWithOneCompletedSurveyInsideExclusionPeriod()
    {
        $this->setupQueryBuilderMockMethods(1);

        $this->motRepositoryMock->expects($this->atLeastOnce())
            ->method('getNormalMotTestCountSinceLastSurvey')
            ->willReturn(1);

        $this->motTestSurveyRepositoryMock->expects($this->atLeastOnce())
            ->method('getLastUserSurveyDate')
            ->willReturn(date('Y-m-d', time()));

        // repository mocked method
        $valueMap = [
            [MotTest::class, $this->motRepositoryMock],
            [MotTestSurvey::class, $this->motTestSurveyRepositoryMock],
        ];

        // repository mocked method
        $this->entityManagerMock
            ->expects($this->any())
            ->method('getRepository')
            ->will($this->returnValueMap($valueMap));

        $service = $this->createSurveyService();

        $result = $service->shouldDisplaySurvey(MotTestTypeCode::NORMAL_TEST, 1);

        $this->assertFalse($result);
    }

    /**
     * @group display_survey_page
     */
    public function testShouldDisplaySurveyWithSurveysCompletedByOtherUsers()
    {
        $this->setupQueryBuilderMockMethods(5);

        $this->motRepositoryMock->expects($this->atLeastOnce())
            ->method('getNormalMotTestCountSinceLastSurvey')
            ->willReturn(1);

        $this->motTestSurveyRepositoryMock->expects($this->atLeastOnce())
            ->method('getLastUserSurveyDate')
            ->willThrowException(new NotFoundException(MotTestSurvey::class));

        // repository mocked method
        $valueMap = [
            [MotTest::class, $this->motRepositoryMock],
            [MotTestSurvey::class, $this->motTestSurveyRepositoryMock],
        ];

        // repository mocked method
        $this->entityManagerMock
            ->expects($this->any())
            ->method('getRepository')
            ->will($this->returnValueMap($valueMap));

        $service = $this->createSurveyService();

        $result = $service->shouldDisplaySurvey(MotTestTypeCode::NORMAL_TEST, 1);

        $this->assertTrue($result);
    }

    /**
     * @param $tokenExistsInDb
     * @param $tokenUsed
     * @param $shouldValidate
     *
     * @dataProvider sessionTokenIsValidProvider
     */
    public function testSessionTokenIsValid($tokenExistsInDb, $tokenUsed, $shouldValidate)
    {
        $this->entityManagerMock->expects($this->any())
            ->method('getRepository')
            ->will(
                $this->returnValueMap(
                    [
                        [MotTest::class, $this->motRepositoryMock],
                        [MotTestSurvey::class, $this->motTestSurveyRepositoryMock],
                    ]
                )
            );

        if ($tokenUsed) {
            $this->motTestSurveyMock->expects($this->any())
                ->method('getSurvey')
                ->willReturn($this->surveyMock);
        } else {
            $this->motTestSurveyMock->expects($this->any())
                ->method('getSurvey')
                ->willReturn(null);
        }

        if ($tokenExistsInDb) {
            $this->motTestSurveyRepositoryMock->expects($this->once())
                ->method('findOneBy')
                ->willReturn($this->motTestSurveyMock);
        } else {
            $this->motTestSurveyRepositoryMock->expects($this->once())
                ->method('findOneBy')
                ->willReturn(null);
        }

        $surveyService = $this->createSurveyService();

        $result = $surveyService->sessionTokenIsValid('someToken');

        $this->assertEquals($result, $shouldValidate);
    }

    /**
     * @return array
     */
    public function sessionTokenIsValidProvider()
    {
        return [
            [true, false, true],
            [true, true, false],
            [false, false, false],
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
            $this->entityManagerMock,
            $this->authenticationServiceMock,
            $this->fileStorageMock,
            $this->surveyConfig
        );
    }

    /**
     * @param int $numberOfCompletedTests
     */
    private function setupQueryBuilderMockMethods($numberOfCompletedTests)
    {
        $this->entityManagerMock
            ->expects($this->atLeastOnce())
            ->method('createQueryBuilder')
            ->willReturn($this->entityManagerMock);

        $this->entityManagerMock
            ->expects($this->atLeastOnce())
            ->method('select')
            ->willReturn($this->entityManagerMock);

        $this->entityManagerMock
            ->expects($this->atLeastOnce())
            ->method('from')
            ->willReturn($this->entityManagerMock);

        $this->entityManagerMock
            ->expects($this->atLeastOnce())
            ->method('getQuery')
            ->willReturn($this->entityManagerMock);

        $this->entityManagerMock
            ->expects($this->any())
            ->method('where')
            ->willReturn($this->entityManagerMock);

        $this->entityManagerMock
            ->expects($this->any())
            ->method('orWhere')
            ->willReturn($this->entityManagerMock);

        $this->entityManagerMock
            ->expects($this->any())
            ->method('orderBy')
            ->willReturn($this->entityManagerMock);

        $this->entityManagerMock
            ->expects($this->any())
            ->method('setParameter')
            ->willReturn($this->entityManagerMock);

        $this->entityManagerMock
            ->expects($this->any())
            ->method('getSingleScalarResult')
            ->willReturn($numberOfCompletedTests);
    }
}
