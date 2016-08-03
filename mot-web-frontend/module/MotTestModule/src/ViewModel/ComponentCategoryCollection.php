<?php

namespace Dvsa\Mot\Frontend\MotTestModule\ViewModel;

use NumberFormatter;

class ComponentCategoryCollection
{
    private $componentCategory;
    /**
     * The ids of categories that were clicked on in order to get to the
     * rightmost column.
     *
     * @var ComponentCategory[]
     */
    private $categoryPath;

    /**
     * @var ComponentCategoryColumn[]
     */
    private $columns;

    /**
     * Is the current user a VE?
     *
     * @var bool
     */
    private $ve;

    /**
     * ComponentCategoryCollection constructor.
     *
     * @param ComponentCategory         $componentCategory
     * @param ComponentCategory[]       $categoryPath
     * @param ComponentCategoryColumn[] $columns
     * @param bool                      $ve
     */
    public function __construct(
        $componentCategory,
        array $categoryPath,
        array $columns,
        $ve
    ) {
        $this->componentCategory = $componentCategory;
        $this->categoryPath = $categoryPath;
        $this->columns = $columns;
        $this->ve = $ve;
    }

    /**
     * @param array[] $componentCategoriesFromApi
     * @param bool    $ve
     *
     * @return ComponentCategoryCollection
     */
    public static function fromDataFromApi(array $componentCategoriesFromApi, $ve)
    {
        $columns = [];
        $categoryPath = [];
        $componentCategory = null;

        foreach ($componentCategoriesFromApi as $column) {
            $currentColumn = [];
            $name = $column['testItemSelector']['name'];
            array_push($categoryPath, new ComponentCategory(
                $column['testItemSelector']['sectionTestItemSelectorId'],
                $column['testItemSelector']['parentTestItemSelectorId'],
                $column['testItemSelector']['id'],
                $column['testItemSelector']['name'],
                $column['testItemSelector']['description'],
                $column['testItemSelector']['descriptions'],
                $column['testItemSelector']['vehicleClasses'],
                DefectCollection::fromDataFromApi($column)
            ));
            foreach ($column['testItemSelectors'] as $category) {
                $currentCategory = new ComponentCategory(
                    $category['sectionTestItemSelectorId'],
                    $category['parentTestItemSelectorId'],
                    $category['id'],
                    $category['name'],
                    $category['description'],
                    $category['descriptions'],
                    $category['vehicleClasses'],
                    DefectCollection::fromDataFromApi($column)
                );
                array_push($currentColumn, $currentCategory);
            }
            $newColumn = new ComponentCategoryColumn($currentColumn, $name);
            array_push($columns, $newColumn);
        }

        $column = end($componentCategoriesFromApi);
        $componentCategory = new ComponentCategory(
            $column['testItemSelector']['sectionTestItemSelectorId'],
            $column['testItemSelector']['parentTestItemSelectorId'],
            $column['testItemSelector']['id'],
            $column['testItemSelector']['name'],
            $column['testItemSelector']['description'],
            $column['testItemSelector']['descriptions'],
            $column['testItemSelector']['vehicleClasses'],
            DefectCollection::fromDataFromApi($column)
        );

        return new self($componentCategory, $categoryPath, $columns, $ve);
    }

    /**
     * @return ComponentCategory
     */
    public function getComponentCategory()
    {
        return $this->componentCategory;
    }

    /**
     * @return ComponentCategory[]
     */
    public function getCategoryPath()
    {
        return $this->categoryPath;
    }

    /**
     * @return int[]
     */
    public function getCategoryPathIds()
    {
        $ids = [];

        foreach ($this->categoryPath as $category) {
            $ids[] = $category->getCategoryId();
        }

        return $ids;
    }

    /**
     * @return bool
     */
    public function isVe()
    {
        return $this->ve;
    }

    /**
     * The number to be appended to the `browse--` element.
     * We add one to this to take into account the current category.
     * Column count = length of path to current category + current category.
     *
     * @return string
     */
    public function getColumnCountForHtml()
    {
        $formatter = new NumberFormatter('en_GB', NumberFormatter::SPELLOUT);

        return $formatter->format(count($this->getColumns()));
    }

    /**
     * @return ComponentCategoryColumn[]
     */
    public function getColumns()
    {
        return $this->columns;
    }
}