<?php

namespace DvsaEntitiesTest\Entity;

use DvsaEntities\Entity\Configuration;
use PHPUnit_Framework_TestCase;

/**
 * Class ConfigurationTest.
 */
class ConfigurationTest extends PHPUnit_Framework_TestCase
{
    public function testInitialState()
    {
        $configuration = new Configuration();
        $this->assertNull($configuration->getKey());
        $this->assertNull($configuration->getValue());
    }

    public function testSetsPropertiesCorrectly()
    {
        $data = [
            'key' => 'thekey',
            'value' => 'thevalue',
        ];

        $configuration = new Configuration();
        $configuration->setKey($data['key'])
            ->setValue($data['value']);

        $this->assertEquals($data['key'], $configuration->getKey());
        $this->assertEquals($data['value'], $configuration->getValue());
    }
}
