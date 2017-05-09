<?php

namespace Site\UpdateVtsProperty;

use Core\TwoStepForm\ProcessBuilderInterface;
use Core\TwoStepForm\SingleStepProcessInterface;
use DvsaClient\Mapper\SiteMapper;
use DvsaCommon\Utility\ArrayUtils;

/**
 * Class UpdateVtsPropertyProcessBuilder.
 *
 * @deprecated remove this class
 */
class UpdateVtsPropertyProcessBuilder implements ProcessBuilderInterface
{
    /**
     * @var SingleStepProcessInterface[]
     */
    private $processes = [];

    private $siteMapper;

    public function __construct(SiteMapper $siteMapper)
    {
        $this->siteMapper = $siteMapper;
    }

    public function add(AbstractSingleStepVtsProcess $process)
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
