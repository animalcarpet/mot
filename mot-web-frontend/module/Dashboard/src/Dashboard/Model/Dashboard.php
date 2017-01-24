<?php

namespace Dashboard\Model;

use DvsaCommon\Domain\MotTestType;
use DvsaCommon\Enum\OrganisationBusinessRoleCode;
use DvsaCommon\Enum\SiteBusinessRoleCode;
use DvsaCommon\Utility\ArrayUtils;

/**
 * Stores all data that can be displayed on dashboard
 */
class Dashboard
{
    const DISPLAY_VTS_LIST = 'vts-list';
    const DISPLAY_DVSA_ADMIN_BOX = 'dvsa-admin-box';
    const DISPLAY_TESTER_STATS_BOX = 'tester-stats-box';
    const DISPLAY_TESTER_CONTINGENCY_BOX = 'tester-contingency-box';

    /** @var $displayRole string */
    private $hero;

    /** @var $authorisedExaminers AuthorisedExaminer[] */
    private $authorisedExaminers;

    /** @var $specialNotice SpecialNotice */
    private $specialNotice;

    /** @var $overdueSpecialNotices array */
    private $overdueSpecialNotices;

    /** @var $notifications array */
    private $notifications;

    /** @var $unreadNotificationsCount integer */
    private $unreadNotificationsCount;

    /** @var  $inProgressTestId integer */
    private $inProgressTestNumber;

    /** @var $inProgressDemoTestNumber string */
    private $inProgressDemoTestNumber;

    /** @var $inProgressNonMotTestNumber integer */
    private $inProgressNonMotTestNumber;

    /** @var  $inProgressTestTypeCode string */
    private $inProgressTestTypeCode;

    public function __construct($data)
    {
        $this->setHero(ArrayUtils::get($data, 'hero'));
        $this->setAuthorisedExaminers(AuthorisedExaminer::getList(ArrayUtils::get($data, 'authorisedExaminers')));
        $this->setSpecialNotice(new SpecialNotice(ArrayUtils::get($data, 'specialNotice')));
        $this->setOverdueSpecialNotices(ArrayUtils::get($data, 'overdueSpecialNotices'));
        $this->setNotifications(Notification::createList(ArrayUtils::get($data, 'notifications')));
        $this->setUnreadNotificationsCount(ArrayUtils::get($data, 'unreadNotificationsCount'));
        $this->setInProgressTestNumber(ArrayUtils::get($data, 'inProgressTestNumber'));
        $this->setInProgressTestTypeCode(ArrayUtils::get($data, 'inProgressTestTypeCode'));
        $this->setInProgressDemoTestNumber(ArrayUtils::tryGet($data, 'inProgressDemoTestNumber'));
        $this->setInProgressNonMotTestNumber(ArrayUtils::tryGet($data, 'inProgressNonMotTestNumber'));
    }

    /**
     * @return int
     */
    public function getOverallSlotCount()
    {
        $slots = 0;

        if (count($this->getAuthorisedExaminers())) {
            /** @var $ae AuthorisedExaminer */
            foreach ($this->getAuthorisedExaminers() as $ae) {
                $slots += $ae->getSlots();
            }
        }

        return $slots;
    }

    /**
     * @return int
     */
    public function getOverallSiteCount()
    {
        $vtsNumber = 0;

        if (count($this->getAuthorisedExaminers())) {
            /** @var $ae AuthorisedExaminer */
            foreach ($this->getAuthorisedExaminers() as $ae) {
                $vtsNumber += $ae->getSiteCount();
            }
        }

        return $vtsNumber;
    }

    /**
     * @return int
     */
    public function getOverallAuthoriseExaminerCount()
    {
        return is_array($this->getAuthorisedExaminers()) ? count($this->getAuthorisedExaminers()) : 0;
    }

    /**
     * Is the person employed as a tester at any site in any AE
     *
     * @return bool
     */
    public function isTesterAtAnySite()
    {
        foreach ($this->getAuthorisedExaminers() as $ae) {
            foreach ($ae->getSites() as $site) {
                $isTesterAtSite = in_array(SiteBusinessRoleCode::TESTER, $site->getPositions());
                if ($isTesterAtSite) {
                    return true;
                }
            }
        }
        return false;
    }

    public function isAedm()
    {
        foreach ($this->getAuthorisedExaminers() as $ae) {
            if (OrganisationBusinessRoleCode::AUTHORISED_EXAMINER_DESIGNATED_MANAGER === $ae->getPosition()) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param bool $piece
     *
     * @return bool
     *
     * @deprecated
     * @throws \LogicException
     */
    public function canDisplay($piece)
    {
        if ($this->getPermissions() && in_array($piece, array_keys($this->getPermissions()))) {
            return (bool)$this->getPermissions()[$piece];
        }
        throw new \LogicException('Display permission ' . $piece . ' does not exist');
    }

    /**
     * @param \Dashboard\Model\AuthorisedExaminer[] $authorisedExaminers
     *
     * @return Dashboard
     */
    public function setAuthorisedExaminers($authorisedExaminers)
    {
        $this->authorisedExaminers = $authorisedExaminers;
        return $this;
    }

    /**
     * @return \Dashboard\Model\AuthorisedExaminer[]
     */
    public function getAuthorisedExaminers()
    {
        return $this->authorisedExaminers;
    }

    /**
     * @param string $displayRole
     *
     * @return Dashboard
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
     * @param array $permissions
     *
     * @return Dashboard
     */
    public function setPermissions($permissions)
    {
        $this->permissions = $permissions;
        return $this;
    }

    /**
     * @return array
     */
    public function getPermissions()
    {
        return isset($this->permissions) ? $this->permissions : [];
    }

    /**
     * @param \Dashboard\Model\SpecialNotice $specialNotice
     *
     * @return Dashboard
     */
    public function setSpecialNotice($specialNotice)
    {
        $this->specialNotice = $specialNotice;
        return $this;
    }

    /**
     * @return \Dashboard\Model\SpecialNotice
     */
    public function getSpecialNotice()
    {
        return $this->specialNotice;
    }

    /**
     * @param array $overdueSpecialNotices
     * @return Dashboard
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
     * @param array $notifications
     *
     * @return Dashboard
     */
    public function setNotifications($notifications)
    {
        $this->notifications = $notifications;
        return $this;
    }

    /**
     * @return array
     */
    public function getNotifications()
    {
        return $this->notifications;
    }

    /**
     * @param $inProgressTestNumber
     *
     * @return $this
     */
    public function setInProgressTestNumber($inProgressTestNumber)
    {
        $this->inProgressTestNumber = $inProgressTestNumber;
        return $this;
    }

    /**
     * @return integer|null
     */
    public function getInProgressTestNumber()
    {
        return $this->inProgressTestNumber;
    }

    /**
     * @return string
     */
    public function getInProgressTestTypeCode()
    {
        return $this->inProgressTestTypeCode;
    }

    /**
     * @return bool
     */
    public function hasTestInProgress()
    {
        return null !== $this->inProgressTestNumber;
    }

    /**
     * @param $inProgressTestNumber
     *
     * @return $this
     */
    public function setInProgressDemoTestNumber($inProgressTestNumber)
    {
        $this->inProgressDemoTestNumber = $inProgressTestNumber;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getInProgressDemoTestNumber()
    {
        return $this->inProgressDemoTestNumber;
    }

    /**
     * @return bool
     */
    public function hasDemoTestInProgress()
    {
        return null !== $this->inProgressDemoTestNumber;
    }

    /**
     * @param $inProgressTestNumber
     *
     * @return $this
     */
    public function setInProgressNonMotTestNumber($inProgressTestNumber)
    {
        $this->inProgressNonMotTestNumber = $inProgressTestNumber;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getInProgressNonMotTestNumber()
    {
        return $this->inProgressNonMotTestNumber;
    }

    /**
     * @return bool
     */
    public function hasNonMotTestInProgress()
    {
        return null !== $this->inProgressNonMotTestNumber;
    }

    /**
     * @param string $inProgressTestTypeCode
     * @return $this
     */
    public function setInProgressTestTypeCode($inProgressTestTypeCode)
    {
        $this->inProgressTestTypeCode = $inProgressTestTypeCode;
        return $this;
    }

    /**
     * @return bool
     */
    public function isInProgressTestARetest()
    {
        return MotTestType::isRetest($this->inProgressTestTypeCode);
    }

    /**
     * @return string
     */
    public function getEnterTestResultsLabel()
    {
        if($this->isInProgressTestARetest()) {
            return "Enter retest results";
        } else {
            return "Enter test results";
        }
    }

    /**
     * @return integer
     */
    public function getUnreadNotificationsCount()
    {
        return $this->unreadNotificationsCount;
    }

    /**
     * @param integer $count
     */
    private function setUnreadNotificationsCount($count)
    {
        $this->unreadNotificationsCount = $count;
    }
}
