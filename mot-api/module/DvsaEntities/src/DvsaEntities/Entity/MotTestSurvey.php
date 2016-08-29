<?php
/**
 * This file is part of the DVSA MOT API project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace DvsaEntities\Entity;

use Doctrine\ORM\Mapping as ORM;
use DvsaEntities\EntityTrait\CommonIdentityTrait;
use Ramsey\Uuid\Uuid;

/**
 * Class MotTestSurvey.
 *
 * @ORM\Table(
 *     name="mot_test_survey",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(
 *              name="token_UNIQUE",
 *              columns={"token"}
 *         )
 *      }
 * )
 * @ORM\Entity(repositoryClass="DvsaEntities\Repository\Doctrine\DoctrineMotTestSurveyRepository")
 */
class MotTestSurvey extends Entity
{
    use CommonIdentityTrait;

    /**
     * Unique token linking each survey to it's MOT test.
     *
     * @var string
     *
     * @ORM\Column(name="token", type="string", length=36, nullable=false, unique=true)
     */
    private $token;

    /**
     * The MOT test data associated with the survey.
     *
     * @var MotTest
     *
     * @ORM\ManyToOne(targetEntity="DvsaEntities\Entity\MotTest", cascade={"persist"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="mot_test_id", referencedColumnName="id")
     * })
     */
    private $motTest;

    /**
     * The survey results.
     *
     * @var Survey|null
     *
     * @ORM\ManyToOne(targetEntity="DvsaEntities\Entity\Survey", cascade={"persist"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="survey_id", referencedColumnName="id")
     * })
     */
    private $survey;

    /**
     * MotTestSurvey constructor.
     *
     * @param MotTest $motTest
     */
    public function __construct(MotTest $motTest)
    {
        $this->token = Uuid::uuid1()->toString();
        $this->motTest = $motTest;
        $this->survey = null;
    }

    /**
     * @return MotTest
     */
    public function getMotTest()
    {
        return $this->motTest;
    }

    /**
     * @return Survey
     */
    public function getSurvey()
    {
        return $this->survey;
    }

    /**
     * @param Survey $survey
     */
    public function setSurvey(Survey $survey)
    {
        $this->survey = $survey;
    }

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }
}
