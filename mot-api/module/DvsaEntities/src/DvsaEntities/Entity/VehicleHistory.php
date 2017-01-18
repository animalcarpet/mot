<?php
/**
 * This file is part of the DVSA MOT API project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace DvsaEntities\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class VehicleHistory
 *
 * @ORM\Table(name="vehicle_hist")
 * @ORM\Entity(readOnly=true)
 */
class VehicleHistory extends VehicleAbstract
{
    const MSG_IMMUTABLE_EXCEPTION = 'VehicleHistory is a readonly entity!';

    /**
     * @var integer
     *
     * @ORM\Column(name="hist_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var Vehicle
     *
     * @ORM\ManyToOne(targetEntity="Vehicle", inversedBy="vehicleHistory")
     * @ORM\JoinColumn(name="id", referencedColumnName="id")
     */
    private $vehicle;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Vehicle
     */
    public function getVehicle()
    {
        return $this->vehicle;
    }

    public function __call($name, $value)
    {
        throw new \LogicException(self::MSG_IMMUTABLE_EXCEPTION);
    }
}
