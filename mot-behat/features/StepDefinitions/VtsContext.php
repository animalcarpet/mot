<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Dvsa\Mot\Behat\Support\Api\Session;
use Dvsa\Mot\Behat\Support\Api\Vts;
use Dvsa\Mot\Behat\Support\Api\Notification;
use Dvsa\Mot\Behat\Support\Api\AuthorisedExaminer;
use DvsaCommon\Dto\Site\VehicleTestingStationDto;
use DvsaCommon\Model\VehicleTestingStation;
use PHPUnit_Framework_Assert as PHPUnit;
use DvsaCommon\Dto\Site\SiteListDto;
use DvsaCommon\Utility\DtoHydrator;
use Dvsa\Mot\Behat\Support\Helper\TestSupportHelper;
use DvsaCommon\Enum\VehicleClassId;
use Behat\Gherkin\Node\TableNode;

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
    private $siteCreate;
    private $resultContext;

    /** @var PersonContext */
    private $personContext;

    /** @var AuthorisedExaminerContext */
    private $authorisedExaminerContext;

    private $siteManager1Data = null;
    private $siteManager2Data = null;

    private $riskAssessmentData = [];

    /**
     * @param Vts $vehicleTestingStation
     */
    public function __construct(
        Vts $vehicleTestingStation,
        TestSupportHelper $testSupportHelper,
        Session $session,
        Notification $notification,
        AuthorisedExaminer $authorisedExaminer
    )
    {
        $this->vehicleTestingStation = $vehicleTestingStation;
        $this->testSupportHelper = $testSupportHelper;
        $this->session = $session;
        $this->notification = $notification;
        $this->authorisedExaminer = $authorisedExaminer;
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
     */
    public function createSite()
    {
        if ($this->siteCreate === null) {
            $areaOffice1Service = $this->testSupportHelper->getAreaOffice1Service();
            $ao = $areaOffice1Service->create([]);
            $aoSession = $this->session->startSession(
                $ao->data["username"],
                $ao->data["password"]
            );

            $params = [
                'accessToken' => $aoSession->getAccessToken(),
                'name' => self::SITE_NAME,
                'town' => self::SITE_TOWN,
                'postcode' => self::SITE_POSTCODE,
            ];

            $response = $this->vehicleTestingStation->create($aoSession->getAccessToken(), $params);
            $responseBody = $response->getBody();
            if (! is_object($responseBody)) {
                throw new Exception("createSite: responseBody is not an object: failed to create Vts");
            }
            $this->siteCreate = $responseBody->toArray()['data'];
        }
    }

    /**
     * @When /^I request information about a VTS$/
     */
    public function iRequestInformationAboutVts()
    {
        $this->resultContext = $this->vehicleTestingStation->getVtsDetails(
            $this->siteCreate['id'],
            $this->sessionContext->getCurrentAccessToken()
        );
    }

    /**
     * @Then /^the VTS details are returned$/
     */
    public function theVtsDetailsAreReturned()
    {
        /** @var \DvsaCommon\Dto\Site\VehicleTestingStationDto $dto */
        $dto = DtoHydrator::jsonToDto($this->resultContext->getBody()->toArray()['data']);

        PHPUnit::assertThat(
            $dto->getSiteNumber(),
            PHPUnit::equalTo($this->siteCreate['siteNumber']), 'No VTS details returned for VTS Number'
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
    public function iSearchForAnExistingVehicleTestingStationByItsNumber()
    {
        $this->iSearchForAnExistingVehicleTestingStationByParam(['siteNumber' => $this->siteCreate['siteNumber']]);
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
    public function IAttemptToAssignTheRoleOfSiteManagerToMoreThanOneUserOfAVTS()
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
            $this->siteCreate['id'],
            $this->sessionContext->getCurrentAccessToken()
        );

        $role = 'SITE-MANAGER';
        $result2 = $this->vehicleTestingStation->nominateToRole(
            $this->siteManager2Data['personId'],
            $role,
            $this->siteCreate['id'],
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
    public function getSite()
    {
        return $this->siteCreate;
    }

    /**
     * @When I attempt to add risk assessment to site with data:
     */
    public function iAttemptToAddRiskAssessmentToSiteWithData(TableNode $table)
    {
        $hash = $table->getColumnsHash();

        if (count($hash) !== 1) {
            throw new \InvalidArgumentException(sprintf('Expected a single record but got: %d', count($hash)));
        }

        $this->riskAssessmentData = $this->prepareRiskAssessmentData($hash[0]);
        $response = $this->vehicleTestingStation->addRiskAssessment($this->sessionContext->getCurrentAccessToken(), $this->getSite()['id'], $this->riskAssessmentData);

        PHPUnit::assertEquals(200, $response->getStatusCode());
    }

    private function prepareRiskAssessmentData(array $data)
    {
        $this->createSite();
        $siteId = $this->getSite()['id'];
        $siteNumber = $this->getSite()["siteNumber"];
        $dataGeneratorHelper = $this->testSupportHelper->getDataGeneratorHelper();
        $suffixLength = 10;

        $aedmData = [];
        if (!empty($data["aeRepresentativesUserId"])) {
            $username = $data["aeRepresentativesUserId"] . $dataGeneratorHelper->generateRandomString($suffixLength);
            $aedmData = ["username" => $username];
        }
        $this->personContext->createAEDM($aedmData);
        $aedmUsername = $this->personContext->getPersonUsername();

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
            $this->personContext->createTester(["siteIds" => [$siteId] , "username" => $username]);
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

        $this->riskAssessmentData = $this->prepareRiskAssessmentData($hash[0]);
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
    public function classIsRemovedFromSite($vtsClass)
    {
        $areaOffice1Service = $this->testSupportHelper->getAreaOffice1Service();
        $ao = $areaOffice1Service->create([]);
        $aoSession = $this->session->startSession(
            $ao->data["username"],
            $ao->data["password"]
        );

        $classes = VehicleClassId::getAll();

        if(($key = array_search($vtsClass, $classes)) !== false) {
            unset($classes[$key]);
        }

        $response = $this->vehicleTestingStation->updateSiteDetails(
            $aoSession->getAccessToken(),
            $this->siteCreate["id"],
            [
                VehicleTestingStation::PATCH_PROPERTY_CLASSES => $classes,
                '_class' => VehicleTestingStationDto::class,
            ]
        );

        PHPUnit::assertEquals(200, $response->getStatusCode());
    }
}
