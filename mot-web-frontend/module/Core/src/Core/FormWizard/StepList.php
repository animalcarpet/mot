<?php
namespace Core\FormWizard;

use Core\Collection\Collection;

class StepList extends Collection
{
    /**
     * StepList constructor.
     * @param AbstractStep[] $data
     */
    public function __construct(array $data = [])
    {
        parent::__construct(AbstractStep::class, $data);
    }

    public function filter(\Closure $closure)
    {
        return new static($this->filterData($closure));
    }

    public function map(\Closure $closure)
    {
        return new static($this->mapData($closure));
    }
}
