<?php

namespace UserApiTest\SpecialNotice\Controller;

use DvsaCommonApiTest\Controller\AbstractRestfulControllerTestCase;
use DvsaCommonTest\Bootstrap;
use DvsaEntities\Entity\SpecialNoticeContent;
use UserApi\SpecialNotice\Controller\SpecialNoticeContentController;
use UserApi\SpecialNotice\Service\SpecialNoticeService;
use Zend\Stdlib\Parameters;

/**
 * Class SpecialNoticeContentControllerTest.
 */
class SpecialNoticeContentControllerTest extends AbstractRestfulControllerTestCase
{
    protected function setUp()
    {
        $this->controller = new SpecialNoticeContentController();
        parent::setUp();
    }

    public function testGetList_getRemovedSpecialNotices_shouldReturnSpecialNotice()
    {
        // given
        $specialNoticeContent = new SpecialNoticeContent();
        $expectedData = ['data' => $specialNoticeContent];

        $this->mockValidAuthorization();
        $mockService = $this->getMockServiceManagerClass(SpecialNoticeService::class, SpecialNoticeService::class);
        $mockService->expects($this->once())
            ->method('getRemovedSpecialNotices')
            ->will($this->returnValue($specialNoticeContent));

        $this->request->setQuery(new Parameters(['removed' => 'true']));

        // when
        $result = $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();

        // then
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertInstanceOf(\Zend\View\Model\JsonModel::class, $result);
        $this->assertEquals($expectedData, $result->getVariables());
    }

    public function testGetList_getAllCurrentSpecialNotices_shouldReturnSpecialNotice()
    {
        // given
        $specialNoticeContent = new SpecialNoticeContent();
        $expectedData = ['data' => $specialNoticeContent];

        $this->mockValidAuthorization();
        $mockService = $this->getMockServiceManagerClass(SpecialNoticeService::class, SpecialNoticeService::class);
        $mockService->expects($this->once())
            ->method('getAllCurrentSpecialNotices')
            ->will($this->returnValue($specialNoticeContent));

        // when
        $result = $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();

        // then
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertInstanceOf(\Zend\View\Model\JsonModel::class, $result);
        $this->assertEquals($expectedData, $result->getVariables());
    }

    public function testGetList_getAllSpecialNotices_shouldReturnSpecialNotice()
    {
        // given
        $specialNoticeContent = new SpecialNoticeContent();
        $expectedData = ['data' => $specialNoticeContent];

        $this->mockValidAuthorization();
        $mockService = $this->getMockServiceManagerClass(SpecialNoticeService::class, SpecialNoticeService::class);
        $mockService->expects($this->once())
            ->method('getAllSpecialNotices')
            ->will($this->returnValue($specialNoticeContent));

        $this->request->setQuery(new Parameters(['listAll' => 'true']));

        // when
        $result = $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();

        // then
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertInstanceOf(\Zend\View\Model\JsonModel::class, $result);
        $this->assertEquals($expectedData, $result->getVariables());
    }

    public function testUpdateInvokesService()
    {
        // given
        $testId = 4;
        $testData = ['isPublished' => true];
        $this->routeMatch->setParam('id', $testId);
        $this->request->setMethod('put');
        $this->request->setContent(json_encode($testData));

        $this->mockValidAuthorization();
        $mockService = $this->getMockServiceManagerClass(SpecialNoticeService::class, SpecialNoticeService::class);
        $mockService->expects($this->once())
            ->method('update')
            ->with($testId)
            ->will($this->returnValue(new SpecialNoticeContent()));

        $this->controller->dispatch($this->request);
        $this->controller->getResponse();
    }

    public function testCreate_withValidData_shouldCreateNewSpecialNotice()
    {
        // given
        $this->mockValidAuthorization();

        $data = ['noticeTitle' => 'Test Title',
                 'issueNumber' => '1',
                 'issueYear' => '2014',
                 'issueDate' => '2014-01-01',
                 'expiryDate' => '2022-01-01',
                 'internalPublishDate' => '2014-01-01',
                 'externalPublishDate' => '2014-01-10',
                 'noticeText' => 'This is a test notice.',
                 'targetRoles' => [0 => 'TESTER-CLASS-1', 1 => 'TESTER-CLASS-2'],
        ];

        $this->request->setMethod('post');
        $this->request->getPost()->set('noticeTitle', $data['noticeTitle']);
        $this->request->getPost()->set('issueNumber', $data['issueNumber']);
        $this->request->getPost()->set('issueYear', $data['issueYear']);
        $this->request->getPost()->set('issueDate', $data['issueDate']);
        $this->request->getPost()->set('expiryDate', $data['expiryDate']);
        $this->request->getPost()->set('internalPublishDate', $data['internalPublishDate']);
        $this->request->getPost()->set('externalPublishDate', $data['externalPublishDate']);
        $this->request->getPost()->set('noticeText', $data['noticeText']);
        $this->request->getPost()->set('targetRoles', $data['targetRoles']);

        $specialNoticeContent = new SpecialNoticeContent();

        $expectedData = ['data' => $specialNoticeContent];

        $specialNoticeServiceMock = \DvsaCommonTest\TestUtils\XMock::of(SpecialNoticeService::class);

        $serviceManager = Bootstrap::getServiceManager();
        $serviceManager->setAllowOverride(true);
        $serviceManager->setService(SpecialNoticeService::class, $specialNoticeServiceMock);

        $specialNoticeServiceMock->expects($this->once())
            ->method('createSpecialNotice')
            ->with($data)
            ->will($this->returnValue($specialNoticeContent));

        // when
        $result = $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();

        // then
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertInstanceOf(\Zend\View\Model\JsonModel::class, $result);
        $this->assertEquals($expectedData, $result->getVariables());
    }
}
