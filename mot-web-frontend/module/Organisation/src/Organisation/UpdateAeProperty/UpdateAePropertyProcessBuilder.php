<?php

namespace Organisation\UpdateAeProperty;

use Core\TwoStepForm\ProcessBuilderInterface;
use Core\TwoStepForm\SingleStepProcessInterface;
use DvsaCommon\Utility\ArrayUtils;

/**
 * Class UpdateAePropertyProcessBuilder.
 *
 * @deprecated remove class
 */
class UpdateAePropertyProcessBuilder implements ProcessBuilderInterface
{
    /**
     * @var SingleStepProcessInterface[]
     */
    private $processes = [];

    public function __construct()
    {
    }

    public function add(AbstractSingleStepAeProcess $process)
    {
        $this->processes[$process->getPropertyName()] = $process;
    }

    public function get($propertyName)
    {
        /** @var SingleStepProcessInterface $process */
        $process = ArrayUtils::tryGet($this->processes, $propertyName);

        if ($process === null) {
            throw new \InvalidArgumentException("Process for property '$propertyName' does not exist.");
        }

        return $process;
    }
}
