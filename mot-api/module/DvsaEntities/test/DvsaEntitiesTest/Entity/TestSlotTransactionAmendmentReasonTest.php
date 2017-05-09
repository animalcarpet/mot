<?php

namespace DvsaEntityTest\Entity;

use DvsaEntities\Entity\TestSlotTransactionAmendmentReason;
use DvsaEntitiesTest\EntityTrait\EntityTestTrait;
use PHPUnit_Framework_TestCase;

/**
 * Class PaymentTest.
 */
class TestSlotTransactionAmendmentReasonTest extends PHPUnit_Framework_TestCase
{
    use EntityTestTrait;

    public function setUp()
    {
        $this->entity = new TestSlotTransactionAmendmentReason();
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
                        'description' => 'failed payment',
                        'displayOrder' => 1,
                    ],
                ],
            ];
    }
}
