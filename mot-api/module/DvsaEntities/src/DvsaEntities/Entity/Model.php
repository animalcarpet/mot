<?php
namespace DvsaEntities\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use DvsaEntities\EntityTrait\CommonIdentityTrait;

/**
 * Model
 * @ORM\Table(name="model", indexes={@ORM\Index(name="fk_model_created_by", columns={"created_by"}), @ORM\Index(name="fk_model_last_updated_by", columns={"last_updated_by"}), @ORM\Index(name="fk_model_make_code", columns={"make_code"})})
 * @ORM\Entity(repositoryClass="DvsaEntities\Repository\ModelRepository", readOnly=true)
 * @ORM\Cache(usage="READ_ONLY", region="staticdata")
 */
class Model extends Entity
{
    use CommonIdentityTrait;

    /**
     * @var Make
     *
     * @ORM\ManyToOne(targetEntity="Make")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="make_id", referencedColumnName="id")
     * })
     */
    private $make;

    /**
     * @var string
     *
     * @ORM\Column(name="make_id", type="integer", length=5, nullable=false)
     */
    private $makeId;

    /**
     * @var string
     *
     * @ORM\Column(name="code", type="string", length=5, nullable=false)
     */
    private $code;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=45, nullable=false)
     */
    private $name;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_verified", type="boolean")
     */
    private $isVerified;

    public function __construct()
    {
        $this->modelDetails = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * @param string $code
     *
     * @return $this
     */
    public function setCode($code)
    {
        $this->code = $code;
        return $this;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param \DvsaEntities\Entity\Make $make
     * @return $this
     */
    public function setMake(Make $make)
    {
        $this->make = $make;
        return $this;
    }

    /**
     * @return \DvsaEntities\Entity\Make
     */
    public function getMake()
    {
        return $this->make;
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return boolean
     */
    public function isVerified()
    {
        return $this->isVerified;
    }
}
