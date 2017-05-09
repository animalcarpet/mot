<?php

namespace DvsaEntities\Entity;

use Doctrine\ORM\Mapping as ORM;
use DvsaEntities\EntityTrait\CommonIdentityTrait;

/**
 * @ORM\Entity(readOnly=true)
 * @ORM\Table(
 *  name="enforcement_condition_appointment_lookup",
 *  options={"collate"="utf8_general_ci", "charset"="utf8", "engine"="InnoDB"}
 * )
 * @ORM\Cache(usage="READ_ONLY", region="staticdata")
 */
class EnforcementConditionAppointment
{
    use CommonIdentityTrait;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=50, nullable=true)
     */
    protected $description;

    /**
     * @param string $description
     *
     * @return EnforcementConditionAppointment
     */
    public function setOutcome($description)
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
