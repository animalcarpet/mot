<?php

namespace DvsaEntities\Entity;

use Doctrine\ORM\Mapping as ORM;
use DvsaEntities\EntityTrait\CommonIdentityTrait;

/**
 * Class SurveyResult
 * @package DvsaEntities\Entity
 * 
 * @ORM\Table(
 *     name="survey_result",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(
 *              name="id_UNIQUE",
 *              columns={"id"}
 *         )
 *      }
 *   )
 * @ORM\Entity(repositoryClass="DvsaEntities\Repository\SurveyResultRepository")
 *
 */
class SurveyResult extends Entity
{
    use CommonIdentityTrait;

    /**
     * The satisfaction rating submitted by the user.
     * 
     * @var string
     *
     * @ORM\Column(name="satisfaction_rating", type="integer", length=35, nullable=true)
     */
    private $satisfactionRating;

    /**
     * @return int
     */
    public function getSatisfactionRating()
    {
        return $this->satisfactionRating;
    }

    /**
     * @param int $satisfactionRating
     */
    public function setSatisfactionRating($satisfactionRating)
    {
        $this->satisfactionRating = $satisfactionRating;
    }
}