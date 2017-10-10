<?php

namespace DvsaEntities\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use DvsaCommon\Utility\ArrayUtils;
use DvsaEntities\EntityTrait\CommonIdentityTrait;

/**
 * RfrDeficiencyCategory
 *
 * @ORM\Table(name="rfr_deficiency_category", options={"collate"="utf8_general_ci", "charset"="utf8", "engine"="InnoDB"})
 * @ORM\Entity(readOnly=true)
 * @ORM\Cache(usage="READ_ONLY", region="staticdata")
 */
class RfrDeficiencyCategory extends Entity
{
    use CommonIdentityTrait;

    /**
     * @var string
     * @ORM\Column(name="code", type="string", length=2, nullable=true)
     */
    private $code;

    /**
     * @var string
     * @ORM\Column(name="description", type="string", length=30, nullable=true)
     */
    private $description;

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
     * @param string $description
     *
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }
}