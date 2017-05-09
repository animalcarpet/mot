<?php

namespace Application\Helper;

use DvsaCommon\Guid\Guid;
use Zend\Http\Request;
use Zend\Session\Container;

/**
 * Post-Redirect-Get helper. Protect a form from double post.
 */
class PrgHelper
{
    const FORM_GUID_FIELD_NAME = 'prgToken';

    private $session;
    private $guid;

    public function __construct(Request $request)
    {
        $this->session = new Container('prgHelperSession');

        $this->isPost = (bool) $request->isPost();
        if ($this->isPost) {
            $this->guid = $request->getPost(self::FORM_GUID_FIELD_NAME);
        }
    }

    public function getHtml()
    {
        $guid = Guid::newGuid();

        return '<input type="hidden" name="'.self::FORM_GUID_FIELD_NAME.'" value="'.$guid.'">';
    }

    public function isRepeatPost()
    {
        return $this->isPost && ($this->session->offsetGet($this->guid) !== null);
    }

    public function getRedirectUrl()
    {
        return $this->session->offsetGet($this->guid);
    }

    public function setRedirectUrl($url)
    {
        if ($this->guid !== null && $this->isPost) {
            $this->session->offsetSet($this->guid, $url);
        }
    }
}
