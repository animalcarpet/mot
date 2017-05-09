<?php

namespace ApplicationTest\Navigation\Breadcrumbs;

use Application\Navigation\Breadcrumbs\BreadcrumbsBuilder;
use Application\Navigation\Breadcrumbs\Handler\BreadcrumbsPartResolver;
use DvsaCommonTest\TestUtils\XMock;
use Zend\ServiceManager\ServiceManager;
use Zend\View\Helper\Layout;
use Zend\View\Model\ViewModel;

class BreadcrumbsBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var BreadcrumbsBuilder
     */
    private $builder;

    private $siteHandler;
    private $organisationBySiteHandler;
    private $simpleHandler;
    private $serviceLocator;
    private $viewModel;

    public function setUp()
    {
        $this->siteHandler = XMock::of(BreadcrumbsPartResolver::class);
        $this->organisationBySiteHandler = XMock::of(BreadcrumbsPartResolver::class);
        $this->simpleHandler = XMock::of(BreadcrumbsPartResolver::class);
        $this->serviceLocator = new ServiceManager();
        $this->serviceLocator->setService('site', $this->siteHandler);
        $this->serviceLocator->setService('organisation', $this->organisationBySiteHandler);
        $this->serviceLocator->setService('simple', $this->simpleHandler);
        $theLayout = XMock::of(Layout::class);
        $this->viewModel = new ViewModel();
        $theLayout->expects($this->any())->method('__invoke')->willReturn($this->viewModel);

        $resolvers = [
            'site' => 'site',
            'organisationBySite' => 'organisation',
            'simple' => 'simple',
        ];
        $this->builder = new BreadcrumbsBuilder($resolvers, $this->serviceLocator, $theLayout);
    }

    public function testSite()
    {
        $siteId = 5;
        $this->siteHandler->expects($this->once())->method('resolve')->with($siteId);
        $this->builder->site($siteId);
    }

    public function testOrganisationBySite()
    {
        $siteId = 5;
        $this->organisationBySiteHandler->expects($this->once())->method('resolve')->with($siteId);
        $this->builder->organisationBySiteId($siteId);
    }

    public function testSimple()
    {
        $label = 'label1';
        $route = 'route1';
        $params = ['params1'];
        $this->simpleHandler->expects($this->once())->method('resolve')
            ->with(['label' => $label, 'link' => ['route' => $route, 'params' => $params]]);
        $this->builder->simple($label, $route, $params);
    }

    public function testBuild()
    {
        $this->siteHandler->expects($this->any())->method('resolve')
            ->willReturn(['SiteName' => 'siteLink']);
        $this->organisationBySiteHandler->expects($this->any())->method('resolve')
            ->willReturn(['OrganisationName' => 'organisationLink']);

        $this->builder->site(5)->organisationBySiteId(3)->build();
        $expectedBreadcrumbs = [
            'breadcrumbs' => [
                ['SiteName' => 'siteLink'],
                ['OrganisationName' => 'organisationLink'],
            ],
        ];
        $this->assertEquals($expectedBreadcrumbs, $this->viewModel->getVariables()['breadcrumbs']);
    }

    public function testBuild_whenElementCannotBeResolved_shouldBeSkipped()
    {
        $this->siteHandler->expects($this->any())->method('resolve')
            ->willReturn([]);
        $this->organisationBySiteHandler->expects($this->any())->method('resolve')
            ->willReturn(['OrganisationName' => 'organisationLink']);

        $this->builder->site(5)->organisationBySiteId(3)->build();
        $expectedBreadcrumbs = [
            'breadcrumbs' => [
                ['OrganisationName' => 'organisationLink'],
            ],
        ];
        $this->assertEquals($expectedBreadcrumbs, $this->viewModel->getVariables()['breadcrumbs']);
    }
}
