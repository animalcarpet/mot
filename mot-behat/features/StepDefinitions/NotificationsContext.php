<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Dvsa\Mot\Behat\Support\Api\Session;
use Dvsa\Mot\Behat\Support\Api\Notification;
use Dvsa\Mot\Behat\Support\Api\Vts;
use Dvsa\Mot\Behat\Support\Api\AuthorisedExaminer;
use PHPUnit_Framework_Assert as PHPUnit;
use Dvsa\Mot\Behat\Support\Helper\TestSupportHelper;

class NotificationsContext implements Context
{
    const TEMPLATE_TESTER_QUALIFICATION_STATUS = 14;
    const TEMPLATE_DVSA_ASSIGN_ROLE = 16;
    const TEMPLATE_DVSA_REMOVE_ROLE = 17;

    private $templateMap = [
        "Tester Qualification Status" => self::TEMPLATE_TESTER_QUALIFICATION_STATUS,
        "DVSA Assign Role" => self::TEMPLATE_DVSA_ASSIGN_ROLE,
        "DVSA Remove Role" => self::TEMPLATE_DVSA_REMOVE_ROLE
    ];

    /**
     * @var Notification
     */
    private $notification;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var Vts
     */
    private $vts;

    /**
     * @var AuthorisedExaminer
     */
    private $ae;

    /**
     * @var TestSupportHelper
     */
    private $testSupportHelper;

    /**
     * @var PersonContext
     */
    private $personContext;

    /**
     * @var SessionContext
     */
    private $sessionContext;

    /**
     * @var array
     */
    private $userNotification = [];

    /**
     * @var VtsContext
     */
    private $vtsContext;

    /**
     * @var AuthorisedExaminerContext
     */
    private $aeContext;

    public function __construct(
        Notification $notification,
        Session $session,
        Vts $vts,
        AuthorisedExaminer $ae,
        TestSupportHelper $testSupportHelper
    )
    {
        $this->notification = $notification;
        $this->session = $session;
        $this->vts = $vts;
        $this->ae = $ae;
        $this->testSupportHelper = $testSupportHelper;
    }

    /**
     * @BeforeScenario
     */
    public function gatherContexts(BeforeScenarioScope $scope)
    {
        $this->personContext = $scope->getEnvironment()->getContext(PersonContext::class);
        $this->sessionContext = $scope->getEnvironment()->getContext(SessionContext::class);
        $this->vtsContext = $scope->getEnvironment()->getContext(VtsContext::class);
        $this->aeContext = $scope->getEnvironment()->getContext(AuthorisedExaminerContext::class);
    }

    /**
     * @Then the user will receive a :template notification
     */
    public function theUserWillReceiveANotification($template)
    {
        $session = $this->session->startSession(
            $this->personContext->getPersonUsername(),
            $this->personContext->getPersonPassword()
        );

        $response = $this->notification->fetchNotificationForPerson($session->getAccessToken(), $session->getUserId());
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
     * @Then the user will NOT receive a status change notification
     */
    public function theUserWillNotReceiveAStatusChangeNotification()
    {
        $session = $this->session->startSession(
            $this->personContext->getPersonUsername(),
            $this->personContext->getPersonPassword()
        );

        $response = $this->notification->fetchNotificationForPerson($session->getAccessToken(), $session->getUserId());
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
}
