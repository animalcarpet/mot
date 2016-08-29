<?php

namespace DvsaEntities\Entity;

use Doctrine\ORM\Mapping as ORM;
use DvsaEntities\EntityTrait\CommonIdentityTrait;

/**
 * Class Survey.
 *
 * @ORM\Table(
 *     name="survey",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(
 *              name="id_UNIQUE",
 *              columns={"id"}
 *         )
 *      }
 * )
 * @ORM\Entity(repositoryClass="DvsaEntities\Repository\SurveyRepository")
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
     * Survey constructor.
     *
     * @param int $rating
     */
    public function __construct($rating)
    {
        $this->rating = $rating;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return sprintf("Survey[id: %d] rating: %d", $this->id, $this->rating);
    }

    /**
     * @return int
     */
    public function getRating()
    {
        return $this->rating;
    }
}
