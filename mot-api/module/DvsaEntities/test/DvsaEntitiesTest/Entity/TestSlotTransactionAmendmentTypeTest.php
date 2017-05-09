<?php

namespace DvsaEntityTest\Entity;

use DvsaEntities\Entity\TestSlotTransactionAmendmentType;
use DvsaEntitiesTest\EntityTrait\EntityTestTrait;
use PHPUnit_Framework_TestCase;

/**
 * Class PaymentTest.
 */
class TestSlotTransactionAmendmentTypeTest extends PHPUnit_Framework_TestCase
{
    use EntityTestTrait;

    public function setUp()
    {
        $this->entity = new TestSlotTransactionAmendmentType();
    }

    /**
     * Test data for entity.
     *
     * @return array
     */
    public function dataProvider()
    {
        return
            [
                [
                    [
                        'code' => 123,
                        'title' => 'test type',
                        'isActive' => true,
                        'displayOrder' => 1,
                    ],
                ],
            ];
    }
}
