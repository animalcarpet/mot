<?php

namespace Dvsa\Mot\Frontend\ServiceModule\Model;

use Zend\Stdlib\AbstractOptions;

class ApiServicesConfigOptions extends AbstractOptions
{
    /** @var string */
    private $authorisationServiceUrl;

    /** @var string */
    private $vehicleServiceUrl;

    /** @var string */
    private $motTestServiceUrl;

    /**
     * @param $serviceUrl
     *
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
     *
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

    /**
     * @return string
     */
    public function getMotTestServiceUrl()
    {
        return $this->motTestServiceUrl;
    }

    /**
     * @param $motTestServiceUrl
     *
     * @return $this
     */
    public function setMotTestServiceUrl($motTestServiceUrl)
    {
        $this->motTestServiceUrl = $motTestServiceUrl;

        return $this;
    }
}
