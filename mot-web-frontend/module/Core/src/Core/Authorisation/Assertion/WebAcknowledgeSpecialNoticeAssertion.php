<?php

namespace Core\Authorisation\Assertion;

use DvsaCommon\Auth\Assertion\AcknowledgeSpecialNoticeAssertion;

class WebAcknowledgeSpecialNoticeAssertion
{
    /**
     * @var AcknowledgeSpecialNoticeAssertion
     */
    private $assertion;

    /**
     * @param AcknowledgeSpecialNoticeAssertion $assertion
     */
    public function __construct(AcknowledgeSpecialNoticeAssertion $assertion)
    {
        $this->assertion = $assertion;
    }

    /**
     * @return bool
     */
    public function isGranted()
    {
        return $this->assertion->isGranted();
    }

    /**
     * @throws \DvsaCommon\Exception\UnauthorisedException
     */
    public function assertGranted()
    {
        $this->assertion->assertGranted();
    }
}
