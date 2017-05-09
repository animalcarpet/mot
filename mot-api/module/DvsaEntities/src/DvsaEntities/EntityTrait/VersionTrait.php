<?php
/**
 * Version trait.
 *
 * @author Jakub Igla <jakub.igla@valtech.co.uk>
 */

namespace DvsaEntities\EntityTrait;

/**
 * Version trait.
 */
trait VersionTrait
{
    /**
     * @var int
     *
     * @ORM\Column(name="version", type="integer", nullable=false)
     *
     * @version
     */
    private $version = '1';

    /**
     * Set version.
     *
     * @param int $version
     *
     * @return $this
     */
    public function setVersion($version)
    {
        $this->version = $version;

        return $this;
    }

    /**
     * Get version.
     *
     * @return int
     */
    public function getVersion()
    {
        return $this->version;
    }
}
