<?php

namespace ApplicationTest\Service\ReportBuilder;

use Application\Helper\PrgHelper;
use DvsaCommon\UrlBuilder\MotTestUrlBuilder;
use DvsaCommonTest\TestUtils\XMock;
use PHPUnit_Framework_TestCase;
use Zend\Http\Request;

/**
 * Class PrgHelperTest.
 */
class PrgHelperTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider dataProviderTest
     */
    public function test($isPost, $guid, $url, $expectUrl, $expectIsSecondPost)
    {
        $mockRequest = XMock::of(Request::class);
        $mockRequest->expects($this->any())
            ->method('isPost')
            ->willReturn($isPost);

        $mockRequest->expects($this->any())
            ->method('getPost')
            ->with(PrgHelper::FORM_GUID_FIELD_NAME)
            ->willReturn($guid);

        $helper = new PrgHelper($mockRequest);
        $helper->setRedirectUrl($url);

        $this->assertSame($expectUrl, $helper->getRedirectUrl(), 'Unexpected Url');
        $this->assertSame($expectIsSecondPost, $helper->isRepeatPost(), 'Unexpected isSecondPost');
    }

    public function dataProviderTest()
    {
        $url = MotTestUrlBuilder::motTest('123456');

        $guid = 'A0123456';

        return [
            [true, $guid, $url->toString(), $url->toString(), true],
        ];
    }

    public function testGetHtml()
    {
        $helper = new PrgHelper(new Request());

        $html = $helper->getHtml();

        $dom = new \DOMDocument();
        $dom->loadHTML($html);

        $elmAttr = $dom->getElementsByTagName('input')->item(0)->attributes;

        $this->assertSame('hidden', $elmAttr->getNamedItem('type')->nodeValue);
        $this->assertSame(PrgHelper::FORM_GUID_FIELD_NAME, $elmAttr->getNamedItem('name')->nodeValue);
        $this->assertNotEmpty($elmAttr->getNamedItem('value')->nodeValue);
    }
}
