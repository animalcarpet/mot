<?php

namespace Dvsa\Mot\Frontend\MotTestModule\ViewModel;


class ComponentCategoryColumn
{
    /**
     * @var ComponentCategory[]
     */
    private $componentCategories;

    /**
     * @var string
     */
    private $columnTitle;

    public function __construct(array $componentCategories, $columnTitle)
    {
        $this->componentCategories = $componentCategories;
        $this->columnTitle = $columnTitle;
    }

    /**
     * @return ComponentCategory[]
     */
    public function getComponentCategories()
    {
        return $this->componentCategories;
    }

    /**
     * @return string
     */
    public function getColumnTitle()
    {
        return $this->columnTitle;
    }
}