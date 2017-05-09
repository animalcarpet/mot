<?php

namespace Csrf;

use Zend\View\Helper\AbstractHelper;

/**
 * Class CsrfTokenViewHelper.
 */
class CsrfTokenViewHelper extends AbstractHelper
{
    /** @var CsrfSupport */
    private $csrfSupport;

    public function __construct(CsrfSupport $csrfSupport)
    {
        $this->csrfSupport = $csrfSupport;
    }

    /**
     * @param bool $html Indicates whether html input or just the token should be generated
     *
     * @return string
     */
    public function __invoke($html = true)
    {
        $token = $this->csrfSupport->getCsrfToken();

        return $html ? '<input type="hidden" name="'.CsrfConstants::REQ_TOKEN.'" value="'.$token.'">' : $token;
    }
}
