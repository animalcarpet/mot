<?php

namespace Dvsa\Mot\Frontend\SecurityCardModule\CardActivation\ViewModel;

class RegisterCardSuccessViewModel
{
    private $hasPendingNominations;

    private $hasNewAedmRole = false;

    /**
     * @return bool
     */
    public function getHasPendingNominations()
    {
        return $this->hasPendingNominations;
    }

    /**
     * @param bool $hasPendingNominations
     */
    public function setHasPendingNominations($hasPendingNominations)
    {
        $this->hasPendingNominations = $hasPendingNominations;
    }

    /**
     * @return bool
     */
    public function getHasNewAedmRole()
    {
        return $this->hasNewAedmRole;
    }

    /**
     * @param bool $hasNewAedmRole
     */
    public function setHasNewAedmRole($hasNewAedmRole)
    {
        $this->hasNewAedmRole = $hasNewAedmRole;
    }
}
