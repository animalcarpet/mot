<?php

namespace OrganisationApiTest\Controller;

use DvsaCommonApiTest\Controller\AbstractRestfulControllerTestCase;
use DvsaCommonTest\Bootstrap;
use DvsaCommonTest\TestUtils\XMock;
use OrganisationApi\Controller\OrganisationRoleController;
use OrganisationApi\Service\OrganisationRoleService;

/**
 * Class OrganisationRoleControllerTest.
 */
class OrganisationRoleControllerTest extends AbstractRestfulControllerTestCase
{
    private $organisationId = 1;
    /** @var OrganisationRoleService|\PHPUnit_Framework_MockObject_MockObject */
    private $organisationRoleServiceMock;

    protected function setUp()
    {
        $this->controller = new OrganisationRoleController();
        $this->organisationRoleServiceMock = $this->getOrganisationRoleServiceMock();
        $this->setupServiceManager();

        parent::setUp();
    }

    public function testGetListCanBeAccessed()
    {
        //given
        $this->routeMatch->setParam('organisationId', $this->organisationId);

        //when
        $result = $this->controller->getList();

        //then
        $this->assertInstanceOf("Zend\View\Model\JsonModel", $result);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     *
     * @throws \Exception
     */
    private function getOrganisationRoleServiceMock()
    {
        $orgServiceMock = XMock::of(OrganisationRoleService::class);

        $orgServiceMock->expects($this->once())
            ->method('getListForPerson')
            ->with($this->organisationId)
            ->will($this->returnValue([]));

        return $orgServiceMock;
    }

    private function setupServiceManager()
    {
        $serviceManager = Bootstrap::getServiceManager();
        $serviceManager->setAllowOverride(true);
        $serviceManager->setService(OrganisationRoleService::class, $this->organisationRoleServiceMock);

        $this->controller->setServiceLocator($serviceManager);
    }
}
