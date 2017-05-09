<?php

namespace DvsaMotEnforcementApiTest\Controller;

use DvsaCommon\Auth\PermissionInSystem;
use Dvsa\Mot\Frontend\Test\StubIdentityAdapter;
use DvsaMotEnforcementApi\Controller\MotTestApiController;
use DvsaMotTestTest\Controller\AbstractDvsaMotTestTestCase;
use DvsaCommon\HttpRestJson\Exception\ValidationException;
use DvsaCommonTest\Bootstrap;

/**
 * Class MotTestApiControllerTest.
 */
class MotTestApiControllerTest extends AbstractDvsaMotTestTestCase
{
    const TEST_SITE_NUMBER = 'V1234';

    protected function setUp()
    {
        $this->controller = new MotTestApiController();
        $this->controller->setServiceLocator(Bootstrap::getServiceManager());
        parent::setUp();
    }

    /**
     * Test when the user is logged in and has the right role.
     */
    public function testExaminerFetchRecentMotTestDataActionCanBeAccessedForAuthenticatedRequest()
    {
        $this->setDvsaSiteSearchPermissions();
        $this->getViewRendererMock();
        $this->getResponseForAction('examinerFetchRecentMotTestData');

        $this->assertResponseStatus(self::HTTP_OK_CODE);
    }

    /**
     * Test when the user is not logged in.
     *
     * @expectedException \DvsaCommon\Exception\UnauthorisedException
     */
    public function testExaminerFetchRecentMotTestDataActionUnauthenticated()
    {
        $this->setupAuthenticationServiceForIdentity(StubIdentityAdapter::asAnonymous());

        $this->getResponseForAction('examinerFetchRecentMotTestData');
    }

    public function testRecentMots()
    {
        $this->setDvsaSiteSearchPermissions();
        $this->getViewRendererMock();

        $this->getRestClientMock('getWithParams', $this->getRecentMotTestData());

        $this->request->getQuery()->set('siteNumber', self::TEST_SITE_NUMBER);
        $this->getResultForAction('examinerFetchRecentMotTestData');

        $this->assertResponseStatus(self::HTTP_OK_CODE);
    }

    public function testRecentMotsThrowError()
    {
        $this->setDvsaSiteSearchPermissions();
        $restController = $this->getRestClientMockForServiceManager();

        $restController->expects($this->once())
            ->method('getWithParams')
            ->will($this->throwException(new ValidationException('/', 'get', [], 10, [['displayMessage' => 'error']])));

        $this->request->getQuery()->set('siteNumber', 'invalidSite');
        $this->getResultForAction('examinerFetchRecentMotTestData');

        $this->assertResponseStatus(self::HTTP_OK_CODE);
    }

    public function testRecentMotsStatusActive()
    {
        $this->setDvsaSiteSearchPermissions();
        $this->getViewRendererMock();

        $testData = $this->getRecentMotTestData();
        $testData['data'][0]['status'] = 'ACTIVE';

        $this->getRestClientMock('getWithParams', $testData);

        $this->request->getQuery()->set('siteNumber', self::TEST_SITE_NUMBER);
        $this->getResultForAction('examinerFetchRecentMotTestData');

        $this->assertResponseStatus(self::HTTP_OK_CODE);
    }

    public function testRecentMotsStatusFailedVe()
    {
        $this->setDvsaSiteSearchPermissions();
        $this->getViewRendererMock();

        $testData = $this->getRecentMotTestData();
        $testData['data'][0]['status'] = 'FAILED_VE';

        $this->getRestClientMock('getWithParams', $testData);

        $this->request->getQuery()->set('siteNumber', self::TEST_SITE_NUMBER);
        $this->getResultForAction('examinerFetchRecentMotTestData');

        $this->assertResponseStatus(self::HTTP_OK_CODE);
    }

    protected function getViewRendererMock()
    {
        $viewRendererMock = \DvsaCommonTest\TestUtils\XMock::of(\Zend\View\Renderer\PhpRenderer::class);
        $serviceManager = $this->getServiceManager();
        $serviceManager->setAllowOverride(true);
        $serviceManager->setService('ViewRenderer', $viewRendererMock);

        return $viewRendererMock;
    }

    protected function getRecentMotTestData()
    {
        return [
            'data' => [
                'data' => [
                    [
                        'motTestNumber' => '1',
                        'hasRegistration' => false,
                        'status' => 'FAILED',
                        'startedDate' => '2014-07-24T10:00:00Z',
                        'completedDate' => '2014-07-24T11:00:00Z',
                        'testType' => 'NT',
                        'make' => 'sipolot',
                        'model' => 'pospolity',
                        'registration' => 'torba5000',
                        'testerUsername' => 'kretyn',
                    ],
                ],
            ],
        ];
    }

    public function testRecentMotsByDate()
    {
        $this->setDvsaSiteSearchPermissions();
        $this->getViewRendererMock();

        $this->getRestClientMock('getWithParams', $this->getMotSearchResult());

        $this->request->getQuery()->set('type', 'vts');
        $this->request->getHeaders()->addHeaders(['X_REQUESTED_WITH' => 'XMLHttpRequest']);

        $this->setPostAndPostParams(['search' => 'test']);
        $this->getResultForAction('examinerFetchMotTestByDate');

        $this->assertResponseStatus(self::HTTP_OK_CODE);
    }

    public function testRecentMotsByDateTester()
    {
        $this->setDvsaSiteSearchPermissions();
        $this->getViewRendererMock();

        $this->getRestClientMock('getWithParams', $this->getMotSearchResult());

        $this->request->getQuery()->set('type', 'tester');
        $this->request->getHeaders()->addHeaders(['X_REQUESTED_WITH' => 'XMLHttpRequest']);

        $this->setPostAndPostParams(['search' => 'test']);
        $this->getResultForAction('examinerFetchMotTestByDate');

        $this->assertResponseStatus(self::HTTP_OK_CODE);
    }

    public function testRecentMotsByDateThrowError()
    {
        $this->setDvsaSiteSearchPermissions();
        $restController = $this->getRestClientMockForServiceManager();

        $this->getViewRendererMock();

        $restController->expects($this->once())
            ->method('getWithParams')
            ->will(
                $this->throwException(
                    new ValidationException(
                        '/',
                        'getWithParams ',
                        ['data' => null],
                        10,
                        [['displayMessage' => 'error']]
                    )
                )
            );

        $this->request->getQuery()->set('type', 'tester');
        $this->request->getHeaders()->addHeaders(['X_REQUESTED_WITH' => 'XMLHttpRequest']);

        $this->setPostAndPostParams(['format' => 'invalidFormat']);
        $this->getResultForAction('examinerFetchMotTestByDate');

        $this->assertResponseStatus(self::HTTP_OK_CODE);
    }

    protected function getMotSearchResult()
    {
        return [
            'data' => [
                'totalResultCount' => 10,
                'resultCount' => 10,
                'data' => [
                    2007 => [
                        'status' => 'PASSED',
                        'primaryColour' => 'Silver',
                        'hasRegistration' => true,
                        'odometerReading' => [
                            'value' => 11111,
                            'unit' => 'mi',
                         ],
                        'vin' => '1M5GDM9AXFT042755',
                        'registration' => 'F123ABC',
                        'make' => 'Vauxhall',
                        'model' => 'Astra',
                        'testType' => 'NT',
                        'siteNumber' => 'V1264',
                        'startedDate' => '2014-02-18T11:47:21Z',
                        'completedDate' => '2014-02-18T11:47:21Z',
                        'testerUsername' => 'ft-catb',
                    ],
                ],
            ],
        ];
    }

    protected function setDvsaSiteSearchPermissions()
    {
        $this->setupAuthorizationService([PermissionInSystem::DVSA_SITE_SEARCH]);
    }
}
