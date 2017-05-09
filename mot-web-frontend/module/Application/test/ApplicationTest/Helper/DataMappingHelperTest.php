<?php

namespace ApplicationTest\Helper;

use Application\Helper\DataMappingHelper;
use PHPUnit_Framework_TestCase;

class DataMappingHelperTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var array
     */
    private $testData = [];

    public function setUp()
    {
        $this->testData = [
            ['id' => 1, 'firstKey' => 'firstValue1', 'secondKey' => 'secondValue1'],
            ['id' => 2, 'firstKey' => 'firstValue2', 'secondKey' => 'secondValue2'],
            ['id' => 3, 'firstKey' => 'firstValue3', 'secondKey' => 'secondValue3'],
            ['id' => 4, 'firstKey' => 'firstValue4', 'secondKey' => 'secondValue4'],
        ];
    }

    public function idDataProvider()
    {
        return [
            [1],
            [2],
            [3],
            [4],
        ];
    }

    /**
     * @dataProvider idDataProvider
     */
    public function testGetValue_ArrayFromStringKey($id)
    {
        $obj = new DataMappingHelper($this->testData, 'firstKey', "firstValue{$id}");
        $actual = $obj->setReturnKeys([
            'id',
            'secondKey',
        ])->getValue();
        $expected = ['id' => $id, 'secondKey' => "secondValue{$id}"];
        $this->assertSame($expected, $actual);
    }

    /**
     * @dataProvider idDataProvider
     */
    public function testGetValue_AllKeysFromIdKey($id)
    {
        $obj = new DataMappingHelper($this->testData, 'id', $id);
        $actual = $obj->getValue();
        $expected = ['id' => $id, 'firstKey' => "firstValue{$id}", 'secondKey' => "secondValue{$id}"];
        $this->assertSame($expected, $actual);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Unable to find what you were looking for
     */
    public function testGetValue_NotFoundException()
    {
        $obj = new DataMappingHelper($this->testData, 'id', 5);
        $obj->getValue();
    }

    /**
     * @expectedException \BadMethodCallException
     * @expectedExceptionMessage Input data must be an array of arrays
     */
    public function testGetKeyValue_NotNestedArrayException()
    {
        $inputData = ['badData'];

        $this->setExpectedException('BadMethodCallException');
        $obj = new DataMappingHelper($inputData, 'id', 5);
        $obj->getValue();
    }

    /**
     * @expectedException \BadMethodCallException
     * @expectedExceptionMessage NotExist Key is missing
     */
    public function testGetKeyValue_MissingKeyException()
    {
        $this->setExpectedException('BadMethodCallException');
        $obj = new DataMappingHelper($this->testData, 'NotExist', 1);
        $obj->getValue();
    }
}
