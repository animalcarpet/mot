<?php

namespace DvsaMotTestTest\Controller;

use Application\Data\ApiPersonalDetails;
use Core\Service\LazyMotFrontendAuthorisationService;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\UrlBuilder\UrlBuilder;
use DvsaCommonTest\Bootstrap;
use Dvsa\Mot\Frontend\Test\StubIdentityAdapter;
use DvsaCommonTest\TestUtils\XMock;
use DvsaMotTest\Controller\SpecialNoticesController;
use Zend\Http\Request;
use Zend\View\Renderer\PhpRenderer;
use Core\Authorisation\Assertion\WebAcknowledgeSpecialNoticeAssertion;

/**
 * Class SpecialNoticesControllerTest.
 *
 * @covers \DvsaMotTest\Controller\SpecialNoticesController
 */
class SpecialNoticesControllerTest extends AbstractDvsaMotTestTestCase
{
    protected function setUp()
    {
        $serviceManager = Bootstrap::getServiceManager();

        $markdown = $serviceManager->get('MaglMarkdown\MarkdownService');
        $controller = new SpecialNoticesController(
            $markdown,
            XMock::of(WebAcknowledgeSpecialNoticeAssertion::class),
            XMock::of(ApiPersonalDetails::class)
        );

        $this->setServiceManager($serviceManager);
        $this->setController($controller);

        parent::setUp();
    }

    public function testSpecialNoticesForAuthenticatedRequestCanBeAccessed()
    {
        // given
        $this->getRestClientMock('get', $this->getSpecialNoticesData(), "person/1/special-notice");

        // when
        $response = $this->getResponseForAction('displaySpecialNotices');

        // then
        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * @expectedException \DvsaCommon\Exception\UnauthorisedException
     */
    public function testSpecialNoticesForUnauthenticatedRequestThrowsException()
    {
        $this->setupAuthenticationServiceForIdentity(StubIdentityAdapter::asAnonymous());

        $this->getResponseForAction('displaySpecialNotices');
    }

    public function testSpecialNoticesOnAcknowledgePostShouldPostToApiAndRedirectToList()
    {
        //given
        $specialNoticeId = 134;

        $restClientMock = $this->getRestClientMockForServiceManager($this->restClientServiceName);

        $restClientMock->expects($this->once())
            ->method('postJson')
            ->with("person/1/special-notice/$specialNoticeId", ['isAcknowledged' => true])
            ->will($this->returnValue($this->getSpecialNoticesData()));
        $this->request->setMethod('post');
        $this->routeMatch->setParam('action', 'acknowledgeSpecialNotice');
        $this->routeMatch->setParam('id', $specialNoticeId);

        //when
        $result = $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();

        //then
        $this->assertEquals(302, $response->getStatusCode());
        $locationHeader = $response->getHeaders()->get('location');
        $this->assertEquals($locationHeader->getUri(), "/special-notices");
    }

    public function testCreateSpecialNoticeForAuthenticatedRequestCanBeAccessed()
    {
        $mockAuth = XMock::of(LazyMotFrontendAuthorisationService::class);
        $mockAuth->expects($this->any())
            ->method('assertGranted')
            ->with(PermissionInSystem::SPECIAL_NOTICE_CREATE);
        $this->serviceManager->setService('AuthorisationService', $mockAuth);

        $response = $this->getResponseForAction('createSpecialNotice');

        // then
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testCreateSpecialNoticeWithValidDataShouldPostToApiAndRedirectToPreview()
    {
        // given

        $restClientMock = $this->getRestClientMockForServiceManager($this->restClientServiceName);
        $mockSpecialNoticeContentData = $this->getTestSpecialNoticeContentData();
        $id = 1;

        $restClientMock->expects($this->once())
            ->method('postJson')
            ->will($this->returnValue($this->getSpecialNoticesReturnData()));

        $this->request->setMethod('post');
        $this->routeMatch->setParam('action', 'createSpecialNotice');
        $this->request->getPost()->set('noticeTitle', $mockSpecialNoticeContentData['noticeTitle']);
        $this->request->getPost()->set(
            'internalPublishDate',
            $mockSpecialNoticeContentData['internalPublishDate']
        );
        $this->request->getPost()->set(
            'externalPublishDate',
            $mockSpecialNoticeContentData['externalPublishDate']
        );
        $this->request->getPost()->set('acknowledgementPeriod', $mockSpecialNoticeContentData['acknowledgementPeriod']);
        $this->request->getPost()->set('targetRoles', $mockSpecialNoticeContentData['targetRoles']);
        $this->request->getPost()->set('noticeText', $mockSpecialNoticeContentData['noticeText']);

        $mockAuth = XMock::of(LazyMotFrontendAuthorisationService::class);
        $mockAuth->expects($this->any())
            ->method('assertGranted')
            ->with(PermissionInSystem::SPECIAL_NOTICE_CREATE);
        $this->serviceManager->setService('AuthorisationService', $mockAuth);

        // when
        $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();

        // then
        $this->assertEquals(302, $response->getStatusCode());
        $locationHeader = $response->getHeaders()->get('location');
        $this->assertEquals($locationHeader->getUri(), "/special-notices/" . $id . "/preview");
    }

    /**
     * @expectedException \DvsaCommon\Exception\UnauthorisedException
     */
    public function testCreateSpecialNoticeForUnauthenticatedRequestThrowsException()
    {
        $this->setupAuthenticationServiceForIdentity(StubIdentityAdapter::asAnonymous());

        $this->getResponseForAction('createSpecialNotice');
    }

    public function testPreviewSpecialNoticeForAuthenticatedRequestCanBeAccessed()
    {
        $this->setupAuthenticationServiceForIdentity(StubIdentityAdapter::asSchemauser());

        // given
        $id = 1;

        $this->getRestClientMock('get', $this->getSpecialNoticesReturnData(), "special-notice-content/$id");
        $this->routeMatch->setParam('id', $id);

        // when
        $response = $this->getResponseForAction('previewSpecialNotice');

        // then
        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * @expectedException \DvsaCommon\Exception\UnauthorisedException
     */
    public function testPreviewSpecialNoticeForUnauthenticatedRequestThrowsException()
    {
        $this->setupAuthenticationServiceForIdentity(StubIdentityAdapter::asAnonymous());

        $this->getResponseForAction('previewSpecialNotice');
    }

    public function testPreviewSpecialNoticeForGetShouldReturnSpecialNotice()
    {
        $this->setupAuthenticationServiceForIdentity(StubIdentityAdapter::asSchemauser());

        // given
        $id = 1;

        $this->getRestClientMock('get', $this->getSpecialNoticesReturnData(), "special-notice-content/$id");
        $this->routeMatch->setParam('id', $id);

        //when
        $response = $this->getResponseForAction('previewSpecialNotice');

        //then
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testPreviewSpecialNoticeForPostShouldUpdateStatusAndReturnSpecialNotice()
    {
        $this->setupAuthenticationServiceForIdentity(StubIdentityAdapter::asSchemauser());

        // given
        $id = 1;

        $this->request->setMethod('post');
        $this->routeMatch->setParam('action', 'previewSpecialNotice');
        $this->routeMatch->setParam('id', $id);
        $restClientMock = $this->getRestClientMockForServiceManager($this->restClientServiceName);
        $restClientMock->expects($this->once())
            ->method('putJson')
            ->with("special-notice-content/$id/publish", [])
            ->will($this->returnValue($this->getSpecialNoticesReturnData()));

        //when
        $result = $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();

        //then
        $this->assertEquals(302, $response->getStatusCode());
        $locationHeader = $response->getHeaders()->get('location');
        $this->assertEquals($locationHeader->getUri(), "/special-notices/all");
    }

    public function testPrintSpecialNoticeForAuthenticatedRequestCanBeAccessed()
    {
        // given
        $id = 1;

        $this->getRestClientMock('get', $this->getSpecialNoticesReturnData(), "special-notice-content/$id");
        $this->routeMatch->setParam('id', $id);

        // when
        $response = $this->getResponseForAction('printSpecialNotice');

        // then
        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * Test that the htmlPurifier helper is available in the print-special-notice view.
     *
     * @throws \Exception
     */
    public function testPrintSpecialNoticeViewHasRequiredHelpers()
    {
        $serviceManager = $this->getServiceManager();
        $view           = $serviceManager->get('ViewHelperManager');

        $htmlPurifier   = $view->get('htmlPurifier');
        $this->assertInstanceOf('Soflomo\Purifier\View\Helper\Purifier', $htmlPurifier);
        $this->assertInstanceOf('Zend\View\Helper\HelperInterface', $htmlPurifier);
    }

    public function testPrintSpecialNoticeWithPdfExtensionGeneratesPdf()
    {
        $id     = 1;
        $params = [
            'id'        => $id,
            'extension' => '.pdf',
        ];

        // Prepare API response
        $specialNoticeContentApiPath = (new UrlBuilder())
            ->specialNoticeContent()
            ->routeParam('id', $id)
            ->toString();
        $specialNotice = $this->getSpecialNoticesReturnData();
        $specialNotice['data']['noticeText'] = file_get_contents(__DIR__ . '/fixtures/special-notice-text.md');
        $this->getRestClientMock('get', $specialNotice, $specialNoticeContentApiPath);

        // Mock ViewRenderer
        $this->getServiceManager()->setService('ViewRenderer', XMock::of(PhpRenderer::class));

        $response = $this->getResponseForAction('printSpecialNotice', $params);
        $headers  = $response->getHeaders();

        // Status code
        $this->assertResponseStatus(self::HTTP_OK_CODE);

        // Check Content-Type
        $this->assertTrue($headers->has('Content-Type'));
        $this->assertEquals('application/pdf', $headers->get('Content-Type')->getFieldValue());

        // Check Content-Disposition
        $this->assertTrue($headers->has('Content-Disposition'));
        $this->assertEquals(
            'attachment; filename="Special Notice.pdf"',
            $headers->get('Content-Disposition')->getFieldValue()
        );

        $this->assertNotEmpty($response->getBody());
    }

    /**
     * @return array
     */
    protected function getSpecialNoticesData()
    {
        $specialNotices = [
            "data" => [
                    [
                        "id"             => 2,
                        "isAcknowledged" => false,
                        "content"        => [
                            "title"       => "Expired special notice",
                            "noticeText"  => "Happy birthday!",
                            "issueDate"   => "1989-02-10",
                            "issueNumber" => "2-1989",
                            "issueYear"   => 1989,
                            "expiryDate"  => "1989-02-26",
                            "targetRoles" => [],
                        ],
                        "isExpired"      => true,
                    ],
                    [
                        "id"             => 5,
                        "isAcknowledged" => true,
                        "content"        => [
                            "title"       => "Another special notice",
                            "noticeText"  => "Testing!",
                            "issueDate"   => "2020-02-10",
                            "issueNumber" => "2-1989",
                            "issueYear"   => 2020,
                            "expiryDate"  => "2022-02-26",
                            "targetRoles" => [],
                        ],
                        "isExpired"      => true
                    ],
                ],
        ];

        return $specialNotices;
    }

    /**
     * @return array
     */
    protected function getSpecialNoticesReturnData()
    {
        $specialNotice = [
            "data" => [
                    'version'             => 1,
                    'title'               => 'test title',
                    'issueNumber'         => '1-2014',
                    'issueYear'           => '2014',
                    'issueDate'           => '2014-01-15',
                    'expiryDate'          => '2014-01-31',
                    'internalPublishDate' => [
                        'date'          => '2014-14-01',
                        'timeZone_type' => 3,
                        'timeZone'      => 'EuropeLondon',
                    ],
                    'externalPublishDate' => [
                        'date'          => '2014-15-01',
                        'timeZone_type' => 3,
                        'timeZone'      => 'EuropeLondon',
                    ],
                    'noticeText'          => 'This is the body of the message',
                    'status'              => 'DRAFT',
                    'id'                  => 1,
                    'targetRoles'         => [
                        'TESTER-CLASS-1',
                        'TESTER-CLASS-2',
                    ],
                ],
        ];

        return $specialNotice;
    }

    /**
     * @return array
     */
    protected function getTestSpecialNoticeContentData()
    {
        $date = new \DateTime("tomorrow");
        $dateArray = [
            'day'   => $date->format("d"),
            'month' => $date->format("m"),
            'year'  => $date->format("Y")
        ];

        return [
            'noticeTitle'              => 'test title',
            'internalPublishDate'      => $dateArray,
            'externalPublishDate'      => $dateArray,
            'acknowledgementPeriod'    => 16,
            'vehicleTestClass'         => true,
            'allClasses'               => null,
            'targetRoles'              => [0 => 'TESTER-CLASS-1', 1 => 'TESTER-CLASS-2'],
            'noticeText'               => 'This is the body of the message',
        ];
    }
}
