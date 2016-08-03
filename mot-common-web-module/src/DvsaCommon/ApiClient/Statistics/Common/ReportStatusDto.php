<?php

namespace DvsaCommon\ApiClient\Statistics\Common;

use DvsaCommon\Date\TimeSpan;
use DvsaCommon\DtoSerialization\ReflectiveDtoInterface;

class ReportStatusDto implements ReflectiveDtoInterface
{
    /** @var \DateTime */
    private $generationStartTime;

    /** @var \DateTime */
    private $generationEndTime;

    /** @var \DvsaCommon\Date\TimeSpan */
    private $generationTime;

    /** @var \DateTime */
    private $generationTimeoutDate;

    private $isCompleted;

    public function getGenerationStartTime()
    {
        return $this->generationStartTime;
    }

    public function setGenerationStartTime(\DateTime $generationStartTime)
    {
        $this->generationStartTime = $generationStartTime;
        return $this;
    }

    public function getGenerationEndTime()
    {
        return $this->generationEndTime;
    }

    public function setGenerationEndTime(\DateTime $generationEndTime)
    {
        $this->generationEndTime = $generationEndTime;
        return $this;
    }

    public function getGenerationTime()
    {
        return $this->generationTime;
    }

    public function setGenerationTime(TimeSpan $generationTime)
    {
        $this->generationTime = $generationTime;
        return $this;
    }

    public function getGenerationTimeoutDate()
    {
        return $this->generationTimeoutDate;
    }

    public function setGenerationTimeoutDate(\DateTime $generationTimeoutDate)
    {
        $this->generationTimeoutDate = $generationTimeoutDate;
        return $this;
    }

    public function getIsCompleted()
    {
        return $this->isCompleted;
    }

    public function setIsCompleted($isCompleted)
    {
        $this->isCompleted = $isCompleted;
        return $this;
    }
}
