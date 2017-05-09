<?php

namespace DvsaAuthenticationTest\Service;

use DvsaAuthentication\Service\OtpServiceAdapter;
use DvsaAuthentication\Service\OtpServiceAdapter\PinOtpServiceAdapter;
use DvsaCommon\Crypt\Hash\BCryptHashFunction;
use DvsaCommonApiTest\Service\AbstractServiceTest;
use DvsaEntities\Entity\Person;

class PinOtpServiceAdapterTest extends AbstractServiceTest
{
    const VALID_TOKEN = '123456';

    const INVALID_TOKEN = '000000';

    /**
     * @var PinOtpServiceAdapter
     */
    private $otpServiceAdapter;

    protected function setUp()
    {
        $this->otpServiceAdapter = new PinOtpServiceAdapter();
    }

    public function testItIsAnOtpServiceAdapter()
    {
        $this->assertInstanceOf(OtpServiceAdapter::class, $this->otpServiceAdapter);
    }

    public function testItReturnsTrueIfTokenIsValid()
    {
        $person = (new Person())->setPin($this->hash(self::VALID_TOKEN));

        $this->assertTrue($this->otpServiceAdapter->authenticate($person, self::VALID_TOKEN));
    }

    public function testItReturnsFalseIfTokenIsInvalid()
    {
        $person = (new Person())->setPin($this->hash(self::VALID_TOKEN));

        $this->assertFalse($this->otpServiceAdapter->authenticate($person, self::INVALID_TOKEN));
    }

    private function hash($text)
    {
        return (new BCryptHashFunction())->hash($text);
    }
}
