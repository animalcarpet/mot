<?php
/**
 * This file is part of the DVSA MOT API project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace DvsaEntities\Entity;

use Doctrine\ORM\Mapping as ORM;
use DvsaEntities\EntityTrait\CommonIdentityTrait;
use DvsaMotApi\Domain\Survey\SurveyToken;

/**
 * Class MotTestSurvey.
 *
 * @ORM\Table(name="mot_test_survey", uniqueConstraints={@ORM\UniqueConstraint(name="token_UNIQUE", columns={"token"})})
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
     * @ORM\JoinColumn(name="mot_test_id", referencedColumnName="id")
     */
    private $motTest;

    /**
     * @var bool
     *
     * @ORM\Column(name="has_been_presented", type="boolean", nullable=false, options={"default":false})
     */
    private $hasBeenPresented = false;

    /**
     * @var bool
     *
     * @ORM\Column(name="has_been_submitted", type="boolean", nullable=false, options={"default":false})
     */
    private $hasBeenSubmitted = false;

    /**
     * MotTestSurvey constructor.
     *
     * @param MotTest $motTest
     */
    public function __construct(MotTest $motTest)
    {
        $this->token = (new SurveyToken())->toString();
        $this->motTest = $motTest;
    }

    /**
     * @return MotTest
     */
    public function getMotTest()
    {
        return $this->motTest;
    }

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param bool $hasBeenPresented
     */
    public function setHasBeenPresented($hasBeenPresented)
    {
        $this->hasBeenPresented = (bool) $hasBeenPresented;
    }

    /**
     * @return bool
     */
    public function hasBeenPresented()
    {
        return (bool) $this->hasBeenPresented;
    }

    /**
     * @param bool $hasBeenSubmitted
     */
    public function setHasBeenSubmitted($hasBeenSubmitted)
    {
        $this->hasBeenSubmitted = (bool) $hasBeenSubmitted;
    }

    /**
     * @return bool
     */
    public function hasBeenSubmitted()
    {
        return (bool) $this->hasBeenSubmitted;
    }
}
