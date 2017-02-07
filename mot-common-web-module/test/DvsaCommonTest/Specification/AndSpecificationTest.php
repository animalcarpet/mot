<?php

namespace DvsaCommonTest\Specification;

use DvsaCommon\Specification\AbstractSpecification;
use DvsaCommon\Specification\AndSpecification;
use DvsaCommon\Specification\SpecificationInterface;
use DvsaCommonTest\Specification\Stubs\FalseSpecification;
use DvsaCommonTest\Specification\Stubs\TrueSpecification;

class AndSpecificationTest extends \PHPUnit_Framework_TestCase
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

    public function testAndSpecificationInstanceIsReturned()
    {
        $result = $this->trueSpec->andSpecification($this->falseSpec);
        $this->assertInstanceOf(AndSpecification::class, $result);
    }

    /**
     * @dataProvider testLogicalAndOperationDP
     * @param $expectedResult
     * @param \DvsaCommon\Specification\SpecificationInterface[] $specifications
     */
    public function testLogicalAndOperation($expectedResult, SpecificationInterface ...$specifications)
    {
        $andSpec = new AndSpecification(...$specifications);
        $result = $andSpec->isSatisfiedBy(null);
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @dataProvider testFluidInterfaceAndDP
     * @param $expectedResult
     * @param AbstractSpecification $originalSpec
     * @param \DvsaCommon\Specification\SpecificationInterface[] ...$specifications
     */
    public function testFluidInterfaceAnd($expectedResult, AbstractSpecification $originalSpec, SpecificationInterface ...$specifications)
    {
        $compositeSpec = $originalSpec->andSpecification(...$specifications);
        $result = $compositeSpec->isSatisfiedBy(null);
        $this->assertEquals($expectedResult, $result);
    }

    public function testLogicalAndOperationDP()
    {
        $true = new TrueSpecification();
        $false = new FalseSpecification();

        return [
            [ true, $true , $true ],
            [ true, $true , $true, $true ],
            [ false, $true , $false  ],
            [ false, $false , $true],
            [ false, $false , $true, $true ],
            [ false, $true , $true, $false ],
        ];
    }

    public function testFluidInterfaceAndDP()
    {
        $true = new TrueSpecification();
        $false = new FalseSpecification();

        return [
            [ false, $false , $false, ],
            [ false, $false , $false, $false ],
            [ false, $false , $false, $false, $false ],
            [ true, $true, $true],
            [ true, $true, $true, $true],
            [ true, $true , $true, $true ],
            [ false, $true , $true, $false ],
            [ false, $false, $true],
            [ false, $false, $true, $true],
            [ false, $false, $true, $true, $false],
        ];
    }
}