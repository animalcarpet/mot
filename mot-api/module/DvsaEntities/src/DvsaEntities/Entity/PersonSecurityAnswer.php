<?php

namespace DvsaEntities\Entity;

use Doctrine\ORM\Mapping as ORM;
use DvsaEntities\EntityTrait\CommonIdentityTrait;

/**
 * PersonSecurityQuestionMap.
 *
 * @ORM\Table(name="person_security_question_map", options={"collate"="utf8_general_ci", "charset"="utf8", "engine"="InnoDB"})
 * @ORM\Entity(repositoryClass="DvsaEntities\Repository\PersonSecurityAnswerRepository")
 */
class PersonSecurityAnswer extends Entity
{
    use CommonIdentityTrait;

    /**
     * @var Person
     *
     * @ORM\ManyToOne(targetEntity="\DvsaEntities\Entity\Person", fetch="LAZY", inversedBy="personSecurityAnswers")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="person_id", referencedColumnName="id")
     * })
     */
    private $person;

    /**
     * @var SecurityQuestion
     *
     * @ORM\ManyToOne(targetEntity="\DvsaEntities\Entity\SecurityQuestion", fetch="LAZY")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="security_question_id", referencedColumnName="id")
     * })
     */
    private $securityQuestion;

    /**
     * @var string
     *
     * @ORM\Column(name="answer", type="string", length=80, nullable=false)
     */
    private $answer;

    public function __construct(SecurityQuestion $securityQuestion, Person $person, $answer)
    {
        $this->person = $person;
        $this->securityQuestion = $securityQuestion;
        $this->answer = $answer;
    }

    public function getPerson()
    {
        return $this->person;
    }

    public function getSecurityQuestion()
    {
        return $this->securityQuestion;
    }

    public function getAnswer()
    {
        return $this->answer;
    }
}
