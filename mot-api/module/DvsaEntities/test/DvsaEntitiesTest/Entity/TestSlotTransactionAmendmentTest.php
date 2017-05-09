<?php

namespace DvsaEntityTest\Entity;

use DvsaEntities\Entity\Organisation;
use DvsaEntities\Entity\TestSlotTransaction;
use DvsaEntities\Entity\TestSlotTransactionAmendment;
use DvsaEntities\Entity\TestSlotTransactionAmendmentReason;
use DvsaEntities\Entity\TestSlotTransactionAmendmentType;
use DvsaEntitiesTest\EntityTrait\EntityTestTrait;
use PHPUnit_Framework_TestCase;

/**
 * Class PaymentTest.
 */
class TestSlotTransactionAmendmentTest extends PHPUnit_Framework_TestCase
{
    use EntityTestTrait;

    public function setUp()
    {
        $this->entity = new TestSlotTransactionAmendment();
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
                        'organisation' => new Organisation(),
                        'testSlotTransaction' => new TestSlotTransaction(),
                        'type' => new TestSlotTransactionAmendmentType(),
                        'reason' => new TestSlotTransactionAmendmentReason(),
                        'slots' => 100,
                        'previousReceiptReference' => 'reference',
                    ],
                ],
            ];
    }
}
