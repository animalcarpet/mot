<?php

namespace Dashboard\ViewModel;

class VehicleTestingStationViewModel
{
    /** @var string $url */
    private $url;

    /** @var int $siteNumber */
    private $siteNumber;

    /** @var string $name */
    private $name;

    /** @var array $positions */
    private $positions;

    /**
     * VehicleTestingStationViewModel constructor.
     *
     * @param string $url
     * @param int    $siteNumber
     * @param string $name
     * @param array  $positions
     */
    public function __construct($url, $siteNumber, $name, $positions)
    {
        $this->url = $url;
        $this->siteNumber = $siteNumber;
        $this->name = $name;
        $this->positions = $positions;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @return int
     */
    public function getSiteNumber()
    {
        return $this->siteNumber;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return array
     */
    public function getPositions()
    {
        return $this->positions;
    }
}