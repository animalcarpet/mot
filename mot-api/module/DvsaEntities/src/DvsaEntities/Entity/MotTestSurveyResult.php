<?php


namespace DvsaEntities\Entity;

use Doctrine\ORM\Mapping as ORM;
use DvsaEntities\EntityTrait\CommonIdentityTrait;

/**
 * Class MotTestSurveyResult
 * @package DvsaEntities\Entity
 *
 * @ORM\Table(
 *     name="mot_test_survey_result",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(
 *              name="id_UNIQUE",
 *              columns={"id"}
 *         )
 *      }
 * )
 * @ORM\Entity(repositoryClass="DvsaEntities\Repository\Doctrine\DoctrineMotTestSurveyResultRepository")
 */
class MotTestSurveyResult extends Entity
{
    use CommonIdentityTrait;

    /**
     * The MOT test data associated with the survey
     *
     * @var MotTest
     *
     * @ORM\ManyToOne(targetEntity="DvsaEntities\Entity\MotTest")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="mot_test_id", referencedColumnName="id")
     * })
     */
    private $motTest;

    /**
     * The survey results
     *
     * @var int|null
     *
     * @ORM\Column(name="satisfaction_rating", type="integer", length=1, nullable=true)
     */
    private $surveyResult;

    /**
     * @return MotTest
     */
    public function getMotTest()
    {
        return $this->motTest;
    }

    /**
     * @return int
     */
    public function getSurveyResult()
    {
        return $this->surveyResult;
    }

    /**
     * @param MotTest $motTest
     */
    public function setMotTest($motTest)
    {
        $this->motTest = $motTest;
    }

    /**
     * @param int|null $surveyResult
     */
    public function setSurveyResult($surveyResult)
    {
        $this->surveyResult = $surveyResult;
    }
}
