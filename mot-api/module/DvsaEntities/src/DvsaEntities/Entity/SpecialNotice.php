<?php

namespace DvsaEntities\Entity;

use Doctrine\ORM\Mapping as ORM;
use DvsaEntities\EntityTrait\CommonIdentityTrait;

/**
 * Special Notice.
 *
 * @ORM\Table(name="special_notice", options={"collate"="utf8_general_ci", "charset"="utf8", "engine"="InnoDB"})
 * @ORM\Entity(repositoryClass="DvsaEntities\Repository\SpecialNoticeRepository")
 */
class SpecialNotice extends Entity
{
    use CommonIdentityTrait;

    /**
     * @var string
     *
     * @ORM\Column(name="username", type="string", length=100, nullable=false)
     */
    private $username;

    /**
     * @var \DvsaEntities\Entity\SpecialNoticeContent
     *
     * @ORM\ManyToOne(targetEntity="DvsaEntities\Entity\SpecialNoticeContent")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="special_notice_content_id", referencedColumnName="id")
     * })
     */
    private $content;

    /**
     * @var int
     *
     * @ORM\Column(name="special_notice_content_id", type="integer")
     */
    private $contentId;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_acknowledged", type="boolean", nullable=false)
     */
    private $isAcknowledged = false;

    /**
     * @var \DateTime
     * @ORM\Column(name="acknowledged_on", type="datetime", nullable=true)
     */
    private $acknowledgedOn;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_deleted", type="boolean", nullable=false)
     */
    private $isDeleted = false;

    /**
     * @param bool $isDeleted
     *
     * @return $this
     * @codeCoverageIgnore
     */
    public function setIsDeleted($isDeleted)
    {
        $this->isDeleted = $isDeleted;

        return $this;
    }

    /**
     * @return bool
     * @codeCoverageIgnore
     */
    public function getIsDeleted()
    {
        return $this->isDeleted;
    }

    /**
     * @param string $username
     *
     * @return SpecialNotice
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param \DvsaEntities\Entity\SpecialNoticeContent $content
     *
     * @return SpecialNotice
     */
    public function setContent($content = null)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * @return \DvsaEntities\Entity\SpecialNoticeContent
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @return bool
     */
    public function getIsAcknowledged()
    {
        return $this->isAcknowledged;
    }

    /**
     * @return int
     */
    public function getContentId()
    {
        return $this->contentId;
    }

    /**
     * @param int $contentId
     */
    public function setContentId($contentId)
    {
        $this->contentId = $contentId;

        return $this;
    }

    public function markAcknowledged()
    {
        $this->isAcknowledged = true;
        $this->acknowledgedOn = new \DateTime();

        return $this;
    }
}
