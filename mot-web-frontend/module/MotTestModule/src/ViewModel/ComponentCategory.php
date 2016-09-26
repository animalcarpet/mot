<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace Dvsa\Mot\Frontend\MotTestModule\ViewModel;

class ComponentCategory
{
    /**
     * @var int
     */
    private $rootCategoryId;

    /**
     * @var int
     */
    private $parentCategoryId;

    /**
     * @var int
     */
    private $categoryId;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $description;

    /**
     * @var string[]
     */
    private $descriptions;

    /**
     * @var int[]
     */
    private $vehicleClasses;

    /**
     * @var DefectCollection
     */
    private $defects;

    /**
     * ComponentCategory constructor.
     *
     * @param $rootCategoryId
     * @param $parentCategoryId
     * @param $categoryId
     * @param $name
     * @param $description
     * @param array            $descriptions
     * @param array            $vehicleClasses
     * @param DefectCollection $defects
     */
    public function __construct($rootCategoryId,
                                $parentCategoryId,
                                $categoryId,
                                $name,
                                $description,
                                array $descriptions,
                                array $vehicleClasses,
                                DefectCollection $defects = null
    ) {
        $this->rootCategoryId = $rootCategoryId;
        $this->parentCategoryId = $parentCategoryId;
        $this->categoryId = $categoryId;
        $this->name = $name;
        $this->description = $description;
        $this->descriptions = $descriptions;
        $this->vehicleClasses = $vehicleClasses;
        $this->defects = $defects;
    }

    /**
     * @return int
     */
    public function getRootCategoryId()
    {
        return $this->rootCategoryId;
    }

    /**
     * @return int
     */
    public function getParentCategoryId()
    {
        return $this->parentCategoryId;
    }

    /**
     * @return int
     */
    public function getCategoryId()
    {
        return $this->categoryId;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return string[]
     */
    public function getDescriptions()
    {
        return $this->descriptions;
    }

    /**
     * @return int[]
     */
    public function getVehicleClasses()
    {
        return $this->vehicleClasses;
    }

    /**
     * @return DefectCollection
     */
    public function getDefectsCollection()
    {
        return $this->defects;
    }
}