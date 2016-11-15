<?php

use Behat\Behat\Context\Context;
use Dvsa\Mot\Behat\Support\Api\Session;
use Dvsa\Mot\Behat\Support\Api\Notification;
use Dvsa\Mot\Behat\Support\Api\Session\AuthenticatedUser;
use Dvsa\Mot\Behat\Support\Data\UserData;
use PHPUnit_Framework_Assert as PHPUnit;

class NotificationsContext implements Context
{
    const TEMPLATE_TESTER_QUALIFICATION_STATUS = 14;
    const TEMPLATE_DVSA_ASSIGN_ROLE = 16;
    const TEMPLATE_DVSA_REMOVE_ROLE = 17;
    const TEMPLATE_PERSONAL_DETAILS_CHANGED_NOTIFICATION_ID = 26;

    private $templateMap = [
        "Tester Qualification Status" => self::TEMPLATE_TESTER_QUALIFICATION_STATUS,
        "DVSA Assign Role" => self::TEMPLATE_DVSA_ASSIGN_ROLE,
        "DVSA Remove Role" => self::TEMPLATE_DVSA_REMOVE_ROLE
    ];

    private $notification;
    private $userData;

    /**
     * @var array
     */
    private $userNotification = [];


    public function __construct(Notification $notification, UserData $userData)
    {
        $this->notification = $notification;
        $this->userData = $userData;
    }

    /**
     * @Then :user will receive a :template notification
     */
    public function theUserWillReceiveANotification(AuthenticatedUser $user, $template)
    {
        $response = $this->notification->fetchNotificationForPerson($user->getAccessToken(), $user->getUserId());
        $notifications = $response->getBody()->toArray();

        PHPUnit::assertNotEmpty($notifications);

        // Assert that the notification that we are expecting exists
        $found = false;
        foreach ($notifications['data'] as $notification) {
            if ($notification['templateId'] == $this->getNotificationTemplateId($template)) {
                $this->userNotification = $notification;
                $found = true;
                break;
            }
        }
        PHPUnit::assertTrue($found, 'Notification for template "'. $template .'" not found');
    }

    /**
     * @Then a notification subject contains phrase :phrase
     */
    public function aNotificationSubjectContainsPhrase($phrase)
    {
        PHPUnit::assertContains($phrase, $this->userNotification['subject']);
    }

    /**
     * @Then a notification content contains phrase :phrase
     */
    public function aNotificationContentContainsPhrase($phrase)
    {
        PHPUnit::assertContains($phrase, $this->userNotification['content']);
    }

    /**
     * @Then :user will NOT receive a status change notification
     */
    public function theUserWillNotReceiveAStatusChangeNotification(AuthenticatedUser $user)
    {
        $response = $this->notification->fetchNotificationForPerson($user->getAccessToken(), $user->getUserId());
        $notifications = $response->getBody()->toArray();

        $found = false;
        foreach ($notifications['data'] as $notification) {
            if ($notification['templateId'] == self::TEMPLATE_TESTER_QUALIFICATION_STATUS) {
                $found = true;
                break;
            }
        }

        PHPUnit::assertFalse($found);
    }

    private function getNotificationTemplateId($template)
    {
        if (isset($this->templateMap[$template])) {
            return $this->templateMap[$template];
        }

        throw new \InvalidArgumentException('Template "' . $template . '" not found');
    }

    /**
     * @Then :user should receive a notification about the change
     */
    public function userGetsNotificationAboutChange(AuthenticatedUser $user = null)
    {
        $response = $this->notification->fetchNotificationForPerson($user->getAccessToken(), $user->getUserId());
        $notifications = $response->getBody()->toArray();

        PHPUnit::assertNotEmpty($notifications);

        // Assert that the notification that we are expecting exists
        $found = false;
        foreach ($notifications['data'] as $notification) {
            if ($notification['templateId'] == self::TEMPLATE_PERSONAL_DETAILS_CHANGED_NOTIFICATION_ID) {
                $found = true;
                break;
            }
        }
        PHPUnit::assertTrue($found, 'Notification for personal details being changed not found');
    }

    /**
     * @Then the person should not receive a notification about the change
     */
    public function userDoesNotGetNotificationAboutChange()
    {
        $response = $this->notification->fetchNotificationForPerson(
            $this->userData->getCurrentLoggedUser()->getAccessToken(),
            $this->userData->getCurrentLoggedUser()->getUserId()
        );
        $notifications = $response->getBody()->toArray();

        if (empty($notifications)) {
            PHPUnit::assertEmpty($notifications);
        } else {
            // Assert that there are no changed details notifications
            $found = false;
            foreach ($notifications['data'] as $notification) {
                if ($notification['templateId'] == self::TEMPLATE_PERSONAL_DETAILS_CHANGED_NOTIFICATION_ID) {
                    $found = true;
                    break;
                }
            }
            PHPUnit::assertFalse($found, 'Notification for personal details being changed was found');
        }
    }
}
