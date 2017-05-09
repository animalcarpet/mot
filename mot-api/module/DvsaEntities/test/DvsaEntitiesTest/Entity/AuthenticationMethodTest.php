<?php

namespace DvsaEntitiesTest\Entity;

use DvsaEntities\Entity\AuthenticationMethod;

class AuthenticationMethodTest extends \PHPUnit_Framework_TestCase
{
    public function testMethodIsCard()
    {
        $method = new AuthenticationMethod();

        $method->setCode(AuthenticationMethod::CARD_CODE);

        $this->assertTrue($method->isCard());
        $this->assertFalse($method->isPin());
    }

    public function testMethodIsPin()
    {
        $method = new AuthenticationMethod();

        $method->setCode(AuthenticationMethod::PIN_CODE);

        $this->assertFalse($method->isCard());
        $this->assertTrue($method->isPin());
    }
}
