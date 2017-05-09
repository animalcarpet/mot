<?php

namespace DvsaEntities\Entity;

use Doctrine\ORM\Mapping as ORM;
use DvsaEntities\EntityTrait\CommonIdentityTrait;

/**
 * DvlaMake.
 *
 * @ORM\Table(
 *  name="dvla_make",
 *  indexes={
 *      @ORM\Index(name="fk_dvla_make_created_by", columns={"created_by"}),
 *      @ORM\Index(name="fk_dvla_make_last_updated_by", columns={"last_updated_by"})
 *  }
 * )
 * @ORM\Entity(repositoryClass="DvsaEntities\Repository\DvlaMakeRepository", readOnly=true)
 * @ORM\Cache(usage="READ_ONLY", region="staticdata")
 */
class DvlaMake extends Entity
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
     * @param string $code
     *
     * @return DvlaMake
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
     * @param string $name
     *
     * @return DvlaMake
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
