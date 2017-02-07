<?php

namespace DvsaCommonApiTest\Specification;

use DvsaCommon\Specification\AbstractSpecification;
use DvsaCommon\Specification\NotSpecification;
use DvsaCommonTest\Specification\Stubs\FalseSpecification;
use DvsaCommonTest\Specification\Stubs\TrueSpecification;

class NotSpecificationTest extends \PHPUnit_Framework_TestCase
{
    /** @var AbstractSpecification */
    private $trueSpec;
    /** @var AbstractSpecification */
    private $falseSpec;


    public function setUp()
    {
        $this->trueSpec = new TrueSpecification();
        $this->falseSpec = new FalseSpecification();
    }

    public function testNotSpecificationInstanceIsReturned()
    {
        $result = $this->trueSpec->not();
        $this->assertInstanceOf(NotSpecification::class, $result);
    }

    /**
     * @dataProvider testNegationDP
     * @param AbstractSpecification $spec
     * @param $expected
     */
    public function testNegation(AbstractSpecification $spec, $expected)
    {
        $result = $spec->not()->isSatisfiedBy(null);
        $this->assertEquals($expected, $result);
    }

    public function testDoubleNegation()
    {
        $result = $this->trueSpec->not()->not()->isSatisfiedBy(null);
        $this->assertEquals(true, $result);
    }

    public function testNegationDP()
    {
        $true = new TrueSpecification();
        $false = new FalseSpecification();

        return [
            [ $true, false ],
            [ $false, true ],
        ];
    }
}