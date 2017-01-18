<?php
/**
 * This file is part of the DVSA MOT API project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace DvsaEntities\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(
 *  name="mot_test_complaint_ref",
 *     options={
 *         "collate"="utf8_general_ci",
 *         "charset"="utf8",
 *         "engine"="InnoDB"
 *     }
 * )
 * @ORM\Entity
 */
class MotTestComplaintRef extends Entity
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     */
    protected $id;

    /**
     * @ORM\Column(name="complaint_ref", type="string", length=30, nullable=false)
     *
     * @var string
     */
    private $complaintRef;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     * @return MotTestComplaintRef
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }
    
    /**
     * @return string
     */
    public function getComplaintRef()
    {
        return $this->complaintRef;
    }

    /**
     * @param $complaintRef
     * @return $this
     */
    public function setComplaintRef($complaintRef)
    {
        $this->complaintRef = $complaintRef;
        return $this;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getComplaintRef();
    }
}
