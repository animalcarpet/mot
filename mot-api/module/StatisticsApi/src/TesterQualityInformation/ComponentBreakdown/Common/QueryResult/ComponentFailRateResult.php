<?php

namespace Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\ComponentBreakdown\Common\QueryResult;

class ComponentFailRateResult
{
    private $testItemCategoryName;
    private $testItemCategoryId;
    private $failedCount;
    private $testerId;

    public function getTestItemCategoryName()
    {
        return $this->testItemCategoryName;
    }

    public function setTestItemCategoryName($testItemCategoryName)
    {
        $this->testItemCategoryName = $testItemCategoryName;

        return $this;
    }

    public function getTestItemCategoryId()
    {
        return $this->testItemCategoryId;
    }

    public function setTestItemCategoryId($testItemCategoryId)
    {
        $this->testItemCategoryId = (int) $testItemCategoryId;

        return $this;
    }

    public function getFailedCount()
    {
        return $this->failedCount;
    }

    public function setFailedCount($failedCount)
    {
        $this->failedCount = (int) $failedCount;

        return $this;
    }

    public function setTesterId($id)
    {
        $this->testerId = $id;

        return $this;
    }

    public function getTesterId()
    {
        return $this->testerId;
    }
}
