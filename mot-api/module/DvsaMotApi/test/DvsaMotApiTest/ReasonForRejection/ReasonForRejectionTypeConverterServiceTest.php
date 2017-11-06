<?php
namespace DvsaMotApiTest\Service\ReasonForRejection;

use DvsaMotApi\Service\ReasonForRejection\ReasonForRejectionTypeConverterService;

class ReasonForRejectionTypeConverterServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \OutOfBoundsException
     */
    public function testConvert_throwsException_whenTryConvertInvalidArray()
    {
        $sut = new ReasonForRejectionTypeConverterService();
        $sut->convert(["invalid_array"]);
    }

    public function testConvert_ReturnsArrayWithConvertedTypes()
    {
        $sut = new ReasonForRejectionTypeConverterService();

        $arrayWithTrueValues = [
            "isAdvisory" => "1",
            "isPrsFail" => "1",
            "vehicleClasses" => "1",
            "description" => "description",
            "advisoryText" => "advisory",
            "categoryName" => "category",
            "categoryDescription" => "category description",
        ];

        $testArray = $sut->convert($arrayWithTrueValues);

        $this->assertTrue($testArray["isAdvisory"]);

        $arrayWithFalseValues = [
            "isAdvisory" => "0",
            "isPrsFail" => "0",
            "vehicleClasses" => "0",
            "description" => "description",
            "advisoryText" => "advisory",
            "categoryName" => "category",
            "categoryDescription" => "category description",
        ];

        $testArray = $sut->convert($arrayWithFalseValues);

        $this->assertFalse($testArray["isAdvisory"]);
    }
}
