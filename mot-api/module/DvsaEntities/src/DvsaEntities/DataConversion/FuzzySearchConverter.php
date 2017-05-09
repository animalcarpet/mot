<?php

namespace DvsaEntities\DataConversion;

class FuzzySearchConverter extends AbstractStringConverter
{
    protected $charMapping = [
        'O' => '0',
        'I' => '1',
        'Z' => '2',
        'E' => '3',
        'A' => '4',
        'S' => '5',
        'G' => '6',
        'T' => '7',
        'L' => '7',
        'B' => '8',
        '-' => '',
        '/' => '',
        '.' => '',
        '*' => '',
        ' ' => '',
    ];
}
