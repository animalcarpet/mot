<?php

namespace DvsaEntities\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ApplicationStatus.
 *
 * @ORM\Table(name="application_status", options={"collate"="utf8_general_ci", "charset"="utf8", "engine"="InnoDB"})
 * @ORM\Entity
 * TODO should be EnumType1
 */
class ApplicationStatus
{
    /**
     * @var string
     *
     * @ORM\Column(name="application_status", type="string", length=50, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $applicationStatus = '';

    /**
     * Get applicationStatus.
     *
     * @return string
     */
    public function getApplicationStatus()
    {
        return $this->applicationStatus;
    }
}
