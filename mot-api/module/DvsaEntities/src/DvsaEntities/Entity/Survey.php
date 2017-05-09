<?php
/**
 * This file is part of the DVSA MOT API project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace DvsaEntities\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use DvsaEntities\EntityTrait\CommonIdentityTrait;

/**
 * Class Survey.
 *
 * @ORM\Table(name="survey", uniqueConstraints={@ORM\UniqueConstraint(name="id_UNIQUE", columns={"id"})})
 * @ORM\Entity()
 */
class Survey
{
    use CommonIdentityTrait;

    /**
     * @var int
     *
     * @ORM\Column(name="satisfaction_rating", type="integer", nullable=true)
     */
    private $rating;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="created_on", type="date", nullable=false)
     */
    private $createdOn;

    /**
     * Survey constructor.
     *
     * @param int $rating
     */
    public function __construct($rating)
    {
        $this->rating = $rating;
        $this->createdOn = new DateTime();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return sprintf('Survey[id: %s] rating: %d', $this->getId() ?: 'null', $this->rating);
    }

    /**
     * @return int
     */
    public function getRating()
    {
        return $this->rating;
    }

    /**
     * @return DateTime
     */
    public function getCreatedOn()
    {
        return $this->createdOn;
    }
}
