<?php

namespace Dvsa\Mot\Frontend\SecurityCardModule\CardActivation\ViewModel;

class RegisterCardSuccessViewModel
{
    private $hasPendingNominations;

    private $hasNewAedmRole = false;

    /**
     * @return boolean
     */
    public function getHasPendingNominations()
    {
        return $this->hasPendingNominations;
    }

    /**
     * @param boolean $hasPendingNominations
     */
    public function setHasPendingNominations($hasPendingNominations)
    {
        $this->hasPendingNominations = $hasPendingNominations;
    }

    /**
     * @return boolean
     */
    public function getHasNewAedmRole()
    {
        return $this->hasNewAedmRole;
    }

    /**
     * @param boolean $hasNewAedmRole
     */
    public function setHasNewAedmRole($hasNewAedmRole)
    {
        $this->hasNewAedmRole = $hasNewAedmRole;
    }
}
