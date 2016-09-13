<?php

namespace DvsaCommonTest\Dto\MotTesting;

use DvsaCommon\Dto\MotTesting\MotTestOptionsDto;

class MotTestOptionsDtoTest extends \PHPUnit_Framework_TestCase
{
    public function testFromArrayToArray()
    {
        $expected = [
            'startedDate' => 'not relevant',
            'vehicle'     => [
                'id'                        => 1,
                'make'                      => 'not relevant',
                'model'                     => 'not relevant',
                'vehicleRegistrationNumber' => 'not relevant',
            ],
            'motTestType' => [
                'id' => 'A',
                'code' => 'TEST'
            ]
        ];

        $dto = MotTestOptionsDto::fromArray($expected);

        $this->assertEquals($expected, $dto->toArray());
    }
}
