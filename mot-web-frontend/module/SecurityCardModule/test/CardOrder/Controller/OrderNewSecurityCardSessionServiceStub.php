<?php

namespace Dvsa\Mot\Frontend\SecurityCardModuleTest\CardOrder\Controller;

use Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\Service\OrderNewSecurityCardSessionService;

class OrderNewSecurityCardSessionServiceStub extends OrderNewSecurityCardSessionService
{
    private $sessionData;

    public function __construct()
    {
        $this->sessionData = [];
    }

    /**
     * Clear and Kill the Session.
     */
    public function destroy()
    {
    }

    /**
     * Clear all the data from sessionStorage.
     */
    public function clear()
    {
        $this->sessionData = [];
    }

    /**
     * @param $key
     *
     * @return array|mixed
     */
    public function load($key)
    {
        return array_key_exists($key, $this->sessionData) ? $this->sessionData[$key] : [];
    }

    /**
     * @param $key
     * @param $value
     */
    public function save($key, $value)
    {
        $this->sessionData[$key] = $value;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return $this->sessionData;
    }
}
