<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Event\Step;

class SummaryStep extends AbstractEventStep
{
    /**
     * const used for the session key, available via getId().
     */
    const STEP_ID = 'SUMMARY';

    /**
     * @return string
     */
    public function getId()
    {
        return self::STEP_ID;
    }

    /**
     * Load the steps data from the session storage.
     *
     * @return array
     */
    public function load()
    {
        return $this;
    }

    /**
     * @param array $values
     *
     * @return mixed
     */
    public function readFromArray(array $values)
    {
        return;
    }

    /**
     * Export the step values as an array.
     *
     * @return array
     */
    public function toArray()
    {
        $values = $this->sessionService->toArray();

        return array_reduce($values, 'array_merge', []);
    }

    /**
     * A function that must be implemented in the step. This is how
     * we enforce that the logic of what is saved within a step, stays within that step.
     *
     * @return bool
     */
    protected function saveToSession()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function getCleanFilterWhiteList()
    {
        return [];
    }

    /**
     * The route for this step.
     *
     * @return mixed
     */
    public function route()
    {
        return 'event-manual-add/summary';
    }

    /**
     * describes the steps progress in the event process.
     *
     * Step 1 of 3
     * Step 2 of 3
     * etc
     *
     * @return string|null
     */
    public function getProgress()
    {
        return 'Step 3 of 3';
    }
}
