<?php

namespace DvsaEntities\Entity;

/**
 * Interface for Vehicle and DvlaVehicle classes.
 */
interface VehicleInterface
{
    /**
     * @return \DateTime
     *
     * Date vehicle was first registered
     */
    public function getFirstRegistrationDate();

    /**
     * @return \DateTime
     *
     * First used date is either derived from DVLA vehicle data (Date of Manufacture and Date of First Registration) at
     * time of test registration or is entered by the NT
     */
    public function getFirstUsedDate();

    /**
     * @return Make|null
     */
    public function getMake();

    /**
     * @return \DateTime
     */
    public function getManufactureDate();

    /**
     * @return Model|null
     */
    public function getModel();

    /**
     * @return ModelDetail|null
     */
    public function getModelDetail();

    /**
     * @return bool
     */
    public function isVehicleNewAtFirstRegistration();

    /**
     * @return bool
     */
    public function isDvla();
}
