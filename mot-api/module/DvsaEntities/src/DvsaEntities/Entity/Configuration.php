<?php

namespace DvsaEntities\Entity;

use Doctrine\ORM\Mapping as ORM;
use DvsaEntities\EntityTrait\CommonIdentityTrait;

/**
 * Configuration
 *
 * @ORM\Table(name="configuration", options={"collate"="utf8_general_ci", "charset"="utf8", "engine"="InnoDB"})
 * @ORM\Entity(repositoryClass="DvsaEntities\Repository\ConfigurationRepository", readOnly=true)
 * @ORM\Cache(usage="READ_ONLY", region="staticdata")
 */
class Configuration extends Entity
{
    const KEY_TEST_SLOT_PRICE = 'testSlotPrice';
    const KEY_TEST_SLOT_BATCH = 'testSlotBatch';
    const KEY_TEST_SLOT_MINIMUM_AMOUNT = 'testSlotMinAmount';
    const KEY_TEST_SLOT_MAXIMUM_AMOUNT = 'testSlotMaxAmount';
    const KEY_DAYS_AFTER_FOR_INCREMENT = 'daysForSlotIncrementAfterDDCollectionDate';
    const DIRECT_DEBIT_DAYS_AHEAD = 'directDebitDaysAhead';

    /**
     * @var string
     *
     * @ORM\Column(name="`key`", type="string", length=30, nullable=false)
     * @ORM\Id
     */
    private $key;

    /**
     * @var string
     *
     * @ORM\Column(name="`value`", type="string", length=50, nullable=true)
     */
    private $value;

    /**
     * Get key
     *
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * Set key
     *
     * @param string $key
     * @return Configuration
     */
    public function setKey($key)
    {
        $this->key = $key;

        return $this;
    }

    /**
     * Get value
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set value
     *
     * @param string $value
     * @return Configuration
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }
}
