<?php

namespace DvsaEntitiesTest\Entity;

use DvsaEntities\Entity\TestItemSelector;
use PHPUnit_Framework_TestCase;

/**
 * Class TestItemSelectorTest
 * TODO update test.
 */
class TestItemSelectorTest extends PHPUnit_Framework_TestCase
{
    public function testInitialState()
    {
        $testItemSelector = new TestItemSelector();

        $this->assertNull(
            $testItemSelector->getSectionTestItemSelectorId(),
            '"section test item selector id" should initially be null'
        );
        $this->assertNull(
            $testItemSelector->getParentTestItemSelectorId(), '"parent test item selector id" should initially be null'
        );
    }

    public function testSetsPropertiesCorrectly()
    {
        $data = array(
            'sectionTestItemSelectorId' => 1,
            'parentTestItemSelectorId' => 1,
        );
        $testItemSelector = new TestItemSelector();
        $testItemSelector
            ->setSectionTestItemSelectorId($data['sectionTestItemSelectorId'])
            ->setParentTestItemSelectorId($data['parentTestItemSelectorId']);

        $this->assertEquals($data['sectionTestItemSelectorId'], $testItemSelector->getSectionTestItemSelectorId());
        $this->assertEquals($data['parentTestItemSelectorId'], $testItemSelector->getParentTestItemSelectorId());
    }
}
