<?php

namespace DvsaCommonTest\DtoSerialization;

use DvsaCommon\Date\Time;
use DvsaCommon\DtoSerialization\DtoConvertibleTypesRegistry;
use DvsaCommon\DtoSerialization\DtoReflectiveDeserializer;
use DvsaCommon\DtoSerialization\DtoReflectiveSerializer;
use DvsaCommon\DtoSerialization\DtoReflector;
use DvsaCommonTest\DtoSerialization\TestDto\SampleDto;
use DvsaCommonTest\DtoSerialization\TestDto\SampleDtoWithConvertible;
use DvsaCommonTest\DtoSerialization\TestDto\SampleDtoWithDifferentGetters;
use DvsaCommonTest\DtoSerialization\TestDto\SampleDtoWithNestedValues;
use PHPUnit_Framework_TestCase;

class DtoReflectiveSerializationTest extends PHPUnit_Framework_TestCase
{
    /** @var DtoReflectiveSerializer */
    private $serializer;

    /** @var DtoReflectiveDeserializer */
    private $deserializer;

    public function setUp()
    {
        $this->serializer = new DtoReflectiveSerializer();
        $this->deserializer = new DtoReflectiveDeserializer();
    }

    public function testSerializationOfDtoWithScalarValuesWorks()
    {
        $sampleName = 'kitty';

        $sourceDto = new SampleDto();
        $sourceDto->setName($sampleName);

        /** @var SampleDto $serializedDto */
        $serializedDto = $this->serializeAndDeserialize($sourceDto, SampleDto::class);

        $this->assertEquals($sampleName, $serializedDto->getName());
    }

    public function testSerializationOfDtoWithNestedScalarArrayWorks()
    {
        $sampleArray = ['dog', 'cat'];

        $sourceDto = new SampleDto();
        $sourceDto->setNames($sampleArray);

        /** @var SampleDto $serializedDto */
        $serializedDto = $this->serializeAndDeserialize($sourceDto, SampleDto::class);

        $this->assertEquals($sampleArray, $serializedDto->getNames());
    }

    public function testSerializationSupportsNullAsArray()
    {
        $sourceDto = new SampleDto();

        /** @var SampleDto $serializedDto */
        $serializedDto = $this->serializeAndDeserialize($sourceDto, SampleDto::class);

        $this->assertNull($serializedDto->getNames());
    }

    public function testSerializationOfNestedDtos()
    {
        $nestedName = 'doggy';
        $nestedObject = new SampleDto();
        $nestedObject->setName($nestedName);

        $masterObject = new SampleDtoWithNestedValues();
        $masterObject->setNestedDto($nestedObject);

        /** @var $serializedDto SampleDtoWithNestedValues */
        $serializedDto = $this->serializeAndDeserialize($masterObject, SampleDtoWithNestedValues::class);

        $this->assertEquals($nestedName, $serializedDto->getNestedDto()->getName());
    }

    public function testSerializationOfNestedArrayOfDtos()
    {
        $nestedName1 = 'doggy';
        $nestedObject1 = new SampleDto();
        $nestedObject1->setName($nestedName1);

        $nestedName2 = 'kitty';
        $nestedObject2 = new SampleDto();
        $nestedObject2->setName($nestedName2);

        $masterObject = new SampleDtoWithNestedValues();
        $masterObject->setNestedDtoList([$nestedObject1, $nestedObject2]);

        /** @var $serializedDto SampleDtoWithNestedValues */
        $serializedDto = $this->serializeAndDeserialize($masterObject, SampleDtoWithNestedValues::class);

        $this->assertEquals($nestedName1, $serializedDto->getNestedDtoList()[0]->getName());
        $this->assertEquals($nestedName2, $serializedDto->getNestedDtoList()[1]->getName());
    }

    public function testSerializationOfListOfDtos()
    {
        $dto1 = new SampleDto();
        $dto1->setName('kitty');

        $dto2 = new SampleDto();
        $dto2->setName('doggy');

        $dtoList = [$dto1, $dto2];

        $serializedDtoList = $this->serializeAndDeserializeArray($dtoList, SampleDto::class);

        $this->assertEquals($dtoList, $serializedDtoList);
    }

    public function testSerializerSupportsMethodsStartingWithIs()
    {
        $dto = new SampleDtoWithDifferentGetters();

        $value = "Should be bool, but this is PHP so who cares";
        $dto->setActive($value);

        /** @var SampleDtoWithDifferentGetters $deserialized */
        $deserialized = $this->serializeAndDeserialize($dto, SampleDtoWithDifferentGetters::class);

        $this->assertEquals($value, $deserialized->isActive());
    }

    public function testSupportsNestedConvertibleTypes()
    {
        $dto = new SampleDtoWithConvertible();

        $date = new \DateTime('now');

        $dto->setDate($date);

        /** @var SampleDtoWithConvertible $deserialized */
        $deserialized = $this->serializeAndDeserialize($dto, SampleDtoWithConvertible::class);

        $this->assertEquals($date, $deserialized->getDate());
    }

    public function testSupportsNestedArraysOfConvertibleTypes()
    {
        $dto = new SampleDtoWithConvertible();

        $time1 = new Time(10, 40, 24);
        $time2 = new Time(22, 19, 49);

        $dto->setTimes([$time1, $time2]);

        /** @var SampleDtoWithConvertible $deserialized */
        $deserialized = $this->serializeAndDeserialize($dto, SampleDtoWithConvertible::class);

        $this->assertTrue($deserialized->getTimes()[0]->equals($time1));
        $this->assertTrue($deserialized->getTimes()[1]->equals($time2));
    }

    public function testCanSerializeDtoPropertiesWithNullValues()
    {
        $dto = new SampleDtoWithNestedValues();

        $dto->setNestedDto(null);
        $dto->setNestedDtoList(null);

        /** @var SampleDtoWithNestedValues $deserialized */
        $deserialized = $this->serializeAndDeserialize($dto, SampleDtoWithNestedValues::class);

        $this->assertNull($deserialized->getNestedDto());
        $this->assertNull($deserialized->getNestedDtoList());
    }

    public function testCanSetNullAsNestedConvertible()
    {
        $dto = new SampleDtoWithConvertible();

        $dto->setDate(null);

        /** @var SampleDtoWithConvertible $deserialized */
        $deserialized = $this->serializeAndDeserialize($dto, SampleDtoWithConvertible::class);

        $this->assertNull($deserialized->getDate());
    }

    public function testCanSetNullAsNestedArrayOfConvertibles()
    {
        $dto = new SampleDtoWithConvertible();

        $dto->setTimes(null);

        /** @var SampleDtoWithConvertible $deserialized */
        $deserialized = $this->serializeAndDeserialize($dto, SampleDtoWithConvertible::class);

        $this->assertNull($deserialized->getTimes());
    }

    public function testCanSerializeEmptyArrayOfDtos()
    {
        $deserialized = $this->serializeAndDeserializeArray([], SampleDtoWithConvertible::class);

        $this->assertTrue(is_array($deserialized));
        $this->assertEmpty($deserialized);
    }

    public function testCanSerializeConvertible()
    {
        $time = new Time(10, 10, 2);

        /** @var Time $deserialized */
        $deserialized = $this->serializeAndDeserialize($time, Time::class);

        $this->assertInstanceOf(Time::class, $deserialized);
        $this->assertTrue($time->equals($deserialized));
    }

    public function testCanSerializeArrayOfConvertibles()
    {
        $time1 = new Time(10, 10, 2);
        $time2 = new Time(10, 10, 2);

        /** @var Time[] $deserialized */
        $deserialized = $this->serializeAndDeserializeArray([$time1, $time2], Time::class);

        $this->assertTrue($time1->equals($deserialized[0]));
        $this->assertTrue($time2->equals($deserialized[1]));
    }

    public function testCanSerializeEmptyArrayOfConvertibles()
    {
        $deserialized = $this->serializeAndDeserializeArray([], Time::class);

        $this->assertTrue(is_array($deserialized));
        $this->assertEmpty($deserialized);
    }

    private function serializeAndDeserialize($dto, $dtoType)
    {
        return $this->deserializer->deserialize($this->serializer->serialize($dto), $dtoType);
    }

    private function serializeAndDeserializeArray($dto, $dtoType)
    {
        return $this->deserializer->deserializeArray($this->serializer->serialize($dto), $dtoType);
    }
}
