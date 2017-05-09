<?php

namespace DvsaEntities\Entity;

use DvsaEntities\EntityTrait\CommonIdentityTrait;
use DvsaEntities\EntityTrait\EnumType1EntityTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="DvsaEntities\Repository\CpmsNotificationStatusRepository")
 * @ORM\Table(name="cpms_notification_status")
 * @ORM\Cache(usage="READ_ONLY", region="staticdata")
 */
class CpmsNotificationStatus extends Entity
{
    use CommonIdentityTrait;
    use EnumType1EntityTrait;

    const RECIEVED_CODE = 'R';
    const REJECTED_CODE = 'RJ';
    const PROCESSSED_CODE = 'P';

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=20, nullable=false)
     */
    private $name;

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
