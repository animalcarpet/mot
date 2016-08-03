<?php

namespace DvsaCommonTest\Utility;

use DvsaCommon\Utility\ArrayUtils;
use DvsaCommonTest\TestUtils\SampleTestObject;
use PHPUnit_Framework_TestCase;

/**
 * Class ArrayUtilsTest
 *
 * @package DvsaCommonTest\Utility
 */
class ArrayUtilsTest extends PHPUnit_Framework_TestCase
{
    private $firstExistingElement;
    private $secondExistingElement;
    private $notExistingElement;
    private $haystack;

    private $haystackSimple = [
        'animal1' => 'Dog',
        'animal2' => 'Bird',
        'animal3' => 'Cat',
        'animal4' => 'BigCat',
    ];

    private $sortArray
        = [
            ['name' => 'a', 'age' => 9],
            ['name' => 'c', 'age' => 5],
            ['name' => 'z', 'age' => 2]
        ];

    public function __construct()
    {
        $animal1 = new SampleTestObject(1, 'Dog');
        $animal2 = new SampleTestObject(2, 'Cat');
        $animal3 = new SampleTestObject(3, 'Bird');
        $animal4 = new SampleTestObject(4, 'Cat');
        $animal5 = new SampleTestObject(5, 'Wolf');

        $this->firstExistingElement = $animal2;
        $this->secondExistingElement = $animal4;
        $this->notExistingElement = $animal5;

        $this->haystack = [
            'animal1' => $animal1,
            'animal2' => $animal2,
            'animal3' => $animal3,
            'animal4' => $animal4,
        ];
    }

    public function test_get_keyExists_shouldBeReturnValidValue()
    {
        $value = 1;
        $key = 'test';
        $data = [$key => $value];

        $this->assertEquals($value, ArrayUtils::get($data, $key));
    }

    /**
     * @expectedException \OutOfBoundsException
     */
    public function test_get_keyDoesNotExist_shouldThrowOutOfBoundsException()
    {
        ArrayUtils::get([], 'this key does not exist');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function test_get_wrongDataType_shouldThrowInvalidArgumentException()
    {
        ArrayUtils::get(null, 'there will be an exception - array required');
    }

    public function test_first_or_null_should_find()
    {
        // Given I have a predicate that matches an single element in the haystack
        $searchedId = $this->firstExistingElement->getId();
        $predicate = $this->buildFindByIdPredicate($searchedId);

        // When I search with it through the haystack
        $result = ArrayUtils::firstOrNull($this->haystack, $predicate);

        // Then the element is found
        $this->assertNotNull($result);
        $this->assertEquals($this->firstExistingElement->getId(), $result->getId());
    }

    public function test_first_or_null_should_find_first()
    {
        // Given I have a predicate that matches two elements in the haystack
        $searchedName = $this->firstExistingElement->getName();
        $predicate = $this->buildFindByNamePredicate($searchedName);

        // When I search with it through the haystack
        $result = ArrayUtils::firstOrNull($this->haystack, $predicate);

        // Then the found element is the first one
        $this->assertNotNull($result);
        $this->assertEquals($this->firstExistingElement->getId(), $result->getId());
    }

    public function test_first_or_null_should_return_null_when_none_exists()
    {
        // Given I have a predicate that doesn't match anything in the haystack
        $searchedName = $this->notExistingElement->getName();
        $predicate = $this->buildFindByNamePredicate($searchedName);

        // When I search with it through the haystack
        $result = ArrayUtils::firstOrNull($this->haystack, $predicate);

        // Then nothing is found
        $this->assertNull($result);
    }

    private function buildFindByNamePredicate($name)
    {
        return function (SampleTestObject $animal) use ($name) {
            return $animal->getName() === $name;
        };
    }

    private function buildFindByIdPredicate($id)
    {
        return function (SampleTestObject $animal) use ($id) {
            return $animal->getId() === $id;
        };
    }

    public function testSortBy()
    {
        $result = ArrayUtils::sortBy($this->sortArray, 'age');

        $this->assertEquals(2, $result[0]['age']);
        $this->assertEquals(9, $result[2]['age']);
    }

    public function testSortByDesc()
    {
        $result = ArrayUtils::sortByDesc($this->sortArray, 'name');

        $this->assertEquals('z', $result[0]['name']);
        $this->assertEquals('a', $result[2]['name']);
    }

    public function testAsortByPropertyAsMethod()
    {
        $actual = ArrayUtils::asortBy($this->haystack, 'getName');

        $currActual = current($actual);
        $this->assertEquals('animal3', key($actual));
        $this->assertEquals('Bird', $currActual->getName());
    }

    public function testAsortByWithoutProperty()
    {
        $actual = ArrayUtils::asortBy($this->haystackSimple);

        $currActual = current($actual);
        $this->assertEquals('animal4', key($actual));
        $this->assertEquals('BigCat', $currActual);
    }

    public function test_tryGet_returns_existing_value()
    {
        $input = ['name' => 'John'];
        $result = ArrayUtils::tryGet($input, 'name');

        $this->assertEquals('John', $result);
    }

    public function test_tryGet_returns_null_if_key_does_not_exist()
    {
        $input = ['name' => 'Timmah'];
        $result = ArrayUtils::tryGet($input, 'not_existing_key');

        $this->assertEquals(null, $result);
    }

    public function test_tryGet_returns_passed_parameter_if_key_does_not_exist()
    {
        $input = ['name' => 'Timmah'];
        $result = ArrayUtils::tryGet($input, 'not_existing_key', 'none');

        $this->assertEquals('none', $result);
    }

    public function test_map_returns_transformed_objects()
    {
        $array = [['id' => 1, 'name' => 'Dog'], ['id' => 2, 'name' => 'Cat']];

        $objects = ArrayUtils::map(
            $array,
            function (array $element) {
                return new SampleTestObject($element['id'], $element['name']);
            }
        );

        $this->assertEquals('Dog', $objects[0]->getName());
    }

    public function test_hasNotEmptyValue_returns_true_for_string()
    {
        $array = ['key1' => "abc"];

        $result = ArrayUtils::hasNotEmptyValue($array, 'key1');

        $this->assertTrue($result);
    }

    public function test_hasNotEmptyValue_returns_true_for_number()
    {
        $array = ['numKey' => 123];

        $result = ArrayUtils::hasNotEmptyValue($array, 'numKey');

        $this->assertTrue($result);
    }

    public function test_hasNotEmptyValue_returns_false_for_null()
    {
        $array = ['nullKey' => null];

        $result = ArrayUtils::hasNotEmptyValue($array, 'nullKey');

        $this->assertFalse($result);
    }

    public function test_hasNotEmptyValue_return_false_for_zero()
    {
        $array = ['zeroKey' => 0];

        $result = ArrayUtils::hasNotEmptyValue($array, 'zeroKey');

        $this->assertFalse($result);
    }

    public function test_hasNotEmptyValue_return_false_for_non_existing_key()
    {
        $array = ['someKey' => 'someData'];

        $result = ArrayUtils::hasNotEmptyValue($array, 'otherKey');

        $this->assertFalse($result);
    }

    public function test_filter_leaves_only_elements_that_match_the_predicate()
    {
        $object1 = new SampleTestObject(1, 'Obj1');
        $object2 = new SampleTestObject(2, 'Obj2');
        $object3 = new SampleTestObject(3, 'Obj3');

        $fullArray = [$object1, $object2, $object3];

        $filteredArray = ArrayUtils::filter(
            $fullArray,
            function (SampleTestObject $object) {
                return $object->getId() % 2;
            }
        );

        $this->assertContains($object1, $filteredArray);
        $this->assertContains($object3, $filteredArray);

        $this->assertNotContains($object2, $filteredArray);
    }

    public function test_anyMatch_returns_true_when_element_matching_predicate_exists()
    {
        $object1 = new SampleTestObject(1, 'Obj1');
        $object2 = new SampleTestObject(2, 'Obj2');
        $object3 = new SampleTestObject(3, 'Obj3');

        $array = [$object1, $object2, $object3];

        $result = ArrayUtils::anyMatch(
            $array,
            function (SampleTestObject $object) {
                return $object->getId() % 2 == 0;
            }
        );

        $this->assertTrue($result);
    }

    public function test_anyMatch_returns_false_when_element_matching_predicate_does_not_exist()
    {
        $object1 = new SampleTestObject(1, 'Obj1');
        $object3 = new SampleTestObject(3, 'Obj3');

        $array = [$object1, $object3];

        $result = ArrayUtils::anyMatch(
            $array,
            function (SampleTestObject $object) {
                return $object->getId() % 2 == 0;
            }
        );

        $this->assertFalse($result);
    }

    public function testAddPrefixForKeys()
    {
        // GIVEN I have a prefix I want to append to all keys in array
        $prefix = 'big';

        // WHEN I append it to array
        $array = ['cat' => 1, 'bird' => 2];
        $prefixedArray = ArrayUtils::addPrefixToKeys($array, $prefix);

        // THEN all have the prefix
        $keys = array_keys($prefixedArray);

        $this->assertEquals('bigCat', $keys[0]);
        $this->assertEquals('bigBird', $keys[1]);
    }

    public function test_unsetValue_valueFound()
    {
        $data = [1, 2, 3];
        $dataAfterUnset = ArrayUtils::unsetValue($data, 1);

        $this->assertCount(2, $dataAfterUnset);
        $this->assertTrue(in_array(1, $data));
        $this->assertFalse(in_array(1, $dataAfterUnset));
    }

    public function test_removePrefixFromKeys_()
    {
        $data = [
            'prefix_1' => 1,
            'prefix_2' => 2
        ];

        $dataWithoutPrefixes = ArrayUtils::removePrefixFromKeys($data, 'prefix');

        $this->assertCount(2, $data);
        $this->assertArrayHasKey('prefix_1', $data);
        $this->assertArrayHasKey('prefix_2', $data);

        $this->assertCount(2, $dataWithoutPrefixes);
        $this->assertArrayHasKey('_1', $dataWithoutPrefixes);
        $this->assertArrayHasKey('_2', $dataWithoutPrefixes);
    }

    public function test_moveElementToTop()
    {
        //given
        $source = ['k1' => 'v1', 'k2' => 'v2', 'k3' => 'v3'];
        $expected = ['k2' => 'v2', 'k1' => 'v1', 'k3' => 'v3'];

        //when
        $result = ArrayUtils::moveElementToTop($source, 'k2');

        //then
        $this->assertSame($expected, $result);
    }

    public function test_moveElementToTop_shouldCreateNewKey()
    {
        //given
        $source = ['k1' => 'v1', 'k2' => 'v2', 'k3' => 'v3'];
        $expected = ['k5' => null, 'k1' => 'v1', 'k2' => 'v2', 'k3' => 'v3'];

        //when
        $result = ArrayUtils::moveElementToTop($source, 'k5');

        //then
        $this->assertSame($expected, $result);
    }

    public function test_groupBy()
    {
        //given
        $source = [1, 2 ,3 ,4];
        $expected = [1 => [1, 3], 0 => [2, 4]];

        //when
        $result = ArrayUtils::groupBy($source, function($x) {return $x % 2;});

        //then
        $this->assertSame($expected, $result);
    }

    public function test_mapWithKeys_returnsTransformedCollection()
    {
        $array = ['class1' => "QFD", 'class2' => "QFD", 'class6' => "QFD"];

        $collection = ArrayUtils::mapWithKeys($array,
            function ($key, $value) { return substr($key, 5); },
            function ($key, $value) { return $value; }
        );

        $this->assertEquals("QFD", $collection[1]);
        $this->assertEquals("QFD", $collection[2]);
        $this->assertEquals("QFD", $collection[6]);
    }

    public function test_getKeys_returnsAllTheKeys()
    {
        $array = ['val', 'key2' => 'val', 'key3' => ['val', 'key4' => 'val'] ];
        $keys = ArrayUtils::getKeys($array);

        $this->assertEquals(3, count($keys));
        $this->assertEquals(0, $keys[0]);
        $this->assertEquals('key2', $keys[1]);
        $this->assertEquals('key3', $keys[2]);
    }

    public function test_aggregate()
    {
        $arrayWeWantToSum = [10, 12, 13];
        $expectedSum = 35;

        $actual = ArrayUtils::aggregate($arrayWeWantToSum, 0, function ($current, $total) {
            return $current + $total;
        });

        $this->assertEquals($expectedSum, $actual);

    }
}
