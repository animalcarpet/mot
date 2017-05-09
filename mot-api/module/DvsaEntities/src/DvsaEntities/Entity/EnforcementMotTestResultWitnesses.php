<?php

namespace DvsaEntities\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="enforcement_mot_test_result_witnesses", options={"collate"="utf8_general_ci", "charset"="utf8", "engine"="InnoDB"})
 */
class EnforcementMotTestResultWitnesses
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="smallint", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var \DvsaEntities\Entity\EnforcementMotTestResult This is the re-inspection mot test result
     *
     * @ORM\ManyToOne(targetEntity="EnforcementMotTestResult", fetch="EAGER", cascade={"persist"}, inversedBy="enforcementMotTestResultWitnesses")
     * @ORM\JoinColumn(name="enforcement_mot_test_result_id", referencedColumnName="id")
     */
    protected $enforcementMotTestResult;

    /**
     * @var string Name
     *
     * @ORM\Column(type="string")
     */
    protected $name;

    /**
     * @var string Position
     *
     * @ORM\Column(type="string")
     */
    protected $position;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    protected $type;

    /**
     * @return the int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param
     *            $id
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return the EnforcementMotTestResult
     */
    public function getEnforcementMotTestResult()
    {
        return $this->enforcementMotTestResult;
    }

    /**
     * @param EnforcementMotTestResult $enforcementMotTestResult
     *
     * @return $this
     */
    public function setEnforcementMotTestResult(EnforcementMotTestResult $enforcementMotTestResult)
    {
        $enforcementMotTestResult->addEnforcementMotTestResultWitness($this);
        $this->enforcementMotTestResult = $enforcementMotTestResult;

        return $this;
    }

    /**
     * @return the string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param $name
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @param $position
     *
     * @return $this
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * @param string $type
     *
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }
}
