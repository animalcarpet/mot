<?php

namespace OrganisationApiTest\Controller;

use DvsaCommonApiTest\Controller\AbstractRestfulControllerTestCase;
use DvsaCommonTest\Bootstrap;
use DvsaCommonTest\TestUtils\XMock;
use OrganisationApi\Controller\AuthorisedExaminerPrincipalController;
use OrganisationApi\Service\AuthorisedExaminerPrincipalService;

/**
 * Class AuthorisedExaminerPrincipalControllerTest.
 *
 * @property-read AuthorisedExaminerPrincipalController $controller
 */
class AuthorisedExaminerPrincipalControllerTest extends AbstractRestfulControllerTestCase
{
    private $authorisedExaminerId = 1;
    private $authorisedExaminerPrincipalId = 2;
    private $authorisedExaminerPrincipalService;

    protected function setUp()
    {
        $this->controller = new AuthorisedExaminerPrincipalController();
        $this->authorisedExaminerPrincipalService = $this->getAuthorisedExaminerPrincipalService();
        $this->setupServiceManager();

        parent::setUp();
    }

    public function testGetListWithOrganisationIdCanBeAccessed()
    {
        //given
        $this->routeMatch->setParam('authorisedExaminerId', $this->authorisedExaminerId);

        //when
        $result = $this->controller->getList();

        //then
        $this->assertInstanceOf("Zend\View\Model\JsonModel", $result);
    }

    private function getAuthorisedExaminerPrincipalService()
    {
        $siteServiceMock = XMock::of(AuthorisedExaminerPrincipalService::class);

        $siteServiceMock->expects($this->any())
            ->method('getForAuthorisedExaminer')
            ->with($this->authorisedExaminerId)
            ->will($this->returnValue([]));

        $siteServiceMock->expects($this->any())
            ->method('getForAuthorisedExaminerById')
            ->with($this->authorisedExaminerId, $this->authorisedExaminerPrincipalId)
            ->will($this->returnValue([]));

        $siteServiceMock->expects($this->any())
            ->method('deletePrincipalForAuthorisedExaminer')
            ->with($this->authorisedExaminerId, $this->authorisedExaminerPrincipalId)
            ->will($this->returnValue([]));

        $siteServiceMock->expects($this->any())
            ->method('createForAuthorisedExaminer')
            ->with($this->authorisedExaminerId, [])
            ->will($this->returnValue([]));

        return $siteServiceMock;
    }

    private function setupServiceManager()
    {
        $serviceManager = Bootstrap::getServiceManager();
        $serviceManager->setAllowOverride(true);
        $serviceManager->setService(
            AuthorisedExaminerPrincipalService::class, $this->authorisedExaminerPrincipalService
        );

        $this->controller->setServiceLocator($serviceManager);
    }
}
