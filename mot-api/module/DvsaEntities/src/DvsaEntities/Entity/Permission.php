<?php

namespace DvsaEntities\Entity;

use Doctrine\ORM\Mapping as ORM;
use DvsaEntities\EntityTrait\CommonIdentityTrait;

/**
 * Permission.
 *
 * @ORM\Table(
 *  name="permission",
 *  indexes={
 *      @ORM\Index(name="created_by", columns={"created_by"}),
 *      @ORM\Index(name="last_updated_by", columns={"last_updated_by"})
 *  }
 * )
 * @ORM\Entity(readOnly=true)
 * @ORM\Cache(usage="READ_ONLY", region="staticdata")
 */
class Permission
{
    use CommonIdentityTrait;

    /**
     * @var string
     * @ORM\Column(name="code", type="string", length=50, nullable=true)
     */
    private $code;

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }
}
