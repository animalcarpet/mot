<?php

namespace DvsaCommon\Utility;

class PaginationCalculator
{
    /**
     * Checks if given offset is in range of accessible pages
     * @param int $offset
     * @param int $totalItemCount
     * @return bool
     */
    public static function offsetExists($offset, $totalItemCount)
    {
        return (($offset >= 0) && ($offset < $totalItemCount));
    }

    /**
     * Returns MySQL offset of items for given page
     * @param int $pageNumber
     * @param int $limit
     * @return int
     */
    public static function calculateItemOffsetFromPageNumber($pageNumber, $limit)
    {
        TypeCheck::assertIsPositiveInteger($limit);

        if ($pageNumber <= 0) {
            return 0;
        }
        return ($pageNumber - 1) * $limit;
    }
}