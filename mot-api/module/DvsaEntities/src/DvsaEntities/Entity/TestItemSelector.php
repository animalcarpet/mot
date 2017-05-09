<?php

namespace DvsaEntities\Entity;

use Doctrine\ORM\Mapping as ORM;
use DvsaEntities\EntityTrait\CommonIdentityTrait;

/**
 * TestItemSelector.
 *
 * @ORM\Table(name="test_item_category", options={"collate"="utf8_general_ci", "charset"="utf8", "engine"="InnoDB"})
 * @ORM\Entity(readOnly=true)
 * @ORM\Cache(usage="READ_ONLY", region="staticdata")
 */
class TestItemSelector
{
    use CommonIdentityTrait;

    /**
     * Owning side.
     *
     * @ORM\ManyToMany(targetEntity="VehicleClass")
     * @ORM\JoinTable(name="test_item_category_vehicle_class_map",
     *      joinColumns={@ORM\JoinColumn(name="test_item_category_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="vehicle_class_id", referencedColumnName="id")}
     *      )
     */
    private $vehicleClasses;

    /**
     * @var int
     *
     * @ORM\Column(name="section_test_item_category_id", type="integer", nullable=true)
     */
    private $sectionTestItemSelectorId;

    /**
     * @var int
     *
     * @ORM\Column(name="parent_test_item_category_id", type="integer", nullable=true)
     */
    private $parentTestItemSelectorId;

    /**
     * @var \Doctrine\Common\Collections\Collection|TestItemCategoryDescription[]
     *
     * @ORM\OneToMany(
     *  targetEntity="DvsaEntities\Entity\TestItemCategoryDescription",
     *  mappedBy="testItemCategory",
     *  fetch="LAZY",
     *  cascade={"persist"}
     * )
     */
    private $descriptions;

    public function __construct()
    {
        $this->vehicleClasses = new \Doctrine\Common\Collections\ArrayCollection();
        $this->descriptions = new \Doctrine\Common\Collections\ArrayCollection();
    }

    // TODO VM-3386 remove
    /**
     * Set name.
     *
     * @param string $name
     *
     * @return TestItemSelector
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    // TODO VM-3386 remove
    /**
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    // TODO VM-3386 remove
    /**
     * Set cy name.
     *
     * @param string $name
     *
     * @return TestItemSelector
     */
    public function setNameCy($name)
    {
        $this->nameCy = $name;

        return $this;
    }

    /**
     * Set vehicleClasses.
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $vehicleClasses
     *
     * @return TestItemSelector
     */
    public function setVehicleClasses(\Doctrine\Common\Collections\ArrayCollection $vehicleClasses = null)
    {
        $this->vehicleClasses = $vehicleClasses;

        return $this;
    }

    /**
     * Get vehicleClasses.
     *
     * @return \Doctrine\Common\Collections\ArrayCollection()
     */
    public function getVehicleClasses()
    {
        return $this->vehicleClasses;
    }

    /**
     * Set sectionTestItemSelectorId.
     *
     * @param int $sectionTestItemSelectorId
     *
     * @return TestItemSelector
     */
    public function setSectionTestItemSelectorId($sectionTestItemSelectorId)
    {
        $this->sectionTestItemSelectorId = $sectionTestItemSelectorId;

        return $this;
    }

    /**
     * Get sectionTestItemSelectorId.
     *
     * @return TestItemSelector
     */
    public function getSectionTestItemSelectorId()
    {
        return $this->sectionTestItemSelectorId;
    }

    /**
     * Set parentTestItemSelectorId.
     *
     * @param int $value
     *
     * @return TestItemSelector
     */
    public function setParentTestItemSelectorId($value)
    {
        $this->parentTestItemSelectorId = $value;

        return $this;
    }

    /**
     * Get parentTestItemSelectorId.
     *
     * @return TestItemSelector
     */
    public function getParentTestItemSelectorId()
    {
        return $this->parentTestItemSelectorId;
    }

    /**
     * @return \Doctrine\Common\Collections\Collection|TestItemCategoryDescription[]
     */
    public function getDescriptions()
    {
        return $this->descriptions;
    }

    /**
     * @param \Doctrine\Common\Collections\Collection|TestItemCategoryDescription[] $descriptions
     *
     * @return $this
     */
    public function setDescriptions($descriptions)
    {
        $this->descriptions = $descriptions;

        return $this;
    }
}
