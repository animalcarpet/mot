<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Dvsa\Mot\Frontend\RegistrationModule\Step;

class CompletedStep extends AbstractRegistrationStep
{
    /**
     * const used for the session key, available via getId().
     */
    const STEP_ID = 'COMPLETE';

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
     * @return $this
     */
    public function load()
    {
        return $this;
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
     * Export the step values as an array.
     *
     * @return array
     */
    public function toArray()
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
        // route depends on the result from the registration service
        return 'account-register/complete';
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
}
