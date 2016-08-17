<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Gherkin\Node\TableNode;
use Dvsa\Mot\Behat\Support\Api\AuthorisedExaminer;
use Dvsa\Mot\Behat\Support\Api\Notification;
use Dvsa\Mot\Behat\Support\Api\Session;
use Dvsa\Mot\Behat\Support\Api\Vts;
use Dvsa\Mot\Behat\Support\Data\AuthorisedExaminerData;
use Dvsa\Mot\Behat\Support\Data\SiteData;
use Dvsa\Mot\Behat\Support\Data\UserData;
use Dvsa\Mot\Behat\Support\Helper\TestSupportHelper;
use DvsaCommon\Dto\Site\SiteListDto;
use DvsaCommon\Dto\Site\VehicleTestingStationDto;
use DvsaCommon\Dto\Site\SiteDto;
use DvsaCommon\Dto\Organisation\OrganisationDto;
use DvsaCommon\Enum\VehicleClassId;
use DvsaCommon\Model\VehicleTestingStation;
use DvsaCommon\Utility\DtoHydrator;
use PHPUnit_Framework_Assert as PHPUnit;

class VtsContext implements Context
{
    const SITE_NAME = 'Behat Garage MOT';
    const SITE_TOWN = 'Toulouse';
    const SITE_POSTCODE = 'BS1 3LL';
    const SITE_TOWN_NO_GARAGE = 'Paris';

    /**
     * @var Vts
     */
    private $vehicleTestingStation;

    /**
     * @var TestSupportHelper
     */
    private $testSupportHelper;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var Notification
     */
    private $notification;

    /**
     * @var AuthorisedExaminer
     */
    private $authorisedExaminer;

    /** @var \SessionContext */
    private $sessionContext;

    /** @var array */
    private $createdVts = [];

    private $resultContext;

    /** @var PersonContext */
    private $personContext;

    /** @var AuthorisedExaminerContext */
    private $authorisedExaminerContext;

    private $siteManager1Data = null;
    private $siteManager2Data = null;

    private $riskAssessmentData = [];

    private $siteData;

    private $userData;

    /**
     * @param Vts $vehicleTestingStation
     */
    public function __construct(
        Vts $vehicleTestingStation,
        TestSupportHelper $testSupportHelper,
        Session $session,
        Notification $notification,
        AuthorisedExaminer $authorisedExaminer,
        SiteData $siteData,
        UserData $userData
    )
    {
        $this->vehicleTestingStation = $vehicleTestingStation;
        $this->testSupportHelper = $testSupportHelper;
        $this->session = $session;
        $this->notification = $notification;
        $this->authorisedExaminer = $authorisedExaminer;
        $this->siteData = $siteData;
        $this->userData = $userData;
    }

    /**
     * @BeforeScenario
     */
    public function gatherContexts(BeforeScenarioScope $scope)
    {
        $this->sessionContext = $scope->getEnvironment()->getContext(\SessionContext::class);
        $this->personContext = $scope->getEnvironment()->getContext(PersonContext::class);
        $this->authorisedExaminerContext = $scope->getEnvironment()->getContext(AuthorisedExaminerContext::class);
    }

    /**
     * @BeforeScenario @create-site
     * BeforeScenario can't handle argument, so I extract this to new method
     */
    public function createSiteBeforeScenario()
    {
        return $this->createSiteAssociatedWithAe();
    }

    public function createSite($name = "default")
    {
        if (!array_key_exists($name, $this->createdVts)) {
            $areaOffice1Service = $this->testSupportHelper->getAreaOffice1Service();
            $ao = $areaOffice1Service->create([]);
            $aoSession = $this->session->startSession(
                $ao->data["username"],
                $ao->data["password"]
            );

            $params = [
                'accessToken' => $aoSession->getAccessToken(),
                'name' => $name == "default" ? self::SITE_NAME : $name,
                'town' => self::SITE_TOWN,
                'postcode' => self::SITE_POSTCODE,
            ];

            $response = $this->vehicleTestingStation->create($aoSession->getAccessToken(), $params);
            $responseBody = $response->getBody();
            if (!is_object($responseBody)) {
                throw new Exception("createSite: responseBody is not an object: failed to create Vts");
            }

            $this->createdVts[$name] = $responseBody->toArray()['data'];
        }

        return $this->createdVts[$name];
    }

    public function createSiteAssociatedWithAe($siteName = "default", $aeName = "default")
    {
        if ($this->getSite($siteName)) {
            return $this->getSite($siteName);
        }

        $site = $this->createSite($siteName);

        $areaOffice1Service = $this->testSupportHelper->getAreaOffice1Service();
        $ao = $areaOffice1Service->create([]);
        $aoSession = $this->session->startSession(
            $ao->data["username"],
            $ao->data["password"]
        );

        $aeId = $this->authorisedExaminerContext->createAE(1001, $aeName)["id"];

        $this->authorisedExaminer->linkAuthorisedExaminerWithSite(
            $aoSession->getAccessToken(),
            $aeId,
            $site["siteNumber"]
        );

        return $site;
    }

    /**
     * @Given there is a site :siteName associated with Authorised Examiner :aeName
     */
    public function thereIsASiteAssociatedWithAuthorisedExaminer($siteName, $aeName)
    {
        $this->createSiteAssociatedWithAe($siteName, $aeName);
    }

    /**
     * @Given there is a site associated with Authorised Examiner with following data:
     */
    public function thereIsASiteAssociatedWithAuthorisedExaminerWithFollowingData(TableNode $table)
    {
        $defaults = [
            "name" => SiteData::DEFAULT_NAME,
            "aeName" => AuthorisedExaminerData::DEFAULT_NAME,
            "startDate" => null,
            "endDate" => null
        ];

        $rows = $table->getColumnsHash();
        foreach ($rows as $row) {
            $row["name"] = $row["siteName"];
            unset($row["siteName"]);
            $data = array_replace($defaults, $row);
            $this->siteData->create($data);
        }
    }

    /**
     * @When /^I request information about a VTS$/
     */
    public function iRequestInformationAboutVts($name = "default")
    {
        $this->resultContext = $this->vehicleTestingStation->getVtsDetails(
            $this->createdVts[$name]['id'],
            $this->sessionContext->getCurrentAccessToken()
        );
    }

    /**
     * @Then /^the VTS details are returned$/
     */
    public function theVtsDetailsAreReturned($name = "default")
    {
        /** @var \DvsaCommon\Dto\Site\VehicleTestingStationDto $dto */
        $dto = DtoHydrator::jsonToDto($this->resultContext->getBody()->toArray()['data']);

        PHPUnit::assertThat(
            $dto->getSiteNumber(),
            PHPUnit::equalTo($this->createdVts[$name]['siteNumber']), 'No VTS details returned for VTS Number'
        );
    }

    /**
     * @When /^I search for a VTS with "([^"]*)" "([^"]*)"$/
     * @param string $field
     * @param string $search
     */
    public function iSearchForVtsBySiteNumber($field, $search)
    {
        $params = [
            $field => $search,
            '_class' => '\\DvsaCommon\\Dto\\Search\\SiteSearchParamsDto'
        ];

        $this->vehicleTestingStation->searchVts($params, $this->sessionContext->getCurrentAccessToken());
    }

    /**
     * @When /^I search for a existing Vehicle Testing Station by it's name$/
     */
    public function iSearchForAnExistingVehicleTestingStationByItsName()
    {
        $this->iSearchForAnExistingVehicleTestingStationByParam(['siteName' => self::SITE_NAME]);
    }

    private function iSearchForAnExistingVehicleTestingStationByParam($params)
    {
        $params = array_merge(
            $params,
            [
                "pageNr" => 1,
                "rowsCount" => 10,
                "sortBy" => "site.name",
                "sortDirection" => "ASC",
                '_class' => '\\DvsaCommon\\Dto\\Search\\SiteSearchParamsDto',
            ]
        );
        $response = $this->vehicleTestingStation->searchVts($params, $this->sessionContext->getCurrentAccessToken());
        $this->resultContext = $response->getBody()->toArray()['data'];
    }

    /**
     * @When /^I search for a existing Vehicle Testing Station by it's number$/
     */
    public function iSearchForAnExistingVehicleTestingStationByItsNumber($name = "default")
    {
        $this->iSearchForAnExistingVehicleTestingStationByParam(['siteNumber' => $this->createdVts[$name]['siteNumber']]);
    }

    /**
     * @When /^I search for a existing Vehicle Testing Station by it's town$/
     */
    public function iSearchForAnExistingVehicleTestingStationByItsTown()
    {
        $this->iSearchForAnExistingVehicleTestingStationByParam(['siteTown' => self::SITE_TOWN]);
    }

    /**
     * @When /^I search for a existing Vehicle Testing Station by it's postcode$/
     */
    public function iSearchForAnExistingVehicleTestingStationByItsPostcode()
    {
        $this->iSearchForAnExistingVehicleTestingStationByParam(['sitePostcode' => self::SITE_POSTCODE]);
    }

    /**
     * @Then /^I should see the Vehicle Testing Station result$/
     */
    public function iShouldSeeTheVehicleTestingStationResult()
    {
        /** @var SiteListDto $result */
        $result = DtoHydrator::jsonToDto($this->resultContext);
        $site = $result->getData()[0];

        PHPUnit::assertInstanceOf(SiteListDto::class, $result);
        PHPUnit::assertEquals(self::SITE_NAME, $site['name']);
        PHPUnit::assertEquals(self::SITE_TOWN, $site['town']);
        PHPUnit::assertEquals(self::SITE_POSTCODE, $site['postcode']);
    }

    /**
     * @When /^I search for a Vehicle Testing Station only by a class$/
     */
    public function iSearchForVehicleTestingStationOnlyByClass()
    {
        $params = [
            'siteVehicleClass' => [1],
            '_class' => '\\DvsaCommon\\Dto\\Search\\SiteSearchParamsDto'
        ];

        $response = $this->vehicleTestingStation->searchVts(
            $params,
            $this->sessionContext->getCurrentAccessToken()
        );
        PHPUnit::assertEquals(500, $response->getStatusCode());
        $this->resultContext = [
            '_class' => SiteListDto::class,
            'totalResult' => 0,
        ];
    }

    /**
     * @Then /^the search will return no results$/
     */
    public function theSearchWillReturnNoResult()
    {
        /** @var SiteListDto $result */
        $result = DtoHydrator::jsonToDto($this->resultContext);

        PHPUnit::assertInstanceOf(SiteListDto::class, $result);
        PHPUnit::assertEquals(0, $result->getTotalResultCount());
    }

    /**
     * @When /^I search for a town with no Vehicle Testing Station$/
     */
    public function iSearchForaTownWithNoVehicleTestingStation()
    {
        $params = [
            'siteTown' => self::SITE_TOWN_NO_GARAGE,
            '_class' => '\\DvsaCommon\\Dto\\Search\\SiteSearchParamsDto'
        ];

        $response = $this->vehicleTestingStation->searchVts(
            $params,
            $this->sessionContext->getCurrentAccessToken()
        );
        $this->resultContext = $response->getBody()->toArray()['data'];
    }

    /**
     * @Given I attempt to assign the role of site manager to more than one user of a vehicle testing station
     */
    public function IAttemptToAssignTheRoleOfSiteManagerToMoreThanOneUserOfAVTS($name = "default")
    {
        $testerService = $this->sessionContext->testSupportHelper->getTesterService();

        $this->siteManager1Data = $testerService->create(
            [
                'accountClaimRequired' => false,
                'siteIds' => [1],
            ]
        )->data;

        $this->siteManager2Data = $testerService->create(
            [
                'accountClaimRequired' => false,
                'siteIds' => [1],
            ]
        )->data;

        $role = 'SITE-MANAGER';
        $result1 = $this->vehicleTestingStation->nominateToRole(
            $this->siteManager1Data['personId'],
            $role,
            $this->createdVts[$name]['id'],
            $this->sessionContext->getCurrentAccessToken()
        );

        $role = 'SITE-MANAGER';
        $result2 = $this->vehicleTestingStation->nominateToRole(
            $this->siteManager2Data['personId'],
            $role,
            $this->createdVts[$name]['id'],
            $this->sessionContext->getCurrentAccessToken()
        );

        PHPUnit::assertEquals(200, $result1->getStatusCode());
        PHPUnit::assertEquals(200, $result2->getStatusCode());
    }

    /**
     * @Then /^the site manager roles should be assigned successfully$/
     */
    public function theSiteManagerRolesShouldBeAssignedSuccessfully()
    {
        $positionName = 'Site manager';

        // login as user1
        $this->sessionContext->iMAuthenticatedWithMyUsernameAndPassword(
            $this->siteManager1Data['username'],
            $this->siteManager1Data['password']
        );

        $notification = $this->notification->getRoleNominationNotification(
            $positionName,
            $this->siteManager1Data['personId'],
            $this->sessionContext->getCurrentAccessToken()
        );

        $user1Response = $this->notification->acceptSiteNomination($this->sessionContext->getCurrentAccessToken(), $notification["id"]);

        //login as user2
        $this->sessionContext->iMAuthenticatedWithMyUsernameAndPassword(
            $this->siteManager2Data['username'],
            $this->siteManager2Data['password']
        );

        $notification = $this->notification->getRoleNominationNotification(
            $positionName,
            $this->siteManager2Data['personId'],
            $this->sessionContext->getCurrentAccessToken()
        );

        $user2Response = $this->notification->acceptSiteNomination($this->sessionContext->getCurrentAccessToken(), $notification["id"]);

        PHPUnit::assertEquals(200, $user1Response->getStatusCode());
        PHPUnit::assertEquals(200, $user2Response->getStatusCode());

    }

    /**
     * @return array
     */
    public function getSite($name = "default")
    {
        if (array_key_exists($name, $this->createdVts)) {
            return $this->createdVts[$name];
        }

        return null;
    }

    /**
     * @Given /^I attempt to add risk assessment to site "([^"]*)" on ae "([^"]*)" with data:$/
     */
    public function iAttemptToAddRiskAssessmentToSiteOnAeWithData($siteName = "default", $aeName = "default", TableNode $table)
    {
        $hash = $table->getColumnsHash();

        if (count($hash) !== 1) {
            throw new \InvalidArgumentException(sprintf('Expected a single record but got: %d', count($hash)));
        }

        $this->riskAssessmentData = $this->prepareRiskAssessmentData($siteName, $aeName, $hash[0]);
        $response = $this->vehicleTestingStation->addRiskAssessment($this->sessionContext->getCurrentAccessToken(), $this->getSite($siteName)['id'], $this->riskAssessmentData);

        PHPUnit::assertEquals(200, $response->getStatusCode());
    }

    private function prepareRiskAssessmentData($siteName, $aeName, array $data)
    {
        $site = $this->getSite($siteName);

        if(empty($site)) {
            $siteName = "default";
            $site = $this->createSite($siteName);
        }

        $siteId = $site['id'];
        $siteNumber = $site["siteNumber"];
        $dataGeneratorHelper = $this->testSupportHelper->getDataGeneratorHelper();
        $suffixLength = 10;

        $ae = $this->authorisedExaminerContext->getAe($aeName);
        $aedm = $this->userData->getAedmByAeId($ae["id"]);
        $aedmUsername = $aedm->getUsername();

        $areaOffice1Service = $this->testSupportHelper->getAreaOffice1Service();
        $ao1user = $areaOffice1Service->create([]);
        $ao1Session = $this->session->startSession(
            $ao1user->data['username'],
            $ao1user->data['password']
        );

        $this->authorisedExaminer->linkAuthorisedExaminerWithSite(
            $ao1Session->getAccessToken(),
            $this->authorisedExaminerContext->getAE()["id"],
            $siteNumber
        );

        if (!empty($data["aeRepresentativesUserId"])) {
            $data["aeRepresentativesUserId"] = $aedmUsername;
        }

        if (!empty($data["testerUserId"])) {
            $username = $data["testerUserId"] . $dataGeneratorHelper->generateRandomString($suffixLength);
            $this->personContext->createTester(["siteIds" => [$siteId], "username" => $username]);
            $data["testerUserId"] = $this->personContext->getPersonUsername();
        }

        if (!empty($data["dvsaExaminersUserId"])) {
            $username = $data["dvsaExaminersUserId"] . $dataGeneratorHelper->generateRandomString($suffixLength);
            $vehicleExaminerService = $this->testSupportHelper->getVehicleExaminerService();
            $examiner = $vehicleExaminerService->create(["username" => $username]);
            $data["dvsaExaminersUserId"] = $examiner->data["username"];
        }

        return $data;
    }

    /**
     * @Then risk assessment is added to site
     */
    public function riskAssessmentIsAddedToSite()
    {
        $response = $this->vehicleTestingStation->getRiskAssessment($this->sessionContext->getCurrentAccessToken(), $this->getSite()['id']);
        $riskAssessment = $response->getBody()->toArray()["data"];

        PHPUnit::assertEquals($this->riskAssessmentData["siteAssessmentScore"], $riskAssessment["siteAssessmentScore"]);

        if (empty($this->riskAssessmentData["aeRepresentativesUserId"])) {
            PHPUnit::assertEquals($this->riskAssessmentData["aeRepresentativesFullName"], $riskAssessment["aeRepresentativesFullName"]);
            PHPUnit::assertEquals(null, $riskAssessment["aeRepresentativesUserId"]);
        } else {
            PHPUnit::assertEquals($this->riskAssessmentData["aeRepresentativesUserId"], $riskAssessment["aeRepresentativesUserId"]);
        }

        PHPUnit::assertEquals($this->riskAssessmentData["aeRepresentativesRole"], $riskAssessment["aeRepresentativesRole"]);
        PHPUnit::assertEquals($this->riskAssessmentData["testerUserId"], $riskAssessment["testerUserId"]);

        if (empty($this->riskAssessmentData["aeRepresentativesUserId"])) {
            PHPUnit::assertEquals(null, $riskAssessment["aeRepresentativesUserId"]);
        } else {
            PHPUnit::assertEquals($this->riskAssessmentData["aeRepresentativesUserId"], $riskAssessment["aeRepresentativesUserId"]);
        }

        PHPUnit::assertEquals($this->riskAssessmentData["dateOfAssessment"], $riskAssessment["dateOfAssessment"]);
    }

    /**
     * @When I attempt to add risk assessment to site with invalid data:
     */
    public function iAttemptToAddRiskAssessmentToSiteWithInvalidData(TableNode $table)
    {
        $hash = $table->getColumnsHash();

        if (count($hash) !== 1) {
            throw new \InvalidArgumentException(sprintf('Expected a single record but got: %d', count($hash)));
        }

        $this->riskAssessmentData = $this->prepareRiskAssessmentData("default", "default", $hash[0]);
        $response = $this->vehicleTestingStation->addRiskAssessment($this->sessionContext->getCurrentAccessToken(), $this->getSite()['id'], $this->riskAssessmentData);

        PHPUnit::assertEquals(400, $response->getStatusCode());
    }

    /**
     * @Then risk assessment is not added to site
     */
    public function riskAssessmentIsNotAddedToSite()
    {
        $response = $this->vehicleTestingStation->getRiskAssessment($this->sessionContext->getCurrentAccessToken(), $this->getSite()['id']);

        PHPUnit::assertEquals(404, $response->getStatusCode());
    }

    /**
     * @When class :vtsClass is removed from site
     */
    public function classIsRemovedFromSite($vtsClass, $name = "default")
    {
        $areaOffice1Service = $this->testSupportHelper->getAreaOffice1Service();
        $ao = $areaOffice1Service->create([]);
        $aoSession = $this->session->startSession(
            $ao->data["username"],
            $ao->data["password"]
        );

        $classes = VehicleClassId::getAll();

        if (($key = array_search($vtsClass, $classes)) !== false) {
            unset($classes[$key]);
        }

        $response = $this->vehicleTestingStation->updateSiteDetails(
            $aoSession->getAccessToken(),
            $this->createdVts[$name]["id"],
            [
                VehicleTestingStation::PATCH_PROPERTY_CLASSES => $classes,
                '_class' => VehicleTestingStationDto::class,
            ]
        );

        PHPUnit::assertEquals(200, $response->getStatusCode());
    }

    public function getCreatedSites()
    {
        return $this->createdVts;
    }

    public function iGetTestLogs($name = "default")
    {
        $this->createdVts[$name]["testLogs"] = $this->vehicleTestingStation->getTestLogs(
            $this->sessionContext->getCurrentAccessToken(), $this->createdVts[$name]["id"]
        )->getBody()["data"];

        return $this->createdVts[$name]["testLogs"];
    }

    /**
     * @When /^I attempt to add risk assessment to site with data:$/
     */
    public function iAttemptToAddRiskAssessmentToSiteWithData(TableNode $table)
    {
        $this->iAttemptToAddRiskAssessmentToSiteOnAeWithData("default", "default", $table);
    }

    /**
     * @When I add risk assessment with score :score to site :site on :ae
     */
    public function iAddRiskAssessmentWithScoreToSiteOn($score, SiteDto $site, OrganisationDto $ae)
    {
        $hash = ["siteAssessmentScore" => $score] + $this->getRiskAssessmentDefaults();

        $this->riskAssessmentData = $this->prepareRiskAssessmentData($site->getName(), $ae->getName(), $hash);

        $response = $this->vehicleTestingStation->addRiskAssessment(
            $this->sessionContext->getCurrentAccessToken(),
            $site->getId(),
            $this->riskAssessmentData
        );

        PHPUnit::assertEquals(200, $response->getStatusCode());
    }

    private function getRiskAssessmentDefaults()
    {
        return [
            "siteAssessmentScore" => 200,
            "aeRepresentativesFullName" => "John Kowalsky",
            "aeRepresentativesRole" => "Boss",
            "aeRepresentativesUserId" => "",
            "testerUserId" => "tester",
            "dvsaExaminersUserId" => "dvsaExaminer",
            "dateOfAssessment" => "2015-09-01",
        ];
    }
}
