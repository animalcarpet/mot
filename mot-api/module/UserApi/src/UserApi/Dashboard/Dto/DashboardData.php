<?php

namespace UserApi\Dashboard\Dto;

use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaEntities\Entity\Notification;
use NotificationApi\Mapper\NotificationMapper;

/**
 * All data for dashboard
 */
class DashboardData
{
    /** @var $displayRole string */
    private $hero;

    /** @var $authorisedExaminers AuthorisationForAuthorisedExaminer[] */
    private $authorisedExaminers;

    /** @var $specialNotice array */
    private $specialNotice;

    /** @var $overdueSpecialNotices array */
    private $overdueSpecialNotices;

    /** @var $notifications Notification[] */
    private $notifications;

    /** @var  $inProgressTestNumber integer */
    private $inProgressTestNumber;

    /** @var string */
    private $inProgressDemoTestNumber;

    /** @var int */
    private $inProgressNonMotTestNumber;

    /** @var  $inProgressTestTypeCode string */
    private $inProgressTestTypeCode;

    /** @var $authorisationService MotAuthorisationServiceInterface */
    private $authorisationService;

    /**
     * @param AuthorisationForAuthorisedExaminer[] $authorisedExaminers
     * @param                                      $specialNotice
     * @param                                      $overdueSpecialNotices
     * @param Notification[]                       $notifications
     * @param                                      $inProgressTestNumber
     * @param                                      $inProgressDemoTestNumber
     * @param                                      $inProgressNonMotTestNumber
     * @param                                      $isTesterQualified
     * @param                                      $isTesterActive
     * @param                                      $inProgressTestTypeCode
     * @param MotAuthorisationServiceInterface     $authorisationService
     */
    public function __construct(
        $authorisedExaminers,
        $specialNotice,
        $overdueSpecialNotices,
        $notifications,
        $inProgressTestNumber,
        $inProgressDemoTestNumber,
        $inProgressNonMotTestNumber,
        $isTesterQualified,
        $isTesterActive,
        $inProgressTestTypeCode,
        MotAuthorisationServiceInterface $authorisationService
    ) {
        $this->setAuthorisedExaminers($authorisedExaminers);
        $this->setSpecialNotice(new SpecialNotice($specialNotice));
        $this->setOverdueSpecialNotices($overdueSpecialNotices);
        $this->setNotifications($notifications);
        $this->setInProgressTestNumber($inProgressTestNumber);
        $this->setInProgressDemoTestNumber($inProgressDemoTestNumber);
        $this->setInProgressNonMotTestNumber($inProgressNonMotTestNumber);
        $this->setInProgressTestTypeCode($inProgressTestTypeCode);
        $this->authorisationService = $authorisationService;
        $this->setHero($this->authorisationService->getHero());
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $authorisedExaminers = [];
        /** @var $ae AuthorisationForAuthorisedExaminer */
        foreach ($this->getAuthorisedExaminers() as $ae) {
            $authorisedExaminers[] = $ae->toArray();
        }

        $notificationMapper = new NotificationMapper();
        $notificationExtractedList = [];
        /** @var $notification \DvsaEntities\Entity\Notification */
        foreach ($this->getNotifications() as $notification) {
            $notificationExtractedList[] = $notificationMapper->toArray($notification);
        }

        return [
            'hero'                   => $this->getHero(),
            'authorisedExaminers'    => $authorisedExaminers,
            'specialNotice'          => $this->getSpecialNotice()->toArray(),
            'overdueSpecialNotices'  => $this->overdueSpecialNotices,
            'notifications'          => $notificationExtractedList,
            'inProgressTestNumber'   => $this->inProgressTestNumber,
            'inProgressTestTypeCode' => $this->inProgressTestTypeCode,
            'inProgressDemoTestNumber' => $this->inProgressDemoTestNumber,
            'inProgressNonMotTestNumber' => $this->inProgressNonMotTestNumber
        ];
    }

    /**
     * @param AuthorisationForAuthorisedExaminer[] $authorisedExaminers
     *
     * @return DashboardData
     */
    public function setAuthorisedExaminers($authorisedExaminers)
    {
        $this->authorisedExaminers = $authorisedExaminers;

        return $this;
    }

    /**
     * @return AuthorisationForAuthorisedExaminer[]
     */
    public function getAuthorisedExaminers()
    {
        return $this->authorisedExaminers;
    }

    /**
     * @param string $displayRole
     *
     * @return DashboardData
     */
    public function setHero($displayRole)
    {
        $this->hero = $displayRole;

        return $this;
    }

    /**
     * @return string
     */
    public function getHero()
    {
        return $this->hero;
    }

    /**
     * @param SpecialNotice $specialNotice
     *
     * @return DashboardData
     */
    public function setSpecialNotice($specialNotice)
    {
        $this->specialNotice = $specialNotice;

        return $this;
    }

    /**
     * @return SpecialNotice
     */
    public function getSpecialNotice()
    {
        return $this->specialNotice;
    }

    /**
     * @param array $overdueSpecialNotices
     * @return DashboardData
     */
    public function setOverdueSpecialNotices(array $overdueSpecialNotices)
    {
        $this->overdueSpecialNotices = $overdueSpecialNotices;

        return $this;
    }

    /**
     * @return array
     */
    public function getOverdueSpecialNotices()
    {
        return $this->overdueSpecialNotices;
    }

    /**
     * @param Notification[] $notifications
     *
     * @return DashboardData
     */
    public function setNotifications($notifications)
    {
        $this->notifications = $notifications;

        return $this;
    }

    /**
     * @return Notification[]
     */
    public function getNotifications()
    {
        return $this->notifications;
    }

    /**
     * @param integer $inProgressTestNumber
     *
     * @return DashboardData
     */
    public function setInProgressTestNumber($inProgressTestNumber)
    {
        $this->inProgressTestNumber = $inProgressTestNumber;

        return $this;
    }

    /**
     * @return integer
     */
    public function getInProgressTestNumber()
    {
        return $this->inProgressTestNumber;
    }

    /**
     * @param string $testNumber
     * @return $this
     */
    public function setInProgressDemoTestNumber($testNumber)
    {
        $this->inProgressDemoTestNumber = $testNumber;

        return $this;
    }

    /**
     * @return string
     */
    public function getInProgressDemoTestNumber()
    {
        return $this->inProgressDemoTestNumber;
    }

    /**
     * @return int
     */
    public function getInProgressNonMotTestNumber()
    {
        return $this->inProgressNonMotTestNumber;
    }

    /**
     * @param int $inProgressNonMotTestNumber
     * @return $this
     */
    public function setInProgressNonMotTestNumber($inProgressNonMotTestNumber)
    {
        $this->inProgressNonMotTestNumber = $inProgressNonMotTestNumber;

        return $this;
    }

    /**
     * @param string $inProgressTestTypeCode
     */
    public function setInProgressTestTypeCode($inProgressTestTypeCode)
    {
        $this->inProgressTestTypeCode = $inProgressTestTypeCode;
    }

    /**
     * @return string
     */
    public function getInProgressTestTypeCode()
    {
        return $this->inProgressTestTypeCode;
    }
}
