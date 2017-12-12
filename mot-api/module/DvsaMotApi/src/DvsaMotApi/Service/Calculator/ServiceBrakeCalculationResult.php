<?php
declare(strict_types=1);

namespace DvsaMotApi\Service\Calculator;

class ServiceBrakeCalculationResult
{
    /** @var bool $isPassing */
    private $isPassing;
    /** @var string $failureSeverity */
    private $failureSeverity;

    /**
     * @param bool $isPassing
     * @param string $failureSeverity
     */
    public function __construct(bool $isPassing, string $failureSeverity)
    {
        $this->isPassing = $isPassing;
        $this->failureSeverity = $failureSeverity;
    }

    /**
     * @return bool
     */
    public function isPassing() : bool
    {
        return $this->isPassing;
    }

    /**
     * @return string
     */
    public function getFailureSeverity() : string
    {
        return $this->failureSeverity;
    }
}