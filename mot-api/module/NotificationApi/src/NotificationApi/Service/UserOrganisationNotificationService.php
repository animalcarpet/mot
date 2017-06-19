<?php

namespace NotificationApi\Service;

use DvsaCommon\Enum\BusinessRoleStatusCode;
use DvsaCommon\Enum\RoleCode;
use DvsaEntities\Entity\AuthorisationForAuthorisedExaminer;
use DvsaEntities\Entity\OrganisationBusinessRoleMap;
use DvsaEntities\Entity\Person;
use DvsaEntities\Entity\Site;
use DvsaEntities\Entity\SiteBusinessRoleMap;
use NotificationApi\Dto\Notification;
use Zend\Stdlib\ArrayUtils;

class UserOrganisationNotificationService
{
    /**
     * If there is no person at site/organisation that can recieve the notification, here is the mapping of roles
     * that can be used instead.
     *
     * @var array
     */
    public static $notifyIfOriginalRecieverRoleIsNotPresent = [
        RoleCode::SITE_MANAGER => RoleCode::AUTHORISED_EXAMINER_DESIGNATED_MANAGER,
    ];

    public static $notifyRolesForSiteAssessmentManualCreation = [
        RoleCode::AUTHORISED_EXAMINER_DELEGATE,
        RoleCode::AUTHORISED_EXAMINER_DESIGNATED_MANAGER,
        RoleCode::SITE_MANAGER,
        RoleCode::SITE_ADMIN
    ];

    /**
     * @var NotificationService
     */
    protected $notificationService;

    /**
     * @var PositionRemovalNotificationService
     */
    protected $positionRemovalNotificationService;

    /**
     * UserOrganisationNotificationService constructor.
     *
     * @param NotificationService                $notificationService
     * @param PositionRemovalNotificationService $positionRemovalNotificationService
     */
    public function __construct(
        NotificationService $notificationService,
        PositionRemovalNotificationService $positionRemovalNotificationService
    ) {
        $this->notificationService = $notificationService;
        $this->positionRemovalNotificationService = $positionRemovalNotificationService;
    }

    /**
     * Sends a notification to the user, about removing one of his roles at organisation.
     *
     * @param OrganisationBusinessRoleMap $map
     */
    public function sendNotificationToUserAboutOrganisationRoleRemoval(OrganisationBusinessRoleMap $map)
    {
        $removalNotification = (new Notification())->setTemplate(Notification::TEMPLATE_ORGANISATION_POSITION_REMOVED)
            ->setRecipient($map->getPerson()->getId())
            ->addField('positionName', $map->getOrganisationBusinessRole()->getFullName())
            ->addField('organisationName', $map->getOrganisation()->getName())
            ->addField('siteOrOrganisationId', $map->getOrganisation()->getAuthorisedExaminer()->getNumber())
            ->toArray();

        $this->notificationService->add($removalNotification);
    }

    /**
     * Sends a notification to the user, about removing one of his roles at site.
     *
     * @param SiteBusinessRoleMap $roleMap
     */
    public function sendNotificationToUserAboutSiteRoleRemoval(SiteBusinessRoleMap $roleMap)
    {
        $siteId = $roleMap->getSite()->getId();
        $contactText = $this->positionRemovalNotificationService->getSiteRoleRemovalContactText($siteId);

        $removalNotification = (new Notification())->setTemplate(Notification::TEMPLATE_SITE_POSITION_REMOVED)
            ->setRecipient($roleMap->getPerson()->getId())
            ->addField('positionName', $roleMap->getSiteBusinessRole()->getName())
            ->addField('siteName', $roleMap->getSite()->getName())
            ->addField('siteOrOrganisationId', $roleMap->getSite()->getSiteNumber())
            ->addField('contactText', $contactText)
            ->toArray();

        $this->notificationService->add($removalNotification);
    }

    /**
     * @param $recipentId
     * @param $userName
     * @param $displayName
     * @param $role
     * @param $orgOrSiteName
     * @param $orgOrSiteNumber
     */
    protected function createAndSendNotification($recipentId, $userName, $displayName, $role, $orgOrSiteName, $orgOrSiteNumber)
    {
        $removalNotification = (new Notification())->setTemplate(Notification::TEMPLATE_USER_REMOVED_OWN_ROLE)
            ->setRecipient($recipentId)
            ->addField('userName', $userName)
            ->addField('nameSurname', $displayName)
            ->addField('role', $role)
            ->addField('orgOrSiteName', $orgOrSiteName)
            ->addField('orgOrSiteNumber', $orgOrSiteNumber)
            ->toArray();

        $this->notificationService->add($removalNotification);
    }

    /**
     * Sends a notification to users organisation, when user disassociates his own role.
     *
     * @param Person                      $person
     * @param OrganisationBusinessRoleMap $organisationBusinessRoleMap
     */
    public function sendNotificationToOrganisationAboutRoleRemoval(Person $person, OrganisationBusinessRoleMap $organisationBusinessRoleMap)
    {
        $ae = $organisationBusinessRoleMap->getOrganisation()->getAuthorisedExaminer();
        $this->createAndSendNotification(
            $person->getId(),
            $organisationBusinessRoleMap->getPerson()->getUsername(),
            $organisationBusinessRoleMap->getPerson()->getDisplayName(),
            $organisationBusinessRoleMap->getOrganisationBusinessRole()->getRole()->getName(),
            $organisationBusinessRoleMap->getOrganisation()->getName(),
            is_null($ae) ? '' : $ae->getNumber()
        );
    }

    /**
     * @param Person              $recipient
     * @param SiteBusinessRoleMap $siteBusinessRoleMap
     */
    public function sendNotificationToSiteAboutRoleRemoval(Person $recipient, SiteBusinessRoleMap $siteBusinessRoleMap)
    {
        $this->createAndSendNotification(
            $recipient->getId(),
            $siteBusinessRoleMap->getPerson()->getUsername(),
            $siteBusinessRoleMap->getPerson()->getDisplayName(),
            $siteBusinessRoleMap->getSiteBusinessRole()->getName(),
            $siteBusinessRoleMap->getSite()->getName(),
            $siteBusinessRoleMap->getSite()->getSiteNumber()
        );
    }

    /**
     * @param OrganisationBusinessRoleMap $position
     *
     * @throws NotFoundException
     */
    public function notifyOrganisationAboutRoleRemoval(OrganisationBusinessRoleMap $position)
    {
        $recipient = $this->getPersonToSendNotificationTo(
            $position->getOrganisation()->getPositions(),
            $position->getOrganisationBusinessRole()->getRole()->getCode(),
            $position->getOrganisation()->getAuthorisedExaminer()
        );
        if (!is_null($recipient)) {
            $this->sendNotificationToOrganisationAboutRoleRemoval($recipient, $position);
        }
    }

    /**
     * @param SiteBusinessRoleMap $position
     *
     * @throws NotFoundException
     */
    public function notifySiteAboutRoleRemoval(SiteBusinessRoleMap $position)
    {
        $recipient = $this->getPersonToSendNotificationTo(
            $position->getSite()->getPositions(),
            $position->getSiteBusinessRole()->getCode(),
            $position->getSite()->getAuthorisedExaminer()
        );
        if (!is_null($recipient)) {
            $this->sendNotificationToSiteAboutRoleRemoval($recipient, $position);
        }
    }

    /**
     * @param $siteName
     * @param $siteNumber
     * @param SiteBusinessRoleMap[]|null $siteBusinessRoleMap
     * @param OrganisationBusinessRoleMap[]|null $organisationBusinessRoleMap
     */
    public function sendNotificationToUsersAboutSiteAssessmentCreate($siteName, $siteNumber, $siteBusinessRoleMap, $organisationBusinessRoleMap)
    {
        $persons = $this->getAllPersonsWithRoles(self::$notifyRolesForSiteAssessmentManualCreation, $siteBusinessRoleMap, $organisationBusinessRoleMap);

        if ($persons) {
            foreach ($persons as $person) {
                $siteAssessmentNotification = (new Notification())->setTemplate(Notification::TEMPLATE_SITE_ASSESSMENT_CREATED)
                    ->setRecipient($person->getId())
                    ->addField('siteNumber', $siteNumber)
                    ->addField('siteName', $siteName)
                    ->toArray();

                $this->notificationService->add($siteAssessmentNotification);
            }
        }
    }

    /**
     * @param OrganisationBusinessRoleMap[]|SiteBusinessRoleMap[] $positions
     * @param string                                              $roleCode
     * @param AuthorisationForAuthorisedExaminer                  $authorisedExaminer
     *
     * @return \DvsaEntities\Entity\Person[]
     */
    protected function getPersonToSendNotificationTo($positions, $roleCode, $authorisedExaminer)
    {
        $personToNotifyRoleCode = $this->getPersonsRoleCodeToNotify($roleCode);
        $person = $this->getPersonWithRole($positions, $personToNotifyRoleCode);

        $otherPersonToNotifyRoleCode = $this->checkIfWeCanNotifySomeoneElse($personToNotifyRoleCode);
        if (is_null($person) && !empty($otherPersonToNotifyRoleCode)) {
            $person = $this->getPersonWithRole($positions, $personToNotifyRoleCode);
        }

        if (is_null($person)
            && !is_null($authorisedExaminer)
            && ($this->isPersonToNotifyAuthorisedManager($personToNotifyRoleCode)
            || $this->isPersonToNotifyAuthorisedManager($otherPersonToNotifyRoleCode))
        ) {
            $aedm = $authorisedExaminer->getDesignatedManager();
            if (!is_null($aedm)) {
                $person = $aedm;
            }
        }

        return $person;
    }

    /**
     * Decides what role shoud recieve the notification.
     *
     * @param $roleCode
     *
     * @return array
     */
    protected function getPersonsRoleCodeToNotify($roleCode)
    {
        switch ($roleCode) {
            case RoleCode::AUTHORISED_EXAMINER_DELEGATE:
            case RoleCode::AUTHORISED_EXAMINER_PRINCIPAL:
            case RoleCode::AUTHORISED_EXAMINER:
            case RoleCode::SITE_MANAGER: {
                return RoleCode::AUTHORISED_EXAMINER_DESIGNATED_MANAGER;
            }
            case RoleCode::TESTER:
            case RoleCode::SITE_ADMIN: {
                return RoleCode::SITE_MANAGER;
            }
        }
    }

    /**
     * Iterates over positions in site/organisation returns persons with desired roles.
     *
     * @param OrganisationBusinessRoleMap[]|SiteBusinessRoleMap[] $positions
     * @param $roleName
     *
     * @return Person
     */
    protected function getPersonWithRole($positions, $roleName)
    {
        foreach ($positions as $position) {
            if (($position instanceof OrganisationBusinessRoleMap
                && $position->getOrganisationBusinessRole()->getRole()->getCode() == $roleName)
                || ($position instanceof SiteBusinessRoleMap
                && $position->getSiteBusinessRole()->getCode() == $roleName)) {
                return $position->getPerson();
            }
        }
    }

    /**
     * @param array $rolesNames
     * @param SiteBusinessRoleMap[] $siteBusinessRoleMap
     * @param OrganisationBusinessRoleMap[] $organisationBusinessRoleMap
     * @return Person[]
     */
    protected function getAllPersonsWithRoles($rolesNames, $siteBusinessRoleMap, $organisationBusinessRoleMap)
    {
        $filteredRoles = ArrayUtils::filter(array_merge($siteBusinessRoleMap, $organisationBusinessRoleMap), function ($position) use ($rolesNames) {
            $businessRoleStatusCode = $position instanceof SiteBusinessRoleMap ?
                $position->getSiteBusinessRole()->getCode() :
                $position->getOrganisationBusinessRole()->getRole()->getCode();

            return in_array($businessRoleStatusCode, $rolesNames) &&
                $position->getBusinessRoleStatus()->getCode() == BusinessRoleStatusCode::ACTIVE;
        });

        if ($filteredRoles) {
            $persons = null;
            /** @var OrganisationBusinessRoleMap|SiteBusinessRoleMap $role */
            foreach ($filteredRoles as $role) {
                $persons[] = $role->getPerson();
            }

            return array_unique($persons, SORT_REGULAR);
        }
        return null;
    }

    /**
     * If there are no persons with desired role in organisation/site that can recieve notification,
     * we try to search for other role to forward the notification.
     *
     * @param $roleCode
     *
     * @return string|bool
     */
    protected function checkIfWeCanNotifySomeoneElse($roleCode)
    {
        if (isset(self::$notifyIfOriginalRecieverRoleIsNotPresent[$roleCode])) {
            return self::$notifyIfOriginalRecieverRoleIsNotPresent[$roleCode];
        }

        return false;
    }

    /**
     * Checks if given role code is in fact AUTHORISED_EXAMINER_DESIGNATED_MANAGER.
     *
     * @param string $personToNotifyRoleCode
     *
     * @return bool
     */
    protected function isPersonToNotifyAuthorisedManager($personToNotifyRoleCode)
    {
        return $personToNotifyRoleCode == RoleCode::AUTHORISED_EXAMINER_DESIGNATED_MANAGER;
    }
}
