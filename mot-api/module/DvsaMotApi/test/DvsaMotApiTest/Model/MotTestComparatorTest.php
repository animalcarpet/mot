<?php

namespace DvsaMotApiTest\Model;

use DvsaMotApi\Model\MotTestComparator;
use DvsaCommonApiTest\Service\AbstractServiceTestCase;

/**
 * Class MotTestComparatorTest.
 */
class MotTestComparatorTest extends AbstractServiceTestCase
{
    protected $comparator;
    public function setUp()
    {
        $this->comparator = new MotTestComparator();
    }

    /**
     * Make sure that different rfrIds fail an equality check.
     */
    public function testRfrEqualityCheck()
    {
        $rfrA = $this->makeDefaultRfr();

        foreach (array_keys($rfrA) as $key) {
            $rfrB = $this->makeDefaultRfr();
            $rfrB[$key] = 100;
            $this->assertFalse($this->comparator->rfrIsEqual($rfrA, $rfrB, "Testing {$key}"));
        }
    }

    /**
     * test that comparing an array of the same RFRs, returns an array but with no differences.
     */
    public function testCompareRfrArrayWithSameArray()
    {
        $rfrA = $this->makeDefaultRfr();
        $rfrs = [$rfrA, $rfrA, $rfrA];
        $result = $this->comparator->compareRfrArray($rfrs, $rfrs);
        $this->assertInternalType('array', $result);
        $this->assertCount(0, $result);
    }

    /**
     * Test comparing the array items between the arrays but without testing the actual
     * value differences between the items in the array..
     * That would be too complex for a single test :).
     */
    public function testCompareRfrArrayItems()
    {
        $rfrA = $this->makeDefaultRfr(1);

        $fixtures = [];
        $fixtures[] = ['a' => [], 'b' => [], 'count' => 0];
        $fixtures[] = ['a' => [$rfrA], 'b' => [$rfrA], 'count' => 0];
        $fixtures[] = ['a' => [$rfrA], 'b' => [], 'count' => 1];
        $fixtures[] = ['a' => [], 'b' => [$rfrA], 'count' => 1];
        $fixtures[] = ['a' => [$rfrA, $rfrA], 'b' => [], 'count' => 2];
        $fixtures[] = ['a' => [$rfrA, $rfrA], 'b' => [$rfrA], 'count' => 0];
        $fixtures[] = ['a' => [$rfrA, $rfrA], 'b' => [$rfrA, $rfrA], 'count' => 0];

        foreach ($fixtures as $fixture) {
            $result = $this->comparator->compareRfrArray($fixture['a'], $fixture['b']);
            $this->assertCount($fixture['count'], $result);
        }
    }

    /**
     * test comparing the same Rfr items but with different values within each array.
     */
    public function testCompareRfrArrayItemsWithDifferences()
    {
        $rfr1A = $this->makeDefaultRfr(1);
        $rfr1B = $this->makeDefaultRfr(1);

        $rfr1A['comment'] = 'this is A';
        $rfr1B['comment'] = 'this is B';

        $fixtures = [];

        $fixtures[] = ['a' => [$rfr1A], 'b' => [$rfr1B], 'count' => 2];
        $fixtures[] = ['a' => [], 'b' => [$rfr1B], 'count' => 1];
        $fixtures[] = ['a' => [$rfr1A], 'b' => [], 'count' => 1];

        foreach ($fixtures as $fixture) {
            $result = $this->comparator->compareRfrArray($fixture['a'], $fixture['b']);
            $this->assertCount($fixture['count'], $result);
        }
    }

    /**
     * Test grouping the items passing in..
     */
    public function testGetMotTestRfrGroupedByManualReference()
    {
        $fixtures = [];
        $fixtures[] = ['rfrId' => 1, 'inspectionManualReference' => 'this'];
        $fixtures[] = ['rfrId' => 2, 'inspectionManualReference' => 'one'];
        $fixtures[] = ['rfrId' => 3, 'inspectionManualReference' => 'is'];
        $fixtures[] = ['rfrId' => 4, 'inspectionManualReference' => 'this'];

        $result = $this->comparator->getMotTestRfrGroupedByManualReference($fixtures);

        $this->assertCount(2, $result['this']);
        $this->assertCount(1, $result['one']);
        $this->assertCount(1, $result['is']);

        $this->assertEquals(1, $result['this'][0]['rfrId']);
        $this->assertEquals(4, $result['this'][1]['rfrId']);
        $this->assertEquals(2, $result['one'][0]['rfrId']);
        $this->assertEquals(3, $result['is'][0]['rfrId']);
    }

    /**
     * Create an Rfr array with all fields set to 0.
     *
     * @param int $rfrId
     *
     * @return array
     */
    protected function makeDefaultRfr($rfrId = 1)
    {
        return [
            'rfrId' => $rfrId,
            'type' => 0,
            'locationLateral' => 0,
            'locationLongitudinal' => 0,
            'locationVertical' => 0,
            'comment' => 0,
            'failureDangerous' => 0,
        ];
    }
}
