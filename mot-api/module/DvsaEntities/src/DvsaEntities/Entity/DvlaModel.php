<?php

namespace DvsaEntities\Entity;

use Doctrine\ORM\Mapping as ORM;
use DvsaEntities\EntityTrait\CommonIdentityTrait;

/**
 * DvlaModel.
 *
 * @ORM\Table(
 *  name="dvla_model",
 *  indexes={
 *      @ORM\Index(name="fk_dvla_model_person_created_by", columns={"created_by"}),
 *      @ORM\Index(name="fk_dvla_model_person_last_updated_by", columns={"last_updated_by"})
 *  }
 * )
 * @ORM\Entity(repositoryClass="DvsaEntities\Repository\DvlaModelRepository", readOnly=true)
 * @ORM\Cache(usage="READ_ONLY", region="staticdata")
 */
class DvlaModel extends Entity
{
    use CommonIdentityTrait;

    /**
     * @var string
     *
     * @ORM\Column(name="code", type="string", length=5, nullable=false)
     */
    private $code;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=50, nullable=false)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="make_code", type="string", length=5, nullable=false)
     */
    private $make_code;

    /**
     * @param string $code
     *
     * @return DvlaModel
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
     * @param string $make_code
     *
     * @return DvlaModel
     */
    public function setMakeCode($make_code)
    {
        $this->make_code = $make_code;

        return $this;
    }

    /**
     * @return string
     */
    public function getMakeCode()
    {
        return $this->make_code;
    }

    /**
     * @param string $name
     *
     * @return DvlaModel
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
}
