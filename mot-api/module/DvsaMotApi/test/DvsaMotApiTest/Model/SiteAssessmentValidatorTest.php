<?php

namespace DvsaMotApiTest\Model;

use DvsaCommon\Constants\SiteAssessment;
use DvsaCommon\Dto\Site\VehicleTestingStationDto;
use DvsaCommon\Utility\ArrayUtils;
use DvsaCommonApiTest\Service\AbstractServiceTestCase;
use DvsaMotApi\Model\SiteAssessmentValidator;
use DvsaMotApi\Service\TesterService;
use DvsaMotApi\Service\UserService;
use OrganisationApi\Service\AuthorisedExaminerService;
use PHPUnit_Framework_MockObject_MockObject;
use SiteApi\Service\SiteService;
use Zend\Form\Annotation\ErrorMessage;

/**
 * Class SiteAssessmentValidatorTest.
 */
class SiteAssessmentValidatorTest extends AbstractServiceTestCase
{
    /** @var PHPUnit_Framework_MockObject_MockObject */
    private $mockSiteService;
    /** @var PHPUnit_Framework_MockObject_MockObject */
    private $mockAEService;
    /** @var PHPUnit_Framework_MockObject_MockObject */
    private $mockUserService;
    /** @var PHPUnit_Framework_MockObject_MockObject */
    private $mockTesterService;

    public function setUp()
    {
        $this->mockSiteService = $this->getMockWithDisabledConstructor(SiteService::class);
        $this->mockUserService = $this->getMockWithDisabledConstructor(UserService::class);
        $this->mockTesterService = $this->getMockWithDisabledConstructor(TesterService::class);
        $this->mockAEService = $this->getMockWithDisabledConstructor(AuthorisedExaminerService::class);
    }

    public function tearDown()
    {
        unset(
            $this->mockSiteService,
            $this->mockUserService,
            $this->mockTesterService,
            $this->mockAEService
        );
    }

    /**
     * Check count of items will be return after validation.
     */
    public function testCanSeparateSiteNameAndNumberToJustNumber()
    {
        $v = $this->subTestError([], [], 0, null);

        $data = $v->getData();

        $this->assertEquals(count($data), 10);
        $this->assertEquals('V1261', $data['vts-search']);
    }

    /**
     * Check error is appear with correct error message if passed invalid data.
     *
     * @param array  $siteData       Site data
     * @param int    $expectErrCount Count of errors
     * @param string $expectErrMssg  Text that have to be a part of error message
     *
     * @dataProvider dataProviderTestError
     */
    public function testError(array $siteData, array $services, $expectErrCount, $expectErrMssg)
    {
        $this->subTestError($siteData, $services, $expectErrCount, $expectErrMssg);
    }

    public function dataProviderTestError()
    {
        return [
            //  --  incorrect date  --
            [
                'siteData' => [
                    'day' => 30,
                    'month' => 2,
                ],
                'services' => [],
                'expectErrCount' => 1,
                'expectErrMssg' => 'Incorrect days',
            ],

            //  --  incorrect Year - too High & too Low    --
            [['year' => null], [], 1, SiteAssessmentValidator::$fieldLabels['year']],
            [['year' => date('Y') + 1], [], 1, 'future'],
            [['year' => 1969], [], 1, 'future'],

            //  --  incorrect Month - missing, too High & too Low    --
            [['month' => null], [], 1, SiteAssessmentValidator::$fieldLabels['month']],
            [['month' => 42], [], 1, 'range 1-12'],
            [['month' => 0], [], 1, 'range 1-12'],

            //  --  incorrect Day - missing, too High  & too Low    --
            [['day' => null], [], 1, SiteAssessmentValidator::$fieldLabels['day']],
            [['day' => 3223], [], 1, 'range 1-31'],
            [['day' => 0], [], 1, 'range 1-31'],

            //  --  invalid AdvisoryCode  --
            [['advisory-issued' => 'X'], [], 1, 'Invalid advisory issued'],

            //  --  site Number - Missing & incorrect  --
            [['vts-search' => null], [], 1, SiteAssessmentValidator::$fieldLabels['vts-search']],
            [['vts-search' => ''], [], 1, SiteAssessmentValidator::$fieldLabels['vts-search']],
            // ????           [['vts-search' => 'V1261 - blah'], null, 1, 'Invalid site'],

            //  --  Visit Outcome - Valid and invalid --
            [['visit-outcome' => 1], ['catalog' => $this->getMockCatalogService()], 0, null],
            [['visit-outcome' => 2], ['catalog' => $this->getMockCatalogService()], 0, null],
            [['visit-outcome' => 3], ['catalog' => $this->getMockCatalogService()], 0, null],
            [['visit-outcome' => null], [], 1, SiteAssessmentValidator::$fieldLabels['visit-outcome']],
            [['visit-outcome' => 'fail'], ['catalog' => $this->getMockCatalogService()], 1, 'Invalid visit outcome'],

            //  --  Missing - AeIdNumber  --
            [['ae-rep-id' => null], [], 1, SiteAssessmentValidator::$fieldLabels['ae-rep-id']],

            //  --  Missing - AeIdNumber  --
            [['tester-id' => null], [], 1, SiteAssessmentValidator::$fieldLabels['tester-id']],

            //  --  Missing - AeIdNumber  --
            [['advisory-issued' => null], [], 1, SiteAssessmentValidator::$fieldLabels['advisory-issued']],

            //  --  Missing - AERepPosition  --
            [['ae-rep-pos' => null], [], 1, SiteAssessmentValidator::$fieldLabels['ae-rep-pos']],

            //  --  Site Score  --
            [['site-score' => null], [], 1, 'site score must be a number and greater or equals zero'],
            [['site-score' => 0], [], 0, null],
            [
                ['site-score' => 9999],
                [],
                1,
                'A site score cannot be higher than '.SiteAssessment::RISK_SCORE_MAX,
            ],
            [['site-score' => 'a'], [], 1, 'A site score must be a number and greater or equals zero'],
            [['site-score' => 0.001], [], 0, null],
        ];
    }

    /**
     * Check error and error message if site service return empty result or exception.
     *
     * @param string|array|null $srvResult        Service result
     * @param bool              $srvIsException   Is service thrown Exception
     * @param array             $siteData         Passed site data
     * @param int               $expectSiteNumber Expected Site Number
     * @param int               $expectErrCount   Expected count of errors
     * @param string            $expectErrMsg     Expected part of error message
     *
     * @dataProvider dataProviderTestSiteNumberFailsFailsToResolve
     */
    public function testSiteNumberFailsFailsToResolve(
        $srvResult,
        $srvIsException,
        $siteData,
        $expectSiteNumber,
        $expectErrCount,
        $expectErrMsg
    ) {
        $this->mockSiteService->expects($this->once())
            ->method('getSiteBySiteNumber')
            ->will(
                $srvIsException
                ? $this->throwException(new \Exception())
                : $this->returnValue($srvResult)
            );

        $v = $this->subTestError(
            $siteData,
            ['site' => $this->mockSiteService],
            $expectErrCount,
            $expectErrMsg
        );

        if ($expectSiteNumber) {
            $this->assertEquals($expectSiteNumber, $v->getSiteNumber());
        }
    }

    public function dataProviderTestSiteNumberFailsFailsToResolve()
    {
        $result = (new VehicleTestingStationDto())->setSiteNumber(12345);

        return [
            [
                'srvResult' => $result,
                'srvIsException' => false,
                'siteData' => [
                    'vts-search' => '0003AW, M And T Transmissions Limited, 120 Bradway Road, Sheffield, S17 4QW',
                    'searchSiteNumber' => '0003AW',
                ],
                'expectSiteNr' => 12345,
                'expectErrCount' => 0,
                'expectErrMsg' => null,
            ],
            [
                null, true,
                [
                    'vts-search' => '0003AW, M And T Transmissions Limited, 120 Bradway Road, Sheffield, S17 4QW',
                    'searchSiteNumber' => '0003AW',
                ],
                null, 1, 'Invalid site',
            ],
            [
                null, false,
                [
                    'vts-search' => '0003AW, M And T Transmissions Limited, 120 Bradway Road, Sheffield, S17 4QW',
                    'searchSiteNumber' => '0003AW',
                ],
                null, 1, '0003AW',
            ],
            [
                null, false,
                [
                    'vts-search' => '0003AW, M And T Transmissions Limited, 120 Bradway Road, Sheffield, S17 4QW',
                    'searchSiteNumber' => '-1',
                ],
                null, 1, '0003AW',
            ],
            [null, false, ['vts-search' => 'V1261 - blah'], null, 1, 'Invalid site'],
            [$result, false, [], 12345, 0, null],
        ];
    }

    /**
     * Check error and error message appear is Authorised Examiner service throw Exception or empty result.
     *
     * @param array|null $userSrvResult   Service return
     * @param bool       $testerSrvResult Is service throw error
     *
     * @dataProvider dataProviderTestAeRepresentativeIdFailsToResolve
     */
    public function testAeRepresentativeIdFailsToResolve($srvResult, $srvIsException)
    {
        $this->mockAEService->expects($this->once())
            ->method('getAuthorisedExaminerData')
            ->will(
                $srvIsException
                ? $this->throwException(new \Exception())
                : $this->returnValue($srvResult)
            );

        $this->subTestError(
            ['tester-id' => 54321],
            ['ae' => $this->mockAEService],
            1,
            SiteAssessmentValidator::$fieldLabels['ae-rep-id']
        );
    }

    public function dataProviderTestAeRepresentativeIdFailsToResolve()
    {
        return [
            [
                'srvResult' => [],
                'srvIsException' => false,
            ],
            [null, true],
        ];
    }

    /**
     * Check error and message error if User service or Tester service thrown exception or return empty result.
     *
     * @param null|array $userSrvResult      User Service return
     * @param bool       $userSrvException   Is User Service thrown exception
     * @param null|array $testerSrvResult    Tester Service return
     * @param bool       $testerSrvException Is Tester Service thrown exception
     *
     * @dataProvider dataProviderTestUserOrTesterFailToResolve
     */
    public function testUserOrTesterFailToResolve(
        $userSrvResult,
        $userSrvException,
        $testerSrvResult,
        $testerSrvException
    ) {
        //  --  user service  --
        $this->mockUserService->expects($this->once())
            ->method('getUserData')
            ->will(
                $userSrvException
                ? $this->throwException(new \Exception())
                : $this->returnValue($userSrvResult)
            );

        //  --  tester service  --
        if ($testerSrvResult === null && $testerSrvException === null) {
            $this->mockTesterService->expects($this->never())
                ->method('getTesterByUserId');
        } else {
            $this->mockTesterService->expects($this->once())
                ->method('getTesterByUserId')
                ->will(
                    $testerSrvException
                    ? $this->throwException(new \Exception())
                    : $this->returnValue($testerSrvResult)
                );
        }

        $this->subTestError(
            ['tester-id' => 54321],
            [
                'user' => $this->mockUserService,
                'tester' => $this->mockTesterService,
            ],
            1,
            SiteAssessmentValidator::$fieldLabels['tester-id']
        );
    }

    public function dataProviderTestUserOrTesterFailToResolve()
    {
        return [
            [
                'userSrvResult' => ['id' => 31415],
                'userSrvException' => null,
                'testerSrvResult' => null,
                'testerSrvException' => true,
            ],
            [['id' => 999], null, [], true],
            [null, true, null, null],
        ];
    }

    public function subTestError(array $siteData, array $services, $expectErrCount, $expectErrMssg)
    {
        $siteData = $siteData + $this->getSiteData();

        $v = new SiteAssessmentValidator(
            $siteData,
            ArrayUtils::tryGet($services, 'site'),
            ArrayUtils::tryGet($services, 'ae'),
            ArrayUtils::tryGet($services, 'tester'),
            ArrayUtils::tryGet($services, 'catalog'),
            ArrayUtils::tryGet($services, 'user')
        );
        $errors = $v->getErrors(0);

        $this->assertEquals($expectErrCount, count($errors));

        /** @var ErrorMessage $err */
        foreach ($errors as $err) {
            $this->assertContains($expectErrMssg, $err->getMessage());
        }

        return $v;
    }

    private function getSiteData()
    {
        return [
            'vts-search' => 'V1261 - MOTs R Us',
            'ae-rep-id' => 1234,
            'visit-outcome' => 1,
            'tester-id' => 999,
            'advisory-issued' => 'N',
            'day' => 31,
            'month' => 1,
            'year' => 2001,
            'ae-rep-pos' => 'human being',
            'site-score' => 1,
        ];
    }

    private function getMockCatalogService()
    {
        $service = $this->getMockWithDisabledConstructor(\DataCatalogApi\Service\DataCatalogService::class);

        $service->expects($this->exactly(3))
            ->method('getSiteAssessmentVisitOutcomeData')
            ->will(
                $this->returnValue(
                    [
                        ['id' => 1, 'description' => 'Satisfactory'],
                        ['id' => 2, 'description' => 'Shortcomings found'],
                        ['id' => 3, 'description' => 'Abandoned'],
                    ]
                )
            );

        return $service;
    }
}
