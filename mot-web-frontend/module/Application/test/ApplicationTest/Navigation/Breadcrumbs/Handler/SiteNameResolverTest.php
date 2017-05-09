<?php

namespace ApplicationTest\Navigation\Breadcrumbs\Handler;

use Application\Navigation\Breadcrumbs\Handler\SiteNameResolver;
use DvsaCommon\HttpRestJson\Client;
use DvsaCommonTest\TestUtils\XMock;
use Zend\View\Helper\Url;

class SiteNameResolverTest extends \PHPUnit_Framework_TestCase
{
    private $urlHelper;
    private $resolver;
    private $client;

    public function setUp()
    {
        $this->urlHelper = XMock::of(Url::class);
        $this->client = XMock::of(Client::class);
        $this->resolver = new SiteNameResolver($this->client, $this->urlHelper);
    }

    public function testResolve_shouldReturnProperLink()
    {
        $siteId = 4;
        $this->urlHelper->expects($this->once())->method('__invoke')
            ->with('vehicle-testing-station', ['id' => $siteId])->willReturn('url');
        $this->client->expects($this->once())->method('get')->willReturn(['data' => 'SiteName']);

        $result = $this->resolver->resolve($siteId);

        $this->assertEquals(['SiteName' => 'url'], $result);
    }
}
