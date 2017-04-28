<?php

namespace Dvsa\Mot\Frontend\SecurityCardModule\CardActivation\ViewModel;

class RegisterCardHardStopViewModel
{
    private $helpdeskConfig;

    private $registerRoute;

    private $logoutRoute;

    /**
     * @return mixed
     */
    public function getHelpdeskConfig()
    {
        return $this->helpdeskConfig;
    }

    /**
     * @param mixed $helpdeskConfig
     *
     * @return RegisterCardHardStopViewModel
     */
    public function setHelpdeskConfig($helpdeskConfig)
    {
        $this->helpdeskConfig = $helpdeskConfig;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getRegisterRoute()
    {
        return $this->registerRoute;
    }

    /**
     * @param mixed $registerRoute
     */
    public function setRegisterRoute($registerRoute)
    {
        $this->registerRoute = $registerRoute;
    }

    /**
     * @return mixed
     */
    public function getLogoutRoute()
    {
        return $this->logoutRoute;
    }

    /**
     * @param mixed $logoutRoute
     */
    public function setLogoutRoute($logoutRoute)
    {
        $this->logoutRoute = $logoutRoute;
    }
}
