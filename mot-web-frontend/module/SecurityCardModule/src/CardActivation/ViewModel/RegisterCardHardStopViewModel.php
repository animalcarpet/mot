<?php

namespace Dvsa\Mot\Frontend\SecurityCardModule\CardActivation\ViewModel;

class RegisterCardHardStopViewModel
{
    private $helpdeskConfig;

    /**
     * @return mixed
     */
    public function getHelpdeskConfig()
    {
        return $this->helpdeskConfig;
    }

    /**
     * @param mixed $helpdeskConfig
     * @return RegisterCardHardStopViewModel
     */
    public function setHelpdeskConfig($helpdeskConfig)
    {
        $this->helpdeskConfig = $helpdeskConfig;
        return $this;
    }
}