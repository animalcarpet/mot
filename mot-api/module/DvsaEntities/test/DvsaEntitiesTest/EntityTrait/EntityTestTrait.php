<?php

namespace DvsaEntitiesTest\EntityTrait;

trait EntityTestTrait
{
    protected $entity;

    /**
     * Method to test setters and getters for entity.
     *
     * @group spms
     * @dataProvider dataProvider
     */
    public function testSetsPropertiesCorrectly($data)
    {
        // set the data
        foreach ($data as $property => $value) {
            $method = 'set'.ucfirst($property);
            $this->entity->$method($value);
        }

        // test the data being set
        foreach ($data as $property => $value) {
            $method = 'get'.ucfirst($property);
            $this->assertEquals($value, $this->entity->$method());
        }
    }
}
