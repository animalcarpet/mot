<?php

namespace DvsaEntities\Entity;

use DvsaEntities\EntityTrait\CommonIdentityTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="DvsaEntities\Repository\CpmsNotificationScopeRepository")
 * @ORM\Table(name="cpms_notification_scope")
 * @ORM\Cache(usage="READ_ONLY", region="staticdata")
 */
class CpmsNotificationScope extends Entity
{
    use CommonIdentityTrait;

    /**
     * @var string code for given enum entity auto-generated found in \DvsaCommon\Enum\
     *
     * @ORM\Column(name="code", type="string", length=20, nullable=false)
     */
    private $code;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=30, nullable=false)
     */
    private $name;

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
     * @return string
     */
    public function getName()
    {
        return $this->name;
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
}
