<?php
/**
 * This file is part of the DVSA MOT API project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace DvsaEntities\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class MotTestCancelled.
 *
 * @ORM\Entity
 * @ORM\Table(
 *     name="mot_test_cancelled",
 *     options={"collate"="utf8_general_ci", "charset"="utf8", "engine"="InnoDB"}
 * )
 */
class MotTestCancelled extends Entity
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     */
    protected $id;

    /**
     * @var MotTestReasonForCancel
     *
     * @ORM\ManyToOne(targetEntity="MotTestReasonForCancel")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="mot_test_reason_for_cancel_id", referencedColumnName="id")
     * })
     */
    private $motTestReasonForCancel;

    /**
     * @var Comment
     *
     * @ORM\ManyToOne(targetEntity="Comment", cascade={"persist"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="reason_for_cancel_comment_id", referencedColumnName="id", nullable=true)
     * })
     */
    private $comment;

    /**
     * @param $id
     *
     * @return $this
     *
     * @throws \Exception
     */
    public function setId($id)
    {
        if (null != $id && !is_numeric($id)) {
            throw new \InvalidArgumentException("Expected numeric id, got [$id]");
        }
        $this->id = (int) $id;

        return $this;
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return MotTestReasonForCancel
     */
    public function getMotTestReasonForCancel()
    {
        return $this->motTestReasonForCancel;
    }

    /**
     * @param MotTestReasonForCancel $motTestReasonForCancel
     *
     * @return MotTestCancelled
     */
    public function setMotTestReasonForCancel(MotTestReasonForCancel $motTestReasonForCancel)
    {
        $this->motTestReasonForCancel = $motTestReasonForCancel;

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
     * @return MotTestCancelled
     */
    public function setComment(Comment $comment)
    {
        $this->comment = $comment;

        return $this;
    }
}
