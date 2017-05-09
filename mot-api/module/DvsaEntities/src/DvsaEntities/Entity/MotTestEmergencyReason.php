<?php
/**
 * This file is part of the DVSA MOT API project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace DvsaEntities\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(
 *  name="mot_test_emergency_reason",
 *     options={
 *         "collate"="utf8_general_ci",
 *         "charset"="utf8",
 *         "engine"="InnoDB"
 *     }
 * )
 */
class MotTestEmergencyReason extends Entity
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity="EmergencyLog")
     * @ORM\JoinColumn(name="emergency_log_id", referencedColumnName="id")
     *
     * @var EmergencyLog
     */
    private $emergencyLog;

    /**
     * @ORM\OneToOne(targetEntity="EmergencyReason")
     * @ORM\JoinColumn(name="emergency_reason_lookup_id", referencedColumnName="id")
     *
     * @var EmergencyReason
     */
    private $emergencyReason;

    /**
     * @ORM\OneToOne(targetEntity="Comment", cascade={"persist"})
     * @ORM\JoinColumn(name="emergency_reason_comment_id", referencedColumnName="id")
     *
     * @var Comment
     */
    private $comment;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return MotTestEmergencyReason
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return EmergencyLog
     */
    public function getEmergencyLog()
    {
        return $this->emergencyLog;
    }

    /**
     * @param EmergencyLog $emergencyLog
     *
     * @return MotTestEmergencyReason
     */
    public function setEmergencyLog(EmergencyLog $emergencyLog)
    {
        $this->emergencyLog = $emergencyLog;

        return $this;
    }

    /**
     * @return EmergencyReason
     */
    public function getEmergencyReason()
    {
        return $this->emergencyReason;
    }

    /**
     * @param EmergencyReason $emergencyReason
     *
     * @return $this
     */
    public function setEmergencyReason(EmergencyReason $emergencyReason)
    {
        $this->emergencyReason = $emergencyReason;

        return $this;
    }

    /**
     * @return Comment
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @param Comment $comment
     *
     * @return $this
     */
    public function setComment(Comment $comment)
    {
        $this->comment = $comment;

        return $this;
    }
}
