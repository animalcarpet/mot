<?php

namespace DvsaCommonTest\DtoSerialization;

use DvsaCommon\DtoSerialization\DtoConvertibleTypesRegistry;
use DvsaCommon\DtoSerialization\DtoReflectiveDeserializer;
use DvsaCommon\DtoSerialization\DtoReflector;
use DvsaCommon\Guid\Guid;
use DvsaCommonTest\DtoSerialization\TestDto\SampleDto;
use DvsaCommonTest\DtoSerialization\TestDto\SampleDtoWithNestedValues;
use PHPUnit_Framework_TestCase;

class DtoReflectiveDeserializerTest extends PHPUnit_Framework_TestCase
{
    /** @var DtoReflectiveDeserializer */
    private $deserializer;

    public function setUp()
    {
        $this->deserializer = new DtoReflectiveDeserializer();
    }

    /**
     * @expectedException \DvsaCommon\DtoSerialization\DtoDeserializationException
     * @expectedExceptionCode 1
     */
    public function testMissingJsonPropertyThrowsException()
    {
        $this->deserializer->deserialize(["name2" => "a value"], SampleDto::class);
    }

    /**
     * @expectedException \DvsaCommon\DtoSerialization\DtoDeserializationException
     * @expectedExceptionCode 2
     */
    public function testsDeserializingThrowExceptionWhenEncouterdScalarValueInsteadOfAnDtoArray()
    {
        $dto1json = ["name" => "kitty", "names" => null];
        $dto2json = "im a string"; //this should be an array

        $json = ['nestedDtoList' => [$dto1json, $dto2json], 'nestedDto' => null];

        $this->deserializer->deserialize($json, SampleDtoWithNestedValues::class);
    }

    /**
     * @expectedException \DvsaCommon\DtoSerialization\DtoDeserializationException
     * @expectedExceptionCode 3
     */
    public function testsDeserializingThrowExceptionWhenEncounteredNullValueInDtoArray()
    {
        $dto1json = ["name" => "kitty", "names" => null];
        $dto2json = null; //this should be an array

        $json = ['nestedDtoList' => [$dto1json, $dto2json], 'nestedDto' => null];

        $this->deserializer->deserialize($json, SampleDtoWithNestedValues::class);
    }

    /**
     * @expectedException \DvsaCommon\DtoSerialization\DtoDeserializationException
     * @expectedExceptionCode 4
     */
    public function testsDeserializingThrowExceptionWhenEncounteredObjectInDtoArray()
    {
        $dto1json = ["name" => "kitty", "names" => null];
        $dto2json = new SampleDto(); //this should be an array, not an object

        $json = ['nestedDtoList' => [$dto1json, $dto2json], 'nestedDto' => null];

        $this->deserializer->deserialize($json, SampleDtoWithNestedValues::class);
    }

    /**
     * @expectedException \DvsaCommon\DtoSerialization\DtoDeserializationException
     * @expectedExceptionCode 5
     */
    public function testsExceptionWhenEncounteredScalarInsteadOfAnArrayOfDtoProperties()
    {
        $nestedDtoArray = "hello"; // this should be an array of DTO properties

        $json = ['nestedDtoList' => [], 'nestedDto' => $nestedDtoArray];

        $this->deserializer->deserialize($json, SampleDtoWithNestedValues::class);
    }

    public function testXXX(){
        //todo null in array of convertibles
        //todo object in array of convertibles
    }

    public function testssAdD()
    {
        // todo nested property was supposed to be a list of DTO scalar/convertable encountered
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testCannotDeserializeToNonDtos()
    {
        $this->deserializer->deserialize([], Guid::class);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testCannotDeserializeToArrayOfNonDtos()
    {
        $this->deserializer->deserializeArray([], Guid::class);
    }
}
