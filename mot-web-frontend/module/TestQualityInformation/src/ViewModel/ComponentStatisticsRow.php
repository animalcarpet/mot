<?php
namespace Dvsa\Mot\Frontend\TestQualityInformation\ViewModel;

class ComponentStatisticsRow
{
    protected $categoryId;
    protected $categoryName;
    protected $testerAverage;
    protected $nationalAverage;

    /**
     * @return mixed
     */
    public function getCategoryId()
    {
        return $this->categoryId;
    }

    /**
     * @param mixed $categoryId
     * @return ComponentStatisticsRow
     */
    public function setCategoryId($categoryId)
    {
        $this->categoryId = $categoryId;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTesterAverage()
    {
        return $this->testerAverage;
    }

    /**
     * @param mixed $testerAverage
     * @return ComponentStatisticsRow
     */
    public function setTesterAverage($testerAverage)
    {
        $this->testerAverage = $testerAverage;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getNationalAverage()
    {
        return $this->nationalAverage;
    }

    /**
     * @param mixed $nationalAverage
     * @return ComponentStatisticsRow
     */
    public function setNationalAverage($nationalAverage)
    {
        $this->nationalAverage = $nationalAverage;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCategoryName()
    {
        return $this->categoryName;
    }

    /**
     * @param mixed $categoryName
     * @return ComponentStatisticsRow
     */
    public function setCategoryName($categoryName)
    {
        $this->categoryName = $categoryName;
        return $this;
    }
}