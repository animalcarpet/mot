<?php

namespace DvsaClientTest\Mapper;

use DvsaCommon\HttpRestJson\Client;
use DvsaCommon\UrlBuilder\AbstractUrlBuilder;
use DvsaCommonTest\TestUtils\TestCaseTrait;
use DvsaCommonTest\TestUtils\XMock;

abstract class AbstractMapperTest extends \PHPUnit_Framework_TestCase
{
    use TestCaseTrait;

    /** @var $client Client|\PHPUnit_Framework_MockObject_MockObject */
    protected $client;

    public function setUp()
    {
        $this->client = XMock::of(Client::class);
    }

    public function tearDown()
    {
        unset($this->client);
    }

    /**
     * @param AbstractUrlBuilder|string $url
     * @param mixed                     $return
     */
    protected function setupClientMockGet($url, $return)
    {
        $this->mockMethod($this->client, 'get', $this->once(), $return, [$this->getUrlAsString($url)]);
    }

    /**
     * @param AbstractUrlBuilder|string $url
     * @param mixed                     $data
     * @param mixed                     $return
     */
    protected function setupClientMockPost($url, $data, $return)
    {
        $this->mockMethod($this->client, 'post', $this->once(), $return, [$this->getUrlAsString($url), $data]);
    }

    /**
     * @param AbstractUrlBuilder|string $url
     * @param mixed                     $data
     * @param mixed                     $return
     */
    protected function setupClientMockPut($url, $data, $return)
    {
        $this->mockMethod($this->client, 'put', $this->once(), $return, [$this->getUrlAsString($url), $data]);
    }

    /**
     * @param AbstractUrlBuilder|string $url
     * @param mixed                     $return
     */
    protected function setupClientMockDelete($url, $return)
    {
        $this->mockMethod($this->client, 'delete', $this->once(), $return, $this->getUrlAsString($url));
    }

    /**
     * @param AbstractUrlBuilder|string $url
     *
     * @return string
     */
    private function getUrlAsString($url)
    {
        return is_object($url) ? $url->toString() : $url;
    }
}
