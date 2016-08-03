<?php
namespace DvsaEntitiesTest\Entity;

use DvsaEntities\Entity\Make;
use DvsaEntities\Entity\Model;
use DvsaEntities\Entity\ModelDetail;
use DvsaEntities\Entity\Vehicle;
use PHPUnit_Framework_TestCase;

class VehicleMakeAndModelTest extends PHPUnit_Framework_TestCase
{
    /** @var Vehicle */
    private $vehicle;

    /** @var Model */
    private $model;

    /** @var Make */
    private $make;

    /** @var string */
    private $freeTextName;

    /** @var ModelDetail */
    private $modelDetail;

    public function setUp()
    {
        $this->make = new Make();
        $this->make->setName("NameFromMake");

        $this->model = new Model();
        $this->model->setName("NameFromModel");
        $this->model->setMake($this->make);

        $this->freeTextName = "Any text";

        $this->modelDetail = new ModelDetail();

        $this->vehicle = new Vehicle();
        $this->vehicle->setModelDetail($this->modelDetail);
    }

    public function testVehicleModelAndMakeNameAreTakenFromModelDetail()
    {
        // GIVEN I have a vehicle with model set
        $this->vehicle->getModelDetail()->setModel($this->model);

        // THEN the model name is taken from the model
        $this->assertEquals($this->model->getName(), $this->vehicle->getModelName());
        $this->assertEquals($this->make->getName(), $this->vehicle->getMakeName());
    }

    public function testVehicleMakeNameIsTakenFromMakeOfModelWhenVehicleHasModel()
    {
        $this->vehicle->getModelDetail()->setModel($this->model);

        // GIVEN I have a vehicle with model set and free text name for make
        $this->vehicle->getModelDetail()->setModel($this->model);

    }
}
