<?php

namespace DvsaEntities\Entity;

use Doctrine\ORM\Mapping as ORM;
use DvsaCommon\Constants\SectionState;
use DvsaEntities\EntityTrait\CommonIdentityTrait;

/**
 * ApplicationSectionState.
 *
 * @ORM\Table(
 * name="application_section_state",
 * options={"collate"="utf8_general_ci", "charset"="utf8", "engine"="InnoDB"}
 * )
 *
 * @ORM\Entity
 */
class ApplicationSectionState
{
    use CommonIdentityTrait;

    const ENTITY_NAME = 'Application Section State';

    /**
     * @var string
     *
     * @ORM\Column(name="uuid", type="string", length=36, nullable=false)
     */
    private $uuid;

    /**
     * @var string
     *
     * @ORM\Column(name="section", type="string", length=50, nullable=false)
     */
    private $section;

    /**
     * @var string
     *
     * @ORM\Column(name="state", type="string", length=50, nullable=false)
     */
    private $state;

    public function __construct($uuid, $section)
    {
        $this->uuid = $uuid;
        $this->section = $section;
        $this->state = SectionState::IN_PROGRESS;
    }

    /**
     * @param string $section
     */
    public function setSection($section)
    {
        $this->section = $section;
    }

    /**
     * @return string
     */
    public function getSection()
    {
        return $this->section;
    }

    /**
     * @param string $state
     */
    public function setState($state)
    {
        $this->state = $state;
    }

    /**
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @param string $uuid
     */
    public function setUuid($uuid)
    {
        $this->uuid = $uuid;
    }

    /**
     * @return string
     */
    public function getUuid()
    {
        return $this->uuid;
    }
}
