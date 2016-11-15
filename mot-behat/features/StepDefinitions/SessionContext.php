<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Dvsa\Mot\Behat\Datasource\Authentication;
use Dvsa\Mot\Behat\Support\Api\AccountClaim;
use Dvsa\Mot\Behat\Support\Api\Session;
use Dvsa\Mot\Behat\Support\Api\Session\AuthenticatedUser;
use Dvsa\Mot\Behat\Support\Api\AuthorisedExaminer;
use Dvsa\Mot\Behat\Support\Helper\TestSupportHelper;
use Dvsa\Mot\Behat\Support\Data\Params\PersonParams;
use Dvsa\Mot\Behat\Support\Data\Params\SiteParams;
use Dvsa\Mot\Behat\Support\Data\UserData;
use Dvsa\Mot\Behat\Support\Data\SiteData;
use Dvsa\Mot\Behat\Support\Data\AuthorisedExaminerData;
use DvsaCommon\Dto\Site\SiteDto;

class SessionContext implements Context
{
    /**
     * @var TestSupportHelper
     */
    public $testSupportHelper;

    /**
     * @var AccountClaim
     */
    private $accountClaim;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var AuthorisedExaminer
     */
    private $authorisedExaminer;

    /**
     * @var AuthenticatedUser
     */
    private $currentUser = null;

    /**
     * @var AccountClaimContext
     */
    private $accountClaimContext;

    /**
     * @var TempPasswordChangeContext
     */
    private $tempPasswordChangeContext;

    /**
     * @var VtsContext
     */
    private $vtsContext;

    /**
     * @var AuthorisedExaminerContext
     */
    private $authorisedExaminerContext;

    /** @var PersonContext */
    private $personContext;

    private $userData;
    private $siteData;
    private $authorisedExaminerData;

    /**
     * @param AccountClaim $accountClaim
     * @param Session $session
     * @param TestSupportHelper $testSupportHelper
     * @param AuthorisedExaminer $authorisedExaminer
     */
    public function __construct(
        AccountClaim $accountClaim,
        Session $session,
        TestSupportHelper $testSupportHelper,
        AuthorisedExaminer $authorisedExaminer,
        UserData $userData,
        SiteData $siteData,
        AuthorisedExaminerData $authorisedExaminerData
    ) {
        $this->accountClaim      = $accountClaim;
        $this->session           = $session;
        $this->testSupportHelper = $testSupportHelper;
        $this->authorisedExaminer = $authorisedExaminer;
        $this->userData = $userData;
        $this->siteData = $siteData;
        $this->authorisedExaminerData = $authorisedExaminerData;
    }

    /**
     * @BeforeScenario
     */
    public function gatherContexts(BeforeScenarioScope $scope)
    {
        $this->accountClaimContext       = $scope->getEnvironment()->getContext(AccountClaimContext::class);
        $this->tempPasswordChangeContext = $scope->getEnvironment()->getContext(TempPasswordChangeContext::class);
        $this->vtsContext = $scope->getEnvironment()->getContext(VtsContext::class);
        $this->authorisedExaminerContext = $scope->getEnvironment()->getContext(AuthorisedExaminerContext::class);
        $this->personContext = $scope->getEnvironment()->getContext(PersonContext::class);
    }

    /**
     * @Given I am registered as a new user
     * @Given I am logged in as a new User
     */
    public function iAmRegisteredAsANewUser()
    {
        $this->currentUser = $this->userData->createUser();
        $this->userData->setCurrentLoggedUser($this->currentUser);
    }

    /**
     * @Given I am logged in as a Tester
     * @Given I am logged in as a Tester at site :name
     */
    public function iAmLoggedInAsATester($name = SiteData::DEFAULT_NAME)
    {
        $site = $this->siteData->tryGet($name);
        if ($site === null) {
            $site = $this->siteData->create($name);
        }

        $this->currentUser = $this->userData->createTesterAssignedWitSite($site->getId());
        $this->userData->setCurrentLoggedUser($this->currentUser);
    }

    /**
     * @param array $siteIds
     */
    public function iAmLoggedInAsATesterAssignedToSites($siteIds)
    {
        $this->currentUser = $this->session->logInAsTester($this->testSupportHelper, $siteIds);
        $this->userData->setCurrentLoggedUser($this->currentUser);
    }

    /**
     * @Given I am logged in as a Special Notice broadcast user
     */
    public function iAmLoggedInAsASpecialNoticeBroadcastUser()
    {
        $username = Authentication::LOGIN_CRON_JOB_USER;
        $password = Authentication::PASSWORD_DEFAULT;

        $this->currentUser = $this->session->startSession($username, $password);
        $this->userData->setCurrentLoggedUser($this->currentUser);
    }

    /**
     * @Given /^I am logged in as an? Scheme User$/
     */
    public function iAmLoggedInAsASchemeUser()
    {
        $this->currentUser  = $this->userData->createSchemeUser();
        $this->userData->setCurrentLoggedUser($this->currentUser);
    }

    /**
     * @Given /^I am not logged in$/
     * @Given I am an unregistered user
     */
    public function iAmNotLoggedIn()
    {
        $this->currentUser = null;
        $this->userData->setCurrentLoggedUser(null);
    }

    /**
     * @Given /^I am logged in as an? Area Office User 2$/
     */
    public function iAmLoggedInAsAnAreaOfficeUser2()
    {
        $this->currentUser  = $this->userData->createAreaOffice2User();
        $this->userData->setCurrentLoggedUser($this->currentUser);
    }

    /**
     * @Given /^I am logged in as an? Cron User$/
     */
    public function iAmLoggedInAsAnCronUser()
    {
        $this->currentUser  = $this->userData->createCronUser();
        $this->userData->setCurrentLoggedUser($this->currentUser);
    }

    /**
     * @Given I am logged in as a Finance User
     */
    public function iAmLoggedInAsAFinanceUser()
    {
        $this->currentUser  = $this->userData->createFinanceUser();
        $this->userData->setCurrentLoggedUser($this->currentUser);
    }

    /**
     * @Given /^I'm authenticated with my username and password (.*) (.*)$/
     *
     * @param $username
     * @param $password
     */
    public function iMAuthenticatedWithMyUsernameAndPassword($username, $password)
    {
        //use generic password if $password is "default"
        if (strcasecmp($password, 'DEFAULT') == 0) {
            $password = Authentication::PASSWORD_DEFAULT;
        }

        $this->currentUser = $this->session->startSession($username, $password);
        $this->userData->setCurrentLoggedUser($this->currentUser);
    }

    /**
     * @Given I log in as a VM10519User
     * @Given I am logged in as a VM10519User
     */
    public function iAmLoggedInAsAVM10519User()
    {
        $sveService = $this->testSupportHelper->getVM10519UserService();
        $user                   = $sveService->create([]);
        $this->currentUser      = $this->session->startSession(
            $user->data[PersonParams::USERNAME],
            $user->data[PersonParams::PASSWORD]
        );

        $this->userData->setCurrentLoggedUser($this->currentUser);
    }

    /**
     * @Given I log in as a Vehicle Examiner
     * @Given /^I am logged in as an? Vehicle Examiner$/
     */
    public function iAmLoggedInAsAVehicleExaminer()
    {
        $this->currentUser = $this->userData->createVehicleExaminer();
        $this->userData->setCurrentLoggedUser($this->currentUser);
    }

    /**
     * @Given /^I am logged in as a DVLA Operative$/
     */
    public function iAmLoggedInAsADVLAOperative()
    {
        $this->currentUser = $this->userData->createDVLAOperative();
        $this->userData->setCurrentLoggedUser($this->currentUser);
    }

    /**
     * @Given /^I am logged in as an? Customer Service Operator$/
     */
    public function iAmLoggedInAsACustomerServiceOperator()
    {
        $this->currentUser = $this->userData->createCustomerServiceOperator();
        $this->userData->setCurrentLoggedUser($this->currentUser);
    }

    /**
     * @Given I am logged in as a GVTSTester
     */
    public function iAmLoggedInAsAnGVTSTester()
    {
        $this->currentUser = $this->userData->createGVTSTester();
        $this->userData->setCurrentLoggedUser($this->currentUser);
    }

    /**
     * @Given I am logged in as a Customer Service Manager
     */
    public function iAmLoggedInAsACustomerServiceManager()
    {
        $this->currentUser = $this->userData->createCustomerServiceManager();
        $this->userData->setCurrentLoggedUser($this->currentUser);
    }

    /**
     * @Given /^I am logged in as an? DVLA Manager$/
     */
    public function iAmLoggedInAsADVLAManager()
    {
        $this->currentUser = $this->userData->createDVLAManager();
        $this->userData->setCurrentLoggedUser($this->currentUser);
    }

    /**
     * @Given I am logged in as an Area Office 1
     * @Given I am logged in as a Area Office 1
     * @Given I am logged in as a Area Office User
     * @Given I am logged in as an Area Office User
     */
    public function iAmLoggedInAsAnAreaOffice1()
    {
        $this->currentUser = $this->userData->createAreaOffice1User();
        $this->userData->setCurrentLoggedUser($this->currentUser);
    }

    /**
     * @Given I am logged in as an Area Office User to new site
     */
    public function iAmLoggedInAsAnAreaOfficeUserToNewSite()
    {
        $this->siteData->create("Auto Moto");
        $this->iAmLoggedInAsAnAreaOffice1();
    }

    /**
     * @Given I am logged in as a Scheme Manager
     */
    public function iAmLoggedInAsASchemeManager()
    {
        $this->currentUser = $this->userData->createSchemeManager();
        $this->userData->setCurrentLoggedUser($this->currentUser);
    }

    /**
     * @Given I am logged in as a Tester with an unclaimed account
     */
    public function iAmLoggedInAsATesterWithAnUnclaimedAccount()
    {
        $params = [
            PersonParams::ACCOUNT_CLAIM_REQUIRED => true,
            PersonParams::SITE_IDS => [$this->siteData->get()->getId()],
        ];

        $this->currentUser = $this->userData->createTesterWithParams($params, "Tester With Unclaimed Account");
        $this->userData->setCurrentLoggedUser($this->currentUser);
    }

    /**
     * @Given I am logged in as a Tester with a Temp Password
     */
    public function iAmLoggedInAsATesterWithATempPassword()
    {
        $params = [
            PersonParams::PASSWORD_CHANGE_REQUIRED => true,
            PersonParams::SITE_IDS => [$this->siteData->get()->getId()],
        ];

        $this->currentUser = $this->userData->createTesterWithParams($params, "Tester With Temp Password");
        $this->userData->setCurrentLoggedUser($this->currentUser);
    }

    /**
     * @return AuthenticatedUser
     */
    public function getCurrentUser()
    {
        if (null === $this->currentUser) {
            throw new \BadMethodCallException('Current user is not available. Have you forgotten to authenticate?');
        }

        return $this->currentUser;
    }

    /**
     * @return string
     */
    public function getCurrentAccessToken()
    {
        return $this->getCurrentUser()->getAccessToken();
    }

    /**
     * @return string
     */
    public function getCurrentUserId()
    {
        return $this->getCurrentUser()->getUserId();
    }

    /**
     * @return null|string
     */
    public function getCurrentAccessTokenOrNull()
    {
        try {
            return $this->getCurrentAccessToken();
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * @return null|string
     */
    public function getCurrentUserIdOrNull()
    {
        try {
            return $this->getCurrentUserId();
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * @Given /^I am logged in as an? Authorised Examiner$/
     * @Given I am logged in as an AEDM
     * @Given I am logged in as an AEDM of :aeName
     */
    public function iAmLoggedInAsAnAedmOf($aeName = AuthorisedExaminerData::DEFAULT_NAME)
    {
        $ae = $this->authorisedExaminerData->create($aeName);
        $aedm = $this->userData->getAedmByAeId($ae->getId());
        $this->userData->setCurrentLoggedUser($aedm);
        $this->currentUser = $aedm;
    }

    /**
     * @Given I am logged in as an Area Office User 2 to new site
     */
    public function iAmLoggedInAsAnAreaOfficeUser2ToNewSite()
    {
        $this->siteData->create("V-Tech UK");
        $this->iAmLoggedInAsAnAreaOfficeUser2();
    }

    /**
     * @Given I am logged in as a Site Manager to new site
     */
    public function iAmLoggedInAsASiteManagerToNewSite()
    {
        $site = $this->siteData->create("Best Garage");
        $this->currentUser  = $this->userData->createSiteManager($site->getId());
        $this->userData->setCurrentLoggedUser($this->currentUser);
    }

    /**
     * @Given I am logged in as a Site Manager at :site site
     */
    public function iAmLoggedInAsASiteManager(SiteDto $site)
    {
        $this->currentUser  = $this->userData->createSiteManager($site->getId());
        $this->userData->setCurrentLoggedUser($this->currentUser);
    }

    /**
     * @Given I am logged in as an Aedm to new organisation
     */
    public function iAmLoggedInAsAnAedmToNewOrganisation()
    {
        $ae = $this->authorisedExaminerData->create("Best Company Ltd");
        $this->currentUser = $this->userData->getAedmByAeId($ae->getId());
        $this->userData->setCurrentLoggedUser($this->currentUser);
    }

    /**
     * @Given /^I am logged in as user with (.*)$/
     */
    public function iAmLoggedInAsUserWith($role)
    {
        switch ($role) {
            case "tester":
                $this->iAmLoggedInAsATester();
                break;
            case "siteManager":
                $this->iAmLoggedInAsASiteManagerToNewSite();
                break;
            case "siteAdmin":
                $this->iAmLoggedInAsASiteAdmin();
                break;
            case "aedm":
                $this->iAmLoggedInAsAnAedmToNewOrganisation();
                break;
            case "vehicleExaminer":
                $this->iAmLoggedInAsAVehicleExaminer();
                break;
            case "areaOffice":
                $this->iAmLoggedInAsAnAreaOffice1();
                break;
            case "csco":
                $this->iAmLoggedInAsACustomerServiceOperator();
                break;
            case "schememgt":
                $this->iAmLoggedInAsASchemeManager();
                break;
            case "schemeuser":
                $this->iAmLoggedInAsASchemeUser();
                break;
            case "dvlaOper":
                $this->iAmLoggedInAsADVLAOperative();
                break;
            default:
                throw new InvalidArgumentException;
        }

    }

    public function iAmLoggedInAsASiteAdmin()
    {
        $siteId = $this->siteData->get()->getId();
        $this->currentUser  = $this->userData->createSiteAdmin($siteId);
        $this->userData->setCurrentLoggedUser($this->currentUser);
    }
}
