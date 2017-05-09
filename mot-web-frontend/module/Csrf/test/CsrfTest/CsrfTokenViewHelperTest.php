<?php

namespace CsrfTest;

use Csrf\CsrfConstants;
use Csrf\CsrfSupport;
use Csrf\CsrfTokenViewHelper;
use DvsaCommonTest\TestUtils\XMock;

/**
 * Class CsrfTokenViewHelperTest.
 */
class CsrfTokenViewHelperTest extends \PHPUnit_Framework_TestCase
{
    const TEST_TOKEN = 'tokenABCDE';

    private function tokenViewHelper()
    {
        $csrfSupport = XMock::of(CsrfSupport::class);
        $csrfSupport->expects($this->any())->method('getCsrfToken')
            ->will($this->returnValue(self::TEST_TOKEN));
        $helper = new CsrfTokenViewHelper($csrfSupport);

        return $helper;
    }

    public function testInvoke_givenHtmlRequested_shouldReturnInputElement()
    {
        $token = self::TEST_TOKEN;
        $helper = $this->tokenViewHelper();

        $html = $helper();

        $this->assertEquals(
            '<input type="hidden" name="'.CsrfConstants::REQ_TOKEN."\" value=\"$token\">",
            $html
        );
    }

    public function testInvoke_givenJustTokenRequested_shouldReturnVanillaToken()
    {
        $token = self::TEST_TOKEN;
        $helper = $this->tokenViewHelper();

        $result = $helper(false);

        $this->assertEquals($token, $result);
    }
}
