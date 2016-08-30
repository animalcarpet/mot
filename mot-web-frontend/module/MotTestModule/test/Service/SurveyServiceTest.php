<?php

namespace Dvsa\Mot\Frontend\MotTestModuletest\Service;

use Dvsa\Mot\Frontend\MotTestModule\Service\SurveyService;
use DvsaCommon\HttpRestJson\Client;

class SurveyServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Client|\PHPUnit_Framework_MockObject_MockObject
     */
    private $clientMock;

    const API_URL = 'survey';

    public function setUp()
    {
        $this->clientMock = $this->getMockBuilder(Client::class)->getMock();
    }

    /**
     * @return array
     */
    public function testSubmitSurveyDataProvider()
    {
        return [
            [
                '1',
                ['1'],
            ],
            [
                '2',
                ['2'],
            ],
            [
                '3',
                ['3'],
            ],
            [
                '4',
                ['4'],
            ],
            [
                '5',
                ['5'],
            ],
        ];
    }

    /**
     * @dataProvider testSubmitSurveyDataProvider
     * @group survey_service_tests
     *
     * @param $feedback
     * @param $expected
     */
    public function testSubmitSurveyResult($feedback, $expected)
    {
        $service = $this->createService();
        $this->clientMock
            ->expects($this->once())
            ->method('post')
            ->with(self::API_URL, $feedback)
            ->willReturn([$feedback]);

        $result = $service->submitSurveyResult($feedback);

        $this->assertSame($result, $expected);
    }

    /**
     * @return array
     */
    public function testGetSurveyReportsDataProvider()
    {
        return [
            [
                'month' => '2016-04',
                'size' => 0,
                'csv' => '',
            ],
            [
                'month' => '2016-03',
                'size' => 100,
                'csv' => 'a,different,csv,file',
            ],
            [
                'month' => '2016-02',
                'size' => 420,
                'csv' => 'a,more,different,csv,file',
            ],
        ];
    }

    /**
     * @dataProvider testGetSurveyReportsDataProvider
     * @group survey_service_tests
     *
     * @param $report
     */
    public function testGetSurveyReports($report)
    {
        $service = $this->createService();
        $this->clientMock
            ->expects($this->once())
            ->method('get')
            ->with()
            ->willReturn($report);

        $result = $service->getSurveyReports();

        $this->assertSame($result, $report);
    }

    public function testTokenValidityTestInvalid()
    {
        $token = 'invalid';
        $service = $this->createService();
        $this->clientMock
            ->expects($this->once())
            ->method('post')
            ->with(SurveyService::API_URL.SurveyService::TOKEN_VALIDATION_ENDPOINT, ['token' => $token])
            ->willReturn(['data' => 'false']);

        $this->assertEquals('false', $service->isTokenValid($token));
    }

    public function testTokenValidityTestValid()
    {
        $token = 'valid';
        $service = $this->createService();
        $this->clientMock
            ->expects($this->once())
            ->method('post')
            ->with(SurveyService::API_URL.SurveyService::TOKEN_VALIDATION_ENDPOINT, ['token' => $token])
            ->willReturn(['data' => 'true']);

        $this->assertEquals('true', $service->isTokenValid($token));
    }

    /**
     * @return SurveyService
     */
    private function createService()
    {
        return new SurveyService($this->clientMock);
    }
}
