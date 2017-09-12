<?php

namespace Site\Service;

class CsvFileSizeCalculator
{
    const GROUP_A_TESTER_ROW_SIZE = 120;
    const GROUP_A_BASE_FILE_SIZE = 600;

    const GROUP_B_TESTER_ROW_SIZE = 140;
    const GROUP_B_BASE_FILE_SIZE = 750;

    /**
     * Returns file size for group A CSV in bytes
     * @param int $testersCount
     * @return int
     */
    public static function calculateFileSizeForGroupA(int $testersCount)
    {
        return $testersCount * self::GROUP_A_TESTER_ROW_SIZE + self::GROUP_A_BASE_FILE_SIZE;
    }

    /**
     * Returns file size for group B CSV in bytes
     * @param int $testersCount
     * @return int
     */
    public static function calculateFileSizeForGroupB(int $testersCount)
    {
        return $testersCount * self::GROUP_B_TESTER_ROW_SIZE + self::GROUP_B_BASE_FILE_SIZE;
    }
}