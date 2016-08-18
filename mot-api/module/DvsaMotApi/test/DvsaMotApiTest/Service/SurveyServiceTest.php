<?php

namespace DvsaMotApiTest\Service;

use Aws\S3\S3Client;
use Doctrine\ORM\EntityManager;
use DvsaCommon\Dto\Common\MotTestDto;
use DvsaCommon\Dto\Common\MotTestTypeDto;
use DvsaCommon\Dto\Person\PersonDto;
use DvsaCommon\Enum\MotTestTypeCode;
use DvsaCommonApi\Service\Exception\NotFoundException;
use DvsaEntities\Entity\MotTest;
use DvsaEntities\Entity\MotTestSurveyResult;
use DvsaEntities\Repository\MotTestRepository;
use DvsaEntities\Repository\MotTestSurveyResultRepository;
use DvsaMotApi\Service\SurveyService;
use Zend\Authentication\AuthenticationService;

class SurveyServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var EntityManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $entityManagerMock;

    /**
     * @var S3Client|\PHPUnit_Framework_MockObject_MockObject
     */
    private $s3ClientMock;

    private $surveyResults;

    /** @var \StdClass */
    private $motTestDetails;

    /** @var \StdClass */
    private $testType;

    /** @var \StdClass */
    private $motTester;

    /** @var MotTestRepository|\PHPUnit_Framework_MockObject_MockObject */
    private $motRepositoryMock;

    /** @var MotTestSurveyResultRepository|\PHPUnit_Framework_MockObject_MockObject */
    private $motTestSurveyRepositoryMock;

    /** @var AuthenticationService|\PHPUnit_Framework_MockObject_MockObject */
    private $authenticationServiceMock;

    /** @var MotTest|\PHPUnit_Framework_MockObject_MockObject */
    private $motTestMock;

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
                    'setParameter'
                ]
            )
            ->getMock();

        $this->s3ClientMock = $this->getMockBuilder(S3Client::class)
            ->disableOriginalConstructor()
            ->setMethods(['putObject'])
            ->getMock();

        $this->motTestDetails = new \StdClass();

        $this->surveyConfig = [
            'numberOfTestsBetweenSurveys' => 1,
            'timeBeforeSurveyRedisplayed' => '1 second'
        ];

        $this->testType = new \StdClass();

        $this->motTester = new \StdClass();

        $this->motRepositoryMock = $this->getMockBuilder(MotTestRepository::class)
            ->disableOriginalConstructor()
            ->setMethods(['getNormalMotTestCountSinceLastSurvey', 'findByNumber'])
            ->getMock();

        $this->motTestSurveyRepositoryMock = $this->getMockBuilder(MotTestSurveyResult::class)
            ->disableOriginalConstructor()
            ->setMethods(['getLastUserSurveyDate'])
            ->getMock();

        $this->authenticationServiceMock = $this->getMockBuilder(AuthenticationService::class)
            ->disableOriginalConstructor()
            ->setMethods(['getIdentity', 'getUserId'])
            ->getMock();

        $this->motTestMock = $this->getMockBuilder(MotTest::class)
            ->disableOriginalConstructor()
            ->setMethods(['getTester', 'getId'])
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
        $currentUserId = 100;

        $this->authenticationServiceMock->expects($this->any())
            ->method('getIdentity')
            ->will($this->returnSelf());

        $this->authenticationServiceMock->expects($this->any())
            ->method('getUserId')
            ->willReturn($currentUserId);

        $this->motTestMock->expects($this->any())
            ->method('getTester')
            ->will($this->returnSelf());

        $this->motTestMock->expects($this->any())
            ->method('getId')
            ->willReturn($currentUserId);

        $this->motRepositoryMock->expects($this->any())
            ->method('findByNumber')
            ->willReturn([$this->motTestMock]);

        $this->entityManagerMock->expects($this->any())
            ->method('getRepository')
            ->willReturn($this->motRepositoryMock);

        $service = $this->createSurveyService();

        $surveyResult = $service->createSurveyResult(['mot_test_number' => 5,'satisfaction_rating' => $satisfactionRating]);

        $this->assertEquals($satisfactionRating, $surveyResult['satisfaction_rating']);
    }

    /**
     * @group survey_report_generation
     * @group integration
     */
    public function testGeneratingSurveyReports()
    {
        $service = $this->withSurveyResults()->createSurveyService();

        $csvHandle = fopen('php://memory', 'w');

        if (!empty(SurveyService::$CSV_COLUMNS)) {
            fputcsv($csvHandle, SurveyService::$CSV_COLUMNS);
        }

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

        fputcsv($csvHandle, $row);
        rewind($csvHandle);

        foreach (([SurveyService::$CSV_COLUMNS]) as $line) {
            fputcsv($csvHandle, $line);
        }

        rewind($csvHandle);
        $expectedContent = stream_get_contents($csvHandle);
        fclose($csvHandle);

        $this->s3ClientMock
            ->expects($this->atLeastOnce())
            ->method('putObject')
            ->with(
                [
                    'Bucket' => '',
                    'Key' => $timeStamp->format('Y-m'),
                    'Body' => $expectedContent,
                    'ContentType' => 'text/csv',
                ]
            );

        $service->generateSurveyReports($this->surveyResults);
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

        $this->testType->code = MotTestTypeCode::NORMAL_TEST;

        $this->motTestDetails->testType = $this->testType;

        $service = $this->createSurveyService();

        $result = $service->shouldDisplaySurvey($this->motTestDetails, true);

        $this->assertTrue($result);
    }

    /**
     * @group display_survey_page
     */
    public function testShouldDisplaySurveyWithReTest()
    {
        $this->testType->code = MotTestTypeCode::RE_TEST;

        $this->motTestDetails->testType = $this->testType;

        $service = $this->createSurveyService();

        $result = $service->shouldDisplaySurvey($this->motTestDetails, true);

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
            [MotTestSurveyResult::class, $this->motTestSurveyRepositoryMock]
        ];

        // repository mocked method
        $this->entityManagerMock
            ->expects($this->any())
            ->method('getRepository')
            ->will($this->returnValueMap($valueMap));

        // MOT test type mocked method
        $this->testType->code = MotTestTypeCode::NORMAL_TEST;

        // MOT test details mocked methods
        $this->motTestDetails->testType = $this->testType;

        $this->motTestDetails->tester = $this->motTester;

        // MOT tester mock method
        $this->motTester->id = 1;

        $service = $this->createSurveyService();

        $result = $service->shouldDisplaySurvey($this->motTestDetails, true);

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
            [MotTestSurveyResult::class, $this->motTestSurveyRepositoryMock]
        ];

        // repository mocked method
        $this->entityManagerMock
            ->expects($this->any())
            ->method('getRepository')
            ->will($this->returnValueMap($valueMap));

        // MOT test type mocked method
        $this->testType->code = MotTestTypeCode::NORMAL_TEST;

        // MOT test details mocked methods
        $this->motTestDetails->testType = $this->testType;

        $this->motTestDetails->tester = $this->motTester;

        // MOT tester mock method
        $this->motTester->id = 1;

        $service = $this->createSurveyService();

        $result = $service->shouldDisplaySurvey($this->motTestDetails, true);

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
            ->willThrowException(new NotFoundException(MotTestSurveyResult::class));

        // repository mocked method
        $valueMap = [
            [MotTest::class, $this->motRepositoryMock],
            [MotTestSurveyResult::class, $this->motTestSurveyRepositoryMock]
        ];

        // repository mocked method
        $this->entityManagerMock
            ->expects($this->any())
            ->method('getRepository')
            ->will($this->returnValueMap($valueMap));

        // MOT test type mocked method
        $this->testType->code = MotTestTypeCode::NORMAL_TEST;

        // MOT test details mocked methods
        $this->motTestDetails->testType = $this->testType;

        $this->motTestDetails->tester = $this->motTester;

        // MOT tester mock method
        $this->motTester->id = 1;


        $service = $this->createSurveyService();

        $result = $service->shouldDisplaySurvey($this->motTestDetails, true);

        $this->assertTrue($result);
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
            $this->s3ClientMock,
            '',
            $this->surveyConfig
        );
    }

    /**
     * @param int   $numberOfCompletedTests
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
