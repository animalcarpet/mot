<?php

namespace Dvsa\Mot\Frontend\SecurityCardModule\CardValidation\ViewModel;

/**
 * Class for views relating to PIN failure.
 */
class PinFailLockedViewModel
{
    /**
     * @var int Max number of attempts..
     */
    private $maxAttempts = 0;

    /**
     * @var int Number of mins user will be locked out for
     */
    private $lockoutTimeMins = 0;

    /**
     * @param $maxAttempts
     */
    public function setMaxAttempts($maxAttempts)
    {
        $this->maxAttempts = $maxAttempts;
    }

    /**
     * @param $lockoutTimeMins
     */
    public function setLockoutTimeMins($lockoutTimeMins)
    {
        $this->lockoutTimeMins = $lockoutTimeMins;
    }

    /**
     * @return int
     */
    public function getMaxAttempts()
    {
        return $this->maxAttempts;
    }

    /**
     * @return int
     */
    public function getLockoutTimeMins()
    {
        return $this->lockoutTimeMins;
    }
}
