<?php

namespace Dvsa\Mot\Api\ServiceModule\Model;

use Zend\Stdlib\AbstractOptions;

class ApiServicesConfigOptions extends AbstractOptions
{
    /** @var  string */
    private $authorisationServiceUrl;

    /** @var string */
    private $vehicleServiceUrl;

    /**
     * @param $serviceUrl
     * @return $this
     */
    public function setAuthorisationServiceUrl($serviceUrl)
    {
        $this->authorisationServiceUrl = $serviceUrl;
        return $this;
    }

    /**
     * @return string
     */
    public function getAuthorisationServiceUrl()
    {
        return $this->authorisationServiceUrl;
    }

    /**
     * @param $serviceUrl
     * @return $this
     */
    public function setVehicleServiceUrl($serviceUrl)
    {
        $this->vehicleServiceUrl = $serviceUrl;
        return $this;
    }

    /**
     * @return string
     */
    public function getVehicleServiceUrl()
    {
        return $this->vehicleServiceUrl;
    }
}
