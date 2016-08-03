<?php

namespace DvsaCommonTest\DtoSerialization;

use DvsaCommon\DtoSerialization\DtoConvertibleTypesRegistry;
use DvsaCommon\DtoSerialization\DtoReflectiveSerializer;
use DvsaCommon\DtoSerialization\DtoReflector;
use DvsaCommon\Guid\Guid;
use DvsaCommonTest\DtoSerialization\TestDto\SampleDto;
use DvsaCommonTest\DtoSerialization\TestDto\SampleDtoWithNestedValues;
use DvsaCommonTest\DtoSerialization\TestDto\TestValidDto;
use PHPUnit_Framework_TestCase;

class DtoReflectiveSerializerTest extends PHPUnit_Framework_TestCase
{
    /** @var DtoReflectiveSerializer */
    private $serializer;

    public function setUp()
    {
        $this->serializer = new DtoReflectiveSerializer();
    }

    public function testSerializerOmitsGettersWithoutRespondingSetters()
    {
        $dto = new TestValidDto();

        $dto->propertyWithoutASetter = "value does not matter";

        $serialized = $this->serializer->serialize($dto);
        $propertyKeys = array_keys($serialized);

        $this->assertContains('getPropertyWithoutASetter', get_class_methods(TestValidDto::class));
        $this->assertNotContains('propertyWithoutASetter', $propertyKeys);
    }

    /**
     * @expectedException \DvsaCommon\DtoSerialization\DtoSerializationException
     * @expectedExceptionCode 1
     */
    public function testSerializerCannotSerializeNonDtoObjects()
    {
        $simpleObject = new Guid(); // random class, if you want just swap this to anything that's not DTO

        $this->serializer->serialize($simpleObject);
    }

    public function testSerializerCannotSerializeNestedNonDtoObjects()
    {
        //todo
    }

    /**
     * @expectedException \DvsaCommon\DtoSerialization\DtoSerializationException
     * @expectedExceptionCode 2
     */
    public function testAllElementsInDtoArrayAreSameClass()
    {
        $dto1 = new SampleDto();
        $dto2 = new SampleDtoWithNestedValues();

        $this->serializer->serialize([$dto1, $dto2]);
    }

    /**
     * @throws \DvsaCommon\DtoSerialization\DtoSerializationException
     * @expectedException \DvsaCommon\DtoSerialization\DtoSerializationException
     * @expectedExceptionCode 3
     */
    public function testCannotSerializeScalarValues()
    {
        $this->serializer->serialize("string");
    }

    /**
     * @throws \DvsaCommon\DtoSerialization\DtoSerializationException
     * @expectedException \DvsaCommon\DtoSerialization\DtoSerializationException
     * @expectedExceptionCode 5
     */
    public function testCannotSerializeNulls()
    {
        $this->serializer->serialize(null);
    }

    public function testSerializesNestedConvertibles()
    {
        $dto = new TestValidDto();

        $isoDate = '2010-10-20 11:10:12';

        $date = new \DateTime($isoDate);

        $dto->setNestedConvertibleWithConstraint($date);

        $serialized = $this->serializer->serialize($dto);

        $this->assertArrayHasKey('nestedConvertibleWithConstraint', $serialized);
        $this->assertEquals($isoDate, $serialized['nestedConvertibleWithConstraint']);
    }

    /**
     * @throws \DvsaCommon\DtoSerialization\DtoSerializationException
     * @expectedException \DvsaCommon\DtoSerialization\DtoSerializationException
     * @expectedExceptionCode 8
     */
    public function testCannotSerializeScalarArrayValues()
    {
        $this->serializer->serialize(['a scalar value', 'another scalar value']);
    }

    /**
     * @throws \DvsaCommon\DtoSerialization\DtoSerializationException
     * @expectedException \DvsaCommon\DtoSerialization\DtoSerializationException
     * @expectedExceptionCode 9
     */
    public function testCannotSerializeArrayWithNull()
    {
        $this->serializer->serialize([new SampleDto(), null]);
    }

    // ##############################
    // type mismatch
    //
    public function testsss()
    {
        // SERIALIZER
        // todo throws exception when different type is encounterred
        // expected dto vs scalar
        // expected scalar vs dto
        // expected scalar vs convertable
        // etc.
    }

    public function testsss3()
    {
        // SERIALIZER
        // todo throws exception when different type is encounterred in nested
        // expected dto vs scalar
        // expected scalar vs dto
        // expected scalar vs convertable

        // and nulls
        // etc.
    }
    //
    // end of type mismatch
    //#################

    /**
     * @throws \DvsaCommon\DtoSerialization\DtoSerializationException
     * @expectedException \DvsaCommon\DtoSerialization\DtoSerializationException
     * @expectedExceptionCode 4
     */
    public function testExceptionWhenArrayOfNestedDtosHasNull()
    {
        $dto1 = new SampleDto();
        $dto3 = new SampleDtoWithNestedValues();

        $dto3->setNestedDtoList([$dto1, null]); // sample dto doesn't have TestValidDto nested, so this should break

        $this->serializer->serialize($dto3);
    }

    /**
     * @throws \DvsaCommon\DtoSerialization\DtoSerializationException
     * @expectedException \DvsaCommon\DtoSerialization\DtoSerializationException
     * @expectedExceptionCode 6
     */
    public function testExceptionWhenArrayOfNestedDtosHasDifferentType()
    {
        $dto1 = new SampleDto();            // this is okay
        $dto2 = new TestValidDto();         // this will break the test
        $dto3 = new SampleDtoWithNestedValues();

        $dto3->setNestedDtoList([$dto1, $dto2]); // sample dto doesn't have TestValidDto nested, so this should break

        $this->serializer->serialize($dto3);
    }

    /**
     * @throws \DvsaCommon\DtoSerialization\DtoSerializationException
     * @expectedException \DvsaCommon\DtoSerialization\DtoSerializationException
     * @expectedExceptionCode 7
     */
    public function testExceptionWhenArrayOfNestedDtosHasDifferentScalar()
    {
        $dto1 = new SampleDto();            // this is okay
        $dto2 = "a scalar value";         // this will break the test, it should be a DTO
        $mainDto = new SampleDtoWithNestedValues();

        $mainDto->setNestedDtoList([$dto1, $dto2]); // sample dto doesn't have TestValidDto nested, so this should break

        $this->serializer->serialize($mainDto);
    }

    public function testExceptionWhenArrayOfNestedScalarsHasDifferentTypes()
    {
        //todo same for null and object
        $dto1 = new SampleDto();
        $dto2 = new SampleDtoWithNestedValues();
    }

    public function testsDeSerializingThrowExceptionWhenEncounteredConvertibleValueInsteadOfAnDtoArray()
    {
        // todo do
//        $dto1 = new SampleDto();            // this is okay
//        $dto2 = "a scalar value";         // this will break the test, it should be a DTO
//        $mainDto = new SampleDtoWithNestedValues();
//
//        $mainDto->setNestedDtoList([$dto1, $dto2]); // sample dto doesn't have TestValidDto nested, so this should break
//
//        $this->serializer->serialize($mainDto);
    }

    public function testbla2ses()
    {
        //todo doesn;t serialize properties without setters
    }

    public function testDeserializingArrayOfArraysThrowException()
    {
        //todo throws exception when array contains array


    }

    public function testNestedxxxx()
    {
        //todo throws exception when nested array contains array
    }

    public function testAAxx()
    {
        //todo cannot serilize converatlbes on it's own (or in an array)
    }

    public function testAAxx2()
    {
        //todo cannot serilize null
    }

    public function testaaaa()
    {
        //todo doesn't have _class property
    }
}
