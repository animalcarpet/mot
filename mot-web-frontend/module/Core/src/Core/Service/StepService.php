<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Core\Service;

use Core\Step\Step;

/**
 * Service used to manage steps through the paged workflow.
 */
class StepService implements \Countable, \IteratorAggregate
{
    /**
     * @var int
     */
    private $position = 0;

    /**
     * @var array
     */
    protected $steps = [];

    /**
     * @var array
     */
    private $idLookup = [];

    /**
     * @param array $steps
     */
    public function __construct(array $steps = [])
    {
        $this->position = 0;

        foreach ($steps as $step) {
            $this->add($step);
        }
    }

    /**
     * Returns the next step.
     *
     * @return Step|false
     */
    public function next()
    {
        if (!$this->isLast()) {
            ++$this->position;

            return $this->current();
        }

        return false;
    }

    /**
     * Returns the previous step.
     *
     * @return Step|false
     */
    public function previous()
    {
        if ($this->position !== 0) {
            --$this->position;

            return $this->current();
        }

        return false;
    }

    /**
     * Returns the current step.
     *
     * @throws \Exception
     *
     * @return Step
     */
    public function current()
    {
        if (!$this->exists()) {
            throw new \Exception('Current step not set');
        }

        return $this->steps[$this->position];
    }

    /**
     * Returns the position of the current pointer.
     *
     * @return int
     */
    private function position()
    {
        return $this->position;
    }

    /**
     * Sets the position back to the first element in the stack.
     *
     * @return $this
     */
    public function rewind()
    {
        $this->position = 0;

        return $this;
    }

    /**
     * Checks to see if the step is within bounds.
     *
     * @return bool
     */
    private function exists()
    {
        return isset($this->steps[$this->position]);
    }

    /**
     * @param string $id
     *
     * @throws \Exception if id does not exist
     *
     * @return Step
     */
    public function getById($id)
    {
        $this->assertIdExists($id);
        $index = $this->idLookup[$id];

        return $this->steps[$index];
    }

    /**
     * @param Step $step
     *
     * @throws \Exception if id already exists in the lookup
     *
     * @return int
     */
    public function add(Step $step)
    {
        // Build the idLookup before adding to the steps;
        $index = count($this->steps);
        $id = $step->getId();
        if (isset($this->idLookup[$id])) {
            throw new \Exception("Step with id {$id} already exists");
        }
        $this->idLookup[$id] = $index;
        $this->steps[] = $step;

        return $index;
    }

    /**
     * Returns the Step of the first step.
     *
     * @throws \Exception
     *
     * @return Step
     */
    public function first()
    {
        $this->assertHasSteps();

        return $this->steps[0];
    }

    /**
     * Returns the Step of the last step.
     *
     * @throws \Exception
     *
     * @return Step
     */
    public function last()
    {
        $this->assertHasSteps();
        $index = count($this->steps) - 1;

        return $this->steps[$index];
    }

    /**
     * Checks to see if the current step is the last step.
     *
     * @return bool
     */
    public function isLast()
    {
        $maxIndex = count($this->steps) - 1;

        return $this->position === $maxIndex;
    }

    /**
     * Sets the current active step by Id.
     *
     * @param string $id
     *
     * @throws \Exception if the step does not exist
     *
     * @return StepService
     */
    public function setActiveById($id)
    {
        $this->assertIdExists($id);
        $index = $this->idLookup[$id];
        $this->position = $index;

        return $this;
    }

    /**
     * Sets the active step by a Step.
     *
     * @param Step $step
     *
     * @throws \Exception if the step does not exist
     *
     * @return bool
     */
    public function setActiveByStep(Step $step)
    {
        $id = $step->getId();

        return $this->setActiveById($id);
    }

    /**
     * Gets the total number of steps that will be displayed on the frontend.
     *
     * @return int
     */
    public function count()
    {
        return count($this->steps);
    }

    /**
     * Gets the current step number - this will be displayed on the frontend.
     *
     * @return int
     */
    public function getPosition()
    {
        return $this->position() + 1;
    }

    /**
     * Define how the steps can be iterated.
     *
     * @return array
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->steps);
    }

    /**
     * Get a key/value array of steps and their routes.
     *
     * @return array
     */
    public function getRoutes()
    {
        $result = [];
        foreach ($this->steps as $step) {
            $result[$step->getId()] = $step->route();
        }

        $this->rewind();

        return $result;
    }

    /**
     * Checks to see if the steps array is empty.
     *
     * @throws \Exception
     */
    private function assertHasSteps()
    {
        if (empty($this->steps)) {
            throw new \Exception('Contains no steps');
        }
    }

    /**
     * @param string $id
     *
     * @throws \Exception
     */
    private function assertIdExists($id)
    {
        if (!isset($this->idLookup[$id])) {
            throw new \Exception("ID {$id} does not exist");
        }
    }
}
