<?php

namespace DvsaCommonTest\Specification;


use DvsaCommon\Specification\AbstractSpecification;
use DvsaCommon\Specification\OrSpecification;
use DvsaCommon\Specification\SpecificationInterface;
use DvsaCommonTest\Specification\Stubs\FalseSpecification;
use DvsaCommonTest\Specification\Stubs\TrueSpecification;

class OrSpecificationTest extends \PHPUnit_Framework_TestCase
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

    public function testOrSpecificationInstanceIsReturned()
    {
        $result = $this->trueSpec->orSpecification($this->falseSpec);
        $this->assertInstanceOf(OrSpecification::class, $result);
    }

    /**
     * @dataProvider testLogicalOrOperationDP
     * @param $expectedResult
     * @param \DvsaCommon\Specification\SpecificationInterface[] $specifications
     */
    public function testLogicalAndOperation($expectedResult, SpecificationInterface ...$specifications)
    {
        $andSpec = new OrSpecification(...$specifications);
        $result = $andSpec->isSatisfiedBy(null);
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @dataProvider testFluidInterfaceOrDP
     * @param $expectedResult
     * @param AbstractSpecification $originalSpec
     * @param \DvsaCommon\Specification\SpecificationInterface[] ...$specifications
     */
    public function testFluidInterfaceOr($expectedResult, AbstractSpecification $originalSpec, SpecificationInterface ...$specifications)
    {
        $compositeSpec = $originalSpec->orSpecification(...$specifications);
        $result = $compositeSpec->isSatisfiedBy(null);
        $this->assertEquals($expectedResult, $result);
    }

    public function testLogicalOrOperationDP()
    {
        $true = new TrueSpecification();
        $false = new FalseSpecification();

        return [
            [ false, $false , $false ],
            [ true, $true , $true ],
            [ true, $true , $true, $true ],
            [ true, $true , $false ],
            [ true, $false , $true ],
            [ true, $false , $true, $true ],
            [ true, $true , $true, $false ],
        ];
    }

    public function testFluidInterfaceOrDP()
    {
        $true = new TrueSpecification();
        $false = new FalseSpecification();

        return [
            [ false, $false , $false ],
            [ false, $false , $false, $false ],
            [ false, $false , $false, $false, $false ],
            [ true, $true, $true],
            [ true, $true, $true, $true],
            [ true, $true , $false, $false ],
            [ true, $false, $true],
            [ true, $false, $true, $true],
            [ true, $false, $true, $true, $false],
        ];
    }
}