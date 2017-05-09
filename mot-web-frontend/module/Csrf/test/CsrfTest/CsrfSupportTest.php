<?php

namespace Csrf;

use Zend\Session\Container as SessionContainer;

/**
 * Class CsrfSupportTest.
 */
class CsrfSupportTest extends \PHPUnit_Framework_TestCase
{
    public function testGetCsrfToken_forNewSession_returnsNonNullToken()
    {
        $csrfSession = new SessionContainer('csrf');
        $csrfSupport = new CsrfSupport($csrfSession);

        $this->assertNotNull($csrfSupport->getCsrfToken());
    }

    public function testGetCsrfToken_forExistingSession_returnsExistingToken()
    {
        $existingToken = 'existingToken';
        $csrfSession = new SessionContainer('csrf');
        $csrfSession['csrfToken'] = $existingToken;
        $csrfSupport = new CsrfSupport($csrfSession);

        $this->assertTrue($csrfSupport->getCsrfToken() == $existingToken);
    }
}
