<?php

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Dvsa\Mot\Behat\Support\Api\Notification;
use Dvsa\Mot\Behat\Support\Api\Person;
use Dvsa\Mot\Behat\Support\Api\Session\AuthenticatedUser;
use Dvsa\Mot\Behat\Support\Data\AuthorisedExaminerData;
use Dvsa\Mot\Behat\Support\Data\Params\AuthorisedExaminerParams;
use Dvsa\Mot\Behat\Support\Data\Params\PersonParams;
use Dvsa\Mot\Behat\Support\Data\Params\RoleParams;
use Dvsa\Mot\Behat\Support\Data\Params\SiteParams;
use Dvsa\Mot\Behat\Support\Api\Vts;
use Dvsa\Mot\Behat\Support\Data\SiteData;
use Dvsa\Mot\Behat\Support\Data\UserData;
use Zend\Stdlib\ArrayUtils;
use PHPUnit_Framework_Assert as PHPUnit;


class TQINotificationContext implements Context
{
    const TEMPLATE_TQI_STATS_GENERATED_INFORMATION_FOR_AEDM_AND_AED = 35;
    const TEMPLATE_TQI_STATS_GENERATED_INFORMATION_FOR_SITE_MANAGER = 36;

    const TQI_NOTIFICATION_TEMPLATES = [
        "Site TQI stats generated" => self::TEMPLATE_TQI_STATS_GENERATED_INFORMATION_FOR_SITE_MANAGER,
        "Organisation TQI stats generated" => self::TEMPLATE_TQI_STATS_GENERATED_INFORMATION_FOR_AEDM_AND_AED
    ];

    private $userData;
    private $siteData;
    private $authorisedExaminerData;
    private $notificationApi;

    public function __construct(
        UserData $userData,
        SiteData $siteData,
        AuthorisedExaminerData $authorisedExaminerData,
        Notification $notification
    )
    {
        $this->userData = $userData;
        $this->siteData = $siteData;
        $this->authorisedExaminerData = $authorisedExaminerData;
        $this->notificationApi = $notification;
    }

    /**
     * @Given there is AED assigned to the organisation with following data:
     */
    public function thereIsAAEDAssignedToTheOrganisationWithFollowingData(TableNode $table)
    {
        $rows = $table->getColumnsHash();
        foreach ($rows as $row)
        {
            $aeId = $this->authorisedExaminerData->get($row[AuthorisedExaminerParams::AE_NAME])->getId();
            $this->userData->createAedmAssignedWithOrganisation($aeId, $row[PersonParams::USERNAME]);
        }
    }

    /**
     * @Given All TQI notifications was sent
     */
    public function sendTQINotifications()
    {
        $cronUserAccessToken = $this->userData->createCronUser()->getAccessToken();
        $this->notificationApi->sendAEDMTqiNotifications($cronUserAccessToken);
        $this->notificationApi->sendSiteManagerTqiNotifications($cronUserAccessToken);
    }

    /**
     * @Given user Site Manager on site:
     */
    public function userIsSiteManagerOnSite(TableNode $table)
    {
        $rows = $table->getColumnsHash();
        foreach ($rows as $row)
        {
            $site = $this->siteData->get($row[SiteParams::SITE_NAME]);
            $user = $this->userData->tryGet($row[PersonParams::USERNAME]);
            if (empty($user)) {
                $user = $this->userData->createUser($row[PersonParams::USERNAME]);
            }

            $this->userData->addSiteRoleToUser($user->getUserId(), $site->getId(), 'Site manager');
        }
    }

    /**
     * @Then being log in as a person i should receive notification that TQI statistics was generated for my site with following data:
     */
    public function iAmLoggedInAsAPersonIShouldReceiveNotificationThatTQIStatisticsForMySiteWasGenerated(TableNode $table)
    {
        $rows = $table->getColumnsHash();
        foreach ($rows as $row)
        {
            $user = $this->userData->get($row[PersonParams::USERNAME]);

            $notifications = $this->fetchAndFilterNotifications($user);

            PHPUnit::assertCount(1, $notifications);
            PHPUnit::assertEquals(self::TQI_NOTIFICATION_TEMPLATES[$row['template']], $notifications[0]['templateId']);
            PHPUnit::assertEquals($row[SiteParams::SITE_NAME], $notifications[0]['fields']['siteName']);
        }
    }

    /**
     * @Then being log in as a Site Manager with few sites i should receive correct number of notifications
     */
    public function siteManagerReceiveCorrectNumberOfNotifications(TableNode $table)
    {
        $rows = $table->getColumnsHash();
        foreach ($rows as $row)
        {
            $user = $this->userData->get($row[PersonParams::USERNAME]);

            $notifications = $this->fetchAndFilterNotifications($user);

            PHPUnit::assertCount((int)$row['notificationCount'], $notifications);
        }
    }

    /**
     * @Then Authorised Examiner Designated Manager received notifications for his site:
     */
    public function AEDMReceivedNotifications(TableNode $table)
    {
        $rows = $table->getColumnsHash();
        foreach ($rows as $row)
        {
            $site = $this->siteData->get($row[SiteParams::SITE_NAME]);
            $aedm = $this->userData->getAedmByAeId($site->getOrganisation()->getId());

            $notifications = $this->fetchAndFilterNotifications($aedm);

            PHPUnit::assertCount(1, $notifications);
            PHPUnit::assertEquals(self::TEMPLATE_TQI_STATS_GENERATED_INFORMATION_FOR_AEDM_AND_AED, $notifications[0]['templateId']);
            PHPUnit::assertEquals($site->getOrganisation()->getName(), $notifications[0]['fields']['orgName']);
        }
    }

    private function fetchAndFilterNotifications(AuthenticatedUser $user)
    {
        $response = $this->notificationApi->fetchNotificationForPerson($user->getAccessToken(), $user->getUserId());
        $notifications = $response->getBody()->toArray();

        $notifications = ArrayUtils::filter($notifications['data'], function($notification) {
            return ArrayUtils::inArray($notification['templateId'], self::TQI_NOTIFICATION_TEMPLATES);
        });

        return $notifications;
    }

    /**
     * @Given there is a Site Manager assigned to the site with following data:
     */
    public function thereIsASiteManagerAssignedToTheSiteWithFollowingData(TableNode $table)
    {
        $rows = $table->getColumnsHash();
        foreach ($rows as $row)
        {
            $siteId = $this->siteData->get($row[SiteParams::SITE_NAME])->getId();
            $this->userData->createSiteManager($siteId, $row[PersonParams::USERNAME]);
        }
    }

    /**
     * @Given AEDM is also a user with role in his site:
     */
    public function AEDMIsAlsoAUserWithRoleInHisSite(TableNode $table)
    {
        $rows = $table->getColumnsHash();
        foreach ($rows as $row)
        {
            $site = $this->siteData->get($row[SiteParams::SITE_NAME]);
            $aedm = $this->userData->getAedmByAeId($site->getOrganisation()->getId());

            $this->userData->addSiteRoleToUser($aedm->getUserId(), $site->getId(), $row[RoleParams::ROLE_NAME]);
        }
    }
}
