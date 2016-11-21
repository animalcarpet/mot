<?php

namespace DvsaMotTest\ViewModel\MotTestCertificate;

use Core\Routing\MotTestRoutes;
use Zend\Mvc\Controller\Plugin\Url;

class MotTestCertificateListViewModel
{
    /** @var boolean */
    private $foundByRegistration = true;
    /** @var VehicleTable[] $tables */
    private $tables;
    private $tablesCount = 0;

    /**
     * @return boolean
     */
    public function isFoundByRegistration()
    {
        return $this->foundByRegistration;
    }

    public function getReturnLinkText()
    {
        if ($this->isFoundByRegistration()) {
            return 'Back to search by registration mark';
        }
        return 'Back to search by VIN';
    }

    public function getReturnLink(Url $url)
    {
        $motTestRoutes = MotTestRoutes::of($url);
        if ($this->isFoundByRegistration()) {
            return $motTestRoutes->vehicleSearchByRegistration($this->getRegistration());
        }
        return $motTestRoutes->vehicleSearchByVin($this->getVin());
    }
    
    public function getFoundByType()
    {
        if ($this->isFoundByRegistration()) {
            return 'registration';
        }
        return 'VIN';
    }

    public function getFoundByValue()
    {
        if ($this->isFoundByRegistration()) {
            return $this->getRegistration();
        }
        return $this->getVin();
    }

    /**
     * @param boolean $foundByRegistration
     */
    public function setFoundByRegistration($foundByRegistration)
    {
        $this->foundByRegistration = $foundByRegistration;
    }

    /**
     * @return string
     */
    public function getRegistration()
    {
        return $this->tables[0]->getRegistration();
    }

    /**
     * @return string
     */
    public function getVin()
    {
        return $this->tables[0]->getVin();
    }

    /**
     * @param VehicleTable $table
     */
    public function addTable(VehicleTable $table)
    {
        $table->setIndex($this->tablesCount);
        $this->tables[] = $table;
        $this->tablesCount++;
    }

    /**
     * @return VehicleTable[]
     */
    public function getTables()
    {
        return $this->tables;
    }

    public function getFoundVehiclesCount()
    {
        return $this->tablesCount;
    }
}