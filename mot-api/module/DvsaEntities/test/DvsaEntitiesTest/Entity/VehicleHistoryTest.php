<?php

namespace DvsaEntitiesTest\Entity;

use DvsaEntities\Entity\VehicleHistory;

/**
 * Class VehicleHistoryTest.
 */
class VehicleHistoryTest extends \PHPUnit_Framework_TestCase
{
    public function testEntityIsNotInheritingAnySetter()
    {
        $this->setExpectedException(
            \LogicException::class,
            VehicleHistory::MSG_IMMUTABLE_EXCEPTION
        );

        $vehicleHistory = new VehicleHistory();

        $reflection = new \ReflectionClass($vehicleHistory);

        $methods = $reflection->getMethods();

        $setters = [];
        $allowed = ['setCreatedBy', 'setCreatedOn', 'setLastUpdatedBy', 'setLastUpdatedOn', 'setVersion'];

        foreach ($methods as $method) {
            if (in_array($method->getName(), $allowed)) {
                continue;
            }

            if ('set' == substr($method->getName(), 0, 3)) {
                $setters[] = $method->getName();
            }
        }

        $this->assertEmpty(
            $setters,
            sprintf(
                'VehicleHistory entity must not contain or inherit any setter method. (%s) has been found',
                implode(',', $setters)
            )
        );

        $vehicleHistory->setSomething('asd');
    }
}
