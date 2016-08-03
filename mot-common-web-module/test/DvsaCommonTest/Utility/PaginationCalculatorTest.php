<?php

namespace DvsaCommonTest\Utility;

use DvsaCommon\Utility\PaginationCalculator;

class PaginationCalculatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider dataProviderOffsetExists
     */
    public function testOffsetExist($offset, $totalItemCount, $expectedOutcome)
    {
        $this->assertEquals($expectedOutcome, PaginationCalculator::offsetExists($offset, $totalItemCount));
    }

    public function dataProviderOffsetExists()
    {
        return [
            // correct offset, no data
            [0, 0, false],
            // offset is correct less than total count
            [9, 10, true],
            [0, 1, true],
            [10, 11, true],
            [1, 15, true],
            // negative offset
            [-1, 0, false],
            [-1, 1, false],
            // offset is greater than total count
            [1, 0, false],
            [1, 1, false],
            [10, 9, false],
            [11, 10, false],
            // offset equals item count,
            [10, 10, false],
            [30, 30, false],
        ];
    }

    /**
     * @dataProvider dataProviderItemOffsetFromPageNumber
     */
    public function testCalculateItemOffsetFromPageNumber($pageNumber, $itemsPerPage, $expectedOffset)
    {
        $this->assertEquals(
            $expectedOffset, PaginationCalculator::calculateItemOffsetFromPageNumber($pageNumber, $itemsPerPage)
        );
    }

    public function dataProviderItemOffsetFromPageNumber()
    {
        return [
            // negative page number
            [-1, 10, 0],
            // page 0, expect offset to be 0
            [0, 1, 0],
            [0, 2, 0],
            [0, 10, 0],
            [1, 10, 0],
            [2, 10, 10],
            [3, 10, 20],
            [1, 5, 0],
            [2, 5, 5],
            [3, 5, 10],
        ];
    }
}