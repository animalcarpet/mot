<?php

namespace DvsaEntities\Entity;

use Doctrine\ORM\Mapping as ORM;
use DvsaEntities\EntityTrait\CommonIdentityTrait;

/**
 * @ORM\Entity
 * @ORM\Table(
 *  name="enforcement_mot_demo_test",
 *  options= {
 *      "collate"="utf8_general_ci",
 *      "charset"="utf8",
 *      "engine"="InnoDB"
 * })
 */
class EnforcementMotDemoTest extends Entity
{
    use CommonIdentityTrait;

    /**
     * @var int
     *
     * @ORM\Column(name="mot_test_id", type="integer", nullable=false)
     */
    private $motTestId;

    /**
     * @var int
     *
     * @ORM\Column (name="is_satisfactory", type="boolean", nullable=false)
     */
    private $isSatisfactory = false;

    /**
     * @var \DvsaEntities\Entity\Comment
     *
     * @ORM\OneToOne(targetEntity="DvsaEntities\Entity\Comment", cascade={"PERSIST"})
     * @ORM\JoinColumn(name="comment_id", referencedColumnName="id", nullable=true)
     */
    private $comment;

    //  ----    Base Mot Test functions   --
    /**
     * @param \DvsaEntities\Entity\MotTest $motTest
     *
     * @return EnforcementMotDemoTest
     */
    public function setMotTestId($motTest)
    {
        $this->motTestId = $motTest;

        return $this;
    }

    /**
     * @return \DvsaEntities\Entity\MotTest
     */
    public function getMotTestId()
    {
        return $this->motTestId;
    }

    //  ----    Test Result functions   --
    /**
     * @param bool $satisfactory
     *
     * @return EnforcementMotDemoTest
     */
    public function setIsSatisfactory($satisfactory)
    {
        $this->isSatisfactory = $satisfactory;

        return $this;
    }

    /**
     * @return int
     */
    public function getIsSatisfactory()
    {
        return $this->isSatisfactory;
    }

    /**
     * @param \DvsaEntities\Entity\Comment $comment comment
     *
     * @return EnforcementMotDemoTest
     */
    public function setComment($comment)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * @return \DvsaEntities\Entity\Comment
     */
    public function getComment()
    {
        return $this->comment;
    }
}
