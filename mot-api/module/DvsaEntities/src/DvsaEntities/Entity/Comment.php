<?php

namespace DvsaEntities\Entity;

use Doctrine\ORM\Mapping as ORM;
use DvsaEntities\EntityTrait\CommonIdentityTrait;

/**
 * Comment.
 *
 * @ORM\Table(name="comment", options={"collate"="utf8_general_ci", "charset"="utf8", "engine"="InnoDB"})
 * @ORM\Entity
 */
class Comment extends Entity
{
    use CommonIdentityTrait;

    /**
     * @var string
     *
     * @ORM\Column(name="comment", type="string", nullable=false)
     */
    private $comment;

    /**
     * @var FuelType
     *
     * @ORM\ManyToOne(targetEntity="Person")
     * @ORM\JoinColumn(name="author_person_id", referencedColumnName="id")
     */
    private $commentAuthor;

    /**
     * @param Person $commentAuthor
     *
     * @return $this
     * @codeCoverageIgnore
     */
    public function setCommentAuthor(Person $commentAuthor)
    {
        $this->commentAuthor = $commentAuthor;

        return $this;
    }

    /**
     * @return Person
     * @codeCoverageIgnore
     */
    public function getCommentAuthor()
    {
        return $this->commentAuthor;
    }

    /**
     * Get Comment.
     *
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * Set comment.
     *
     * @param string $comment
     *
     * @return Comment
     */
    public function setComment($comment)
    {
        $this->comment = $comment;

        return $this;
    }
}
