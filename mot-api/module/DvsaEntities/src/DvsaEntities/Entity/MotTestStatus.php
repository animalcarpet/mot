<?php

namespace DvsaEntities\Entity;

use Doctrine\ORM\Mapping as ORM;
use DvsaEntities\EntityTrait\CommonIdentityTrait;

/**
 * MotTestStatus.
 *
 * @ORM\Table(name="mot_test_status")
 * @ORM\Entity(readOnly=true)
 * @ORM\Entity(repositoryClass="DvsaEntities\Repository\MotTestStatusRepository")
 * @ORM\Cache(usage="READ_ONLY", region="staticdata")
 */
class MotTestStatus
{
    use CommonIdentityTrait;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=10, nullable=false)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="code", type="string", length=5, nullable=false)
     */
    private $code;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=250, nullable=false)
     */
    private $description;

    /**
     * Get motTestStatus.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $name
     *
     * @return MotTestStatus
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }
}
