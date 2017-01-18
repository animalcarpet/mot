<?php
/**
 * This file is part of the DVSA MOT API project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace DvsaEntities\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class MotTestReasonForRejectionComment
 *
 * @ORM\Entity
 * @ORM\Table(
 *     name="mot_test_rfr_map_comment",
 *     options={"collate"="utf8_general_ci", "charset"="utf8", "engine"="InnoDB"}
 * )
 */
class MotTestReasonForRejectionComment extends Entity
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="comment", type="string", length=255)
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
     * @return MotTestReasonForRejectionComment
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @param string $comment
     * @return MotTestReasonForRejectionComment
     */
    public function setComment($comment)
    {
        $this->comment = $comment;
        return $this;
    }
}
