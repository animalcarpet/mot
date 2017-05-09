<?php

namespace DvsaEntityTest\Entity;

use DvsaEntities\Entity\Payment;
use DvsaEntities\Entity\PaymentStatus;
use DvsaEntities\Entity\PaymentType;
use DvsaEntitiesTest\EntityTrait\EntityTestTrait;
use PHPUnit_Framework_TestCase;

/**
 * Class PaymentTest.
 */
class PaymentTest extends PHPUnit_Framework_TestCase
{
    use EntityTestTrait;

    public function setUp()
    {
        $this->entity = new Payment();
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
                        'amount' => 12.00,
                        'receiptReference' => 'test',
                        'details' => [],
                        'status' => new PaymentStatus(),
                        'type' => new PaymentType(),
                    ],
                ],
            ];
    }
}
