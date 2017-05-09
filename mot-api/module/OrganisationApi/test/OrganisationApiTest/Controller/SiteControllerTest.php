<?php

namespace OrganisationApiTest\Controller;

use DvsaCommonApiTest\Controller\AbstractRestfulControllerTestCase;
use DvsaCommonTest\TestUtils\XMock;
use OrganisationApi\Controller\SiteController;
use OrganisationApi\Service\SiteService;
use PHPUnit_Framework_MockObject_MockObject as MockObj;

/**
 * Class SiteControllerTest.
 */
class SiteControllerTest extends AbstractRestfulControllerTestCase
{
    const AE_ID = 111;

    /**
     * @var SiteService|MockObj
     */
    private $mockSiteSrv;
    /**
     * @var SiteController
     */
    protected $controller;

    protected function setUp()
    {
        $this->mockSiteSrv = $this->getSiteServiceMock();

        $this->setController(
            new SiteController($this->mockSiteSrv)
        );

        parent::setUp();
    }

    public function testGetListWithOrganisationIdCanBeAccessed()
    {
        //given
        $this->routeMatch->setParam('id', self::AE_ID);

        //when
        $result = $this->controller->getList();

        //then
        $this->assertInstanceOf(\Zend\View\Model\JsonModel::class, $result);
    }

    private function getSiteServiceMock()
    {
        $this->mockSiteSrv = XMock::of(SiteService::class);

        $this->mockSiteSrv->expects($this->once())
            ->method('getListForOrganisation')
            ->with(self::AE_ID)
            ->will($this->returnValue([]));

        return $this->mockSiteSrv;
    }
}
