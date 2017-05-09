<?php

namespace DvsaMotApiTest\Service\RfrValidator;

use PHPUnit_Framework_TestCase;
use DvsaMotApi\Service\RfrValidator\BaseResultValidator;

/**
 * Class BaseResultValidatorTest.
 */
class BaseResultValidatorTest extends PHPUnit_Framework_TestCase
{
    public function testContructParams()
    {
        $validator = new BaseResultValidator(array(), 0);
        $this->assertInstanceOf(\DvsaMotApi\Service\RfrValidator\BaseValidator::class, $validator);
        $this->assertInstanceOf(\DvsaMotApi\Service\RfrValidator\BaseResultValidator::class, $validator);
    }

    public function testFluentInterface()
    {
        $validator = new BaseResultValidator(array(), 0);
        $validator->setCalculatedScore(1)
            ->setValues(array(1))
            ->setError('test');

        $this->assertEquals($validator->getCalculatedScore(), 1);
        $this->assertEquals($validator->getValues(), array(1));
        $this->assertEquals($validator->getError(), 'test');
    }

    public function testValidateDefault()
    {
        $validator = new BaseResultValidator(0, array());
        $this->assertEquals(false, $validator->validate());
    }
}
