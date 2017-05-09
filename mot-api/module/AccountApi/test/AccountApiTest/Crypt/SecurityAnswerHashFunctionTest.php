<?php

namespace AccountApiTest\Crypt;

use DvsaCommon\Crypt\Hash\BCryptHashFunction;
use AccountApi\Crypt\SecurityAnswerHashFunction;

class SecurityAnswerHashFunctionTest extends \PHPUnit_Framework_TestCase
{
    private static function securityAnswerHashFunction()
    {
        return (new SecurityAnswerHashFunction())->setBaseFunction(
            (new BCryptHashFunction())->setCost(BCryptHashFunction::MIN_COST)
        );
    }

    public function testVerify()
    {
        $secret = 'my_secret';
        $function = self::securityAnswerHashFunction();

        $hash = $function->hash($secret);
        $this->assertTrue($function->verify($secret, $hash));
    }
}
