<?php

namespace DvsaEntities\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use DvsaEntities\EntityTrait\CommonIdentityTrait;

/**
 * SpecialNoticeContent.
 *
 * @ORM\Table(
 *  name="special_notice_content",
 *  uniqueConstraints={@ORM\UniqueConstraint(name="issue_number", columns={"issue_number", "issue_year"})}
 * )
 * @ORM\Entity
 */
class SpecialNoticeContent extends Entity
{
    use CommonIdentityTrait;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=100, nullable=false)
     */
    private $title;

    /**
     * @var int
     *
     * @ORM\Column(name="issue_number", type="integer", nullable=false)
     */
    private $issueNumber;

    /**
     * @var int
     *
     * @ORM\Column(name="issue_year", type="integer", nullable=false)
     */
    private $issueYear;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="issue_date", type="datetime", nullable=false)
     */
    private $issueDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="expiry_date", type="datetime", nullable=false)
     */
    private $expiryDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="internal_publish_date", type="datetime", nullable=false)
     */
    private $internalPublishDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="external_publish_date", type="datetime", nullable=false)
     */
    private $externalPublishDate;

    /**
     * @var string
     *
     * @ORM\Column(name="notice_text", type="text", nullable=false)
     */
    private $noticeText;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_published", type="boolean", nullable=false)
     */
    private $isPublished = '0';
    /**
     * @var bool
     *
     * @ORM\Column(name="is_deleted", type="boolean", nullable=false)
     */
    private $isDeleted = '0';

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\OneToMany(targetEntity="DvsaEntities\Entity\SpecialNoticeAudience", mappedBy="content", cascade={"persist"})
     */
    private $audience;

    public function __construct()
    {
        $this->audience = new ArrayCollection();
    }

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
     * Set title.
     *
     * @param string $title
     *
     * @return SpecialNoticeContent
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set noticeText.
     *
     * @param string $noticeText
     *
     * @return SpecialNoticeContent
     */
    public function setNoticeText($noticeText)
    {
        $this->noticeText = $noticeText;

        return $this;
    }

    /**
     * Get noticeText.
     *
     * @return string
     */
    public function getNoticeText()
    {
        return $this->noticeText;
    }

    /**
     * @param bool $isPublished
     *
     * @return $this
     */
    public function setIsPublished($isPublished)
    {
        $this->isPublished = $isPublished;

        return $this;
    }

    /**
     * @return bool
     */
    public function isPublished()
    {
        return $this->isPublished;
    }

    /**
     * Set issueDate.
     *
     * @param \DateTime $issueDate
     *
     * @return SpecialNoticeContent
     */
    public function setIssueDate($issueDate)
    {
        $this->issueDate = $issueDate;

        return $this;
    }

    /**
     * Get issueDate.
     *
     * @return \DateTime
     */
    public function getIssueDate()
    {
        return $this->issueDate;
    }

    /**
     * Set issueNumber.
     *
     * @param int $issueNumber
     *
     * @return SpecialNoticeContent
     */
    public function setIssueNumber($issueNumber)
    {
        $this->issueNumber = $issueNumber;

        return $this;
    }

    /**
     * Get issueNumber.
     *
     * @return int
     */
    public function getIssueNumber()
    {
        return $this->issueNumber;
    }

    /**
     * Set issueYear.
     *
     * @param int $issueYear
     *
     * @return SpecialNoticeContent
     */
    public function setIssueYear($issueYear)
    {
        $this->issueYear = $issueYear;

        return $this;
    }

    /**
     * Get issueYear.
     *
     * @return int
     */
    public function getIssueYear()
    {
        return $this->issueYear;
    }

    /**
     * Set expiryDate.
     *
     * @param \DateTime $expiryDate
     *
     * @return SpecialNoticeContent
     */
    public function setExpiryDate($expiryDate)
    {
        $this->expiryDate = $expiryDate;

        return $this;
    }

    /**
     * Get expiryDate.
     *
     * @return \DateTime
     */
    public function getExpiryDate()
    {
        return $this->expiryDate;
    }

    /**
     * Set externalPublishDate.
     *
     * @param \DateTime $externalPublishDate
     *
     * @return SpecialNoticeContent
     */
    public function setExternalPublishDate($externalPublishDate)
    {
        $this->externalPublishDate = $externalPublishDate;

        return $this;
    }

    /**
     * Get externalPublishDate.
     *
     * @return \DateTime
     */
    public function getExternalPublishDate()
    {
        return $this->externalPublishDate;
    }

    /**
     * Set internalPublishDate.
     *
     * @param \DateTime $internalPublishDate
     *
     * @return SpecialNoticeContent
     */
    public function setInternalPublishDate($internalPublishDate)
    {
        $this->internalPublishDate = $internalPublishDate;

        return $this;
    }

    /**
     * Get internalPublishDate.
     *
     * @return \DateTime
     */
    public function getInternalPublishDate()
    {
        return $this->internalPublishDate;
    }

    /**
     * @param SpecialNoticeAudience $sna
     *
     * @return SpecialNoticeContent
     */
    public function addSpecialNoticeAudience(SpecialNoticeAudience $sna)
    {
        $sna->setContent($this);

        $this->audience->add($sna);

        return $this;
    }

    public function clearSpecialNoticeAudience()
    {
        $this->audience->clear();
    }

    /**
     * @return SpecialNoticeAudience[]
     */
    public function getAudience()
    {
        return $this->audience;
    }
}
