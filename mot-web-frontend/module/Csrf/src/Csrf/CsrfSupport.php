<?php

namespace Csrf;

use DvsaCommon\Guid\Guid;
use Zend\Session\Container;

/**
 * Class CsrfSupport.
 */
class CsrfSupport
{
    /**
     * @var Container
     */
    protected $csrfSession;

    /**
     * @param Container $csrfSession
     */
    public function __construct(Container $csrfSession)
    {
        $this->csrfSession = $csrfSession;
    }

    /**
     * @return string
     */
    public function getCsrfToken()
    {
        if (!$this->csrfSession['csrfToken']) {
            $this->csrfSession['csrfToken'] = Guid::newGuid();
        }

        return $this->csrfSession['csrfToken'];
    }
}
