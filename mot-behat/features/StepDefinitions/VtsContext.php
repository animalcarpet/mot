<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Gherkin\Node\TableNode;
use Dvsa\Mot\Behat\Support\Data\Exception\UnexpectedResponseStatusCodeException;
use Dvsa\Mot\Behat\Support\Api\Session;
use Dvsa\Mot\Behat\Support\Api\Vts;
use Dvsa\Mot\Behat\Support\Data\AuthorisedExaminerData;
use Dvsa\Mot\Behat\Support\Data\SiteData;
use Dvsa\Mot\Behat\Support\Data\UserData;
use Dvsa\Mot\Behat\Support\Data\Params\SiteParams;
use Dvsa\Mot\Behat\Support\Data\Params\AuthorisedExaminerParams;
use Dvsa\Mot\Behat\Support\Helper\TestSupportHelper;
use DvsaCommon\Dto\Site\SiteListDto;
use DvsaCommon\Dto\Site\SiteDto;
use DvsaCommon\Dto\Organisation\OrganisationDto;
use DvsaCommon\Enum\VehicleClassId;
use DvsaCommon\Enum\VehicleClassCode;
use DvsaCommon\Dto\Search\SiteSearchParamsDto;
use DvsaCommon\Utility\DtoHydrator;
use Zend\Http\Response as HttpResponse;
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

    /** @var \SessionContext */
    private $sessionContext;

    private $resultContext;

    private $riskAssessmentData = [];

    private $siteData;

    private $userData;

    private $authorisedExaminerData;

    /**
     * @var SiteListDto
     */
    private $foundedVts;

    /**
     * @param Vts $vehicleTestingStation
     */
    public function __construct(
        Vts $vehicleTestingStation,
        TestSupportHelper $testSupportHelper,
        SiteData $siteData,
        UserData $userData,
        AuthorisedExaminerData $authorisedExaminerData
    )
    {
        $this->vehicleTestingStation = $vehicleTestingStation;
        $this->testSupportHelper = $testSupportHelper;
        $this->siteData = $siteData;
        $this->userData = $userData;
        $this->authorisedExaminerData = $authorisedExaminerData;
    }

    /**
     * @BeforeScenario
     */
    public function gatherContexts(BeforeScenarioScope $scope)
    {
        $this->sessionContext = $scope->getEnvironment()->getContext(\SessionContext::class);
    }

    /**
     * @BeforeScenario @create-site
     * BeforeScenario can't handle argument, so I extract this to new method
     */
    public function createSiteBeforeScenario()
    {
        return $this->siteData->createWithParams(
            [
                SiteParams::NAME => self::SITE_NAME,
                SiteParams::TOWN => self::SITE_TOWN,
                SiteParams::POSTCODE => self::SITE_POSTCODE,
                AuthorisedExaminerParams::AE_NAME => "Ae Ltd"
            ]
        );
    }

    /**
     * @Given there is a site :siteName associated with Authorised Examiner :aeName
     */
    public function thereIsASiteAssociatedWithAuthorisedExaminer($siteName, $aeName)
    {
        $this->siteData->createWithParams([SiteParams::NAME => $siteName, AuthorisedExaminerParams::AE_NAME => $aeName]);
    }

    /**
     * @Given there is a site associated with Authorised Examiner with following data:
     */
    public function thereIsASiteAssociatedWithAuthorisedExaminerWithFollowingData(TableNode $table)
    {
        $defaults = [
            SiteParams::NAME => SiteData::DEFAULT_NAME,
            AuthorisedExaminerParams::AE_NAME => AuthorisedExaminerData::DEFAULT_NAME,
            SiteParams::START_DATE => null,
            SiteParams::END_DATE => null
        ];

        $rows = $table->getColumnsHash();
        foreach ($rows as $row) {
            $row[SiteParams::NAME] = $row[SiteParams::SITE_NAME];
            unset($row[SiteParams::SITE_NAME]);
            $data = array_replace($defaults, $row);
            $this->siteData->createWithParams($data);
        }
    }

    /**
     * @When /^I request information about a VTS$/
     */
    public function iRequestInformationAboutVts($name = SiteData::DEFAULT_NAME)
    {
        $this->resultContext = $this->vehicleTestingStation->getVtsDetails(
            $this->siteData->get($name)->getId(),
            $this->userData->getCurrentLoggedUser()->getAccessToken()
        );
    }

    /**
     * @Then /^the VTS details are returned$/
     */
    public function theVtsDetailsAreReturned($name = SiteData::DEFAULT_NAME)
    {
        /** @var \DvsaCommon\Dto\Site\VehicleTestingStationDto $dto */
        $dto = DtoHydrator::jsonToDto($this->resultContext->getBody()->getData());

        PHPUnit::assertThat(
            $dto->getSiteNumber(),
            PHPUnit::equalTo($this->siteData->get($name)->getSiteNumber()), 'No VTS details returned for VTS Number'
        );
    }

    /**
     * @When /^I search for a existing Vehicle Testing Station by it's name$/
     */
    public function iSearchForAnExistingVehicleTestingStationByItsName()
    {
        $this->foundedVts = $this->siteData->searchVtsByName($this->userData->getCurrentLoggedUser(), self::SITE_NAME);
    }

    /**
     * @When /^I search for a existing Vehicle Testing Station by it's number$/
     */
    public function iSearchForAnExistingVehicleTestingStationByItsNumber($name = self::SITE_NAME)
    {
        $this->foundedVts = $this->siteData->searchVtsByNumber($this->userData->getCurrentLoggedUser(), $name);
    }

    /**
     * @When /^I search for a existing Vehicle Testing Station by it's town$/
     */
    public function iSearchForAnExistingVehicleTestingStationByItsTown()
    {
        $this->foundedVts = $this->siteData->searchVtsBySiteTown($this->userData->getCurrentLoggedUser(), self::SITE_TOWN);
    }

    /**
     * @When /^I search for a existing Vehicle Testing Station by it's postcode$/
     */
    public function iSearchForAnExistingVehicleTestingStationByItsPostcode()
    {
        $this->foundedVts = $this->siteData->searchVtsBySitePostcode($this->userData->getCurrentLoggedUser(), self::SITE_POSTCODE);
    }

    /**
     * @Then /^I should see the Vehicle Testing Station result$/
     */
    public function iShouldSeeTheVehicleTestingStationResult()
    {
        $sites = $this->foundedVts->getData();
        $actualSite = end($sites);

        PHPUnit::assertEquals(self::SITE_NAME, $actualSite[SiteParams::NAME]);
        PHPUnit::assertEquals(self::SITE_TOWN, $actualSite[SiteParams::TOWN]);
        PHPUnit::assertEquals(self::SITE_POSTCODE, $actualSite[SiteParams::POSTCODE]);
    }

    /**
     * @When /^I search for a Vehicle Testing Station only by a class$/
     */
    public function iSearchForVehicleTestingStationOnlyByClass()
    {
        try {
            $params = [
                'siteVehicleClass' => [VehicleClassCode::CLASS_1],
                '_class' => SiteSearchParamsDto::class
            ];

            $response = $this->vehicleTestingStation->searchVts(
                $params,
                $this->userData->getCurrentLoggedUser()->getAccessToken()
            );

        } catch (UnexpectedResponseStatusCodeException $exception) {
            $response = $this->vehicleTestingStation->getLastResponse();
        }

        PHPUnit::assertTrue(isset($exception), "Exception not thrown");
        PHPUnit::assertInstanceOf(UnexpectedResponseStatusCodeException::class, $exception);

        PHPUnit::assertEquals(HttpResponse::STATUS_CODE_500, $response->getStatusCode());
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
            '_class' => SiteSearchParamsDto::class
        ];

        $response = $this->vehicleTestingStation->searchVts(
            $params,
            $this->userData->getCurrentLoggedUser()->getAccessToken()
        );
        $this->resultContext = $response->getBody()->getData();
    }

    private function prepareRiskAssessmentData($siteName, $aeName, array $data)
    {
        $site = $this->siteData->tryGet($siteName);

        if(empty($site)) {
            $site = $this->siteData->createUnassociatedSite([SiteParams::NAME => $siteName]);
        }

        $siteId = $site->getId();
        $dataGeneratorHelper = $this->testSupportHelper->getDataGeneratorHelper();
        $suffixLength = 10;

        $ae = $this->authorisedExaminerData->tryGet($aeName);
        if ($ae === null) {
            $ae = $this->authorisedExaminerData->create($aeName);
        }

        $aedm = $this->userData->getAedmByAeId($ae->getId());
        $aedmUsername = $aedm->getUsername();

        if (!empty($data["aeRepresentativesUserId"])) {
            $data["aeRepresentativesUserId"] = $aedmUsername;
        }

        if (!empty($data["testerUserId"])) {
            $username = $data["testerUserId"] . $dataGeneratorHelper->generateRandomString($suffixLength);
            $tester = $this->userData->createTesterAssignedWitSite($siteId, $username);
            $data["testerUserId"] = $tester->getUsername();
        }

        if (!empty($data["dvsaExaminersUserId"])) {
            $username = $data["dvsaExaminersUserId"] . $dataGeneratorHelper->generateRandomString($suffixLength);
            $examiner = $this->userData->createVehicleExaminer($username);
            $data["dvsaExaminersUserId"] = $examiner->getUsername();
        }

        $dateOfAssessment = (new \DateTime($data["dateOfAssessment"]))->format("Y-m-d");
        $data["dateOfAssessment"] = $dateOfAssessment;

        return $data;
    }

    /**
     * @Then risk assessment is added to :site site
     */
    public function riskAssessmentIsAddedToSite(SiteDto $site)
    {
        $user = $this->userData->getCurrentLoggedUser();
        $response = $this->vehicleTestingStation->getRiskAssessment($user->getAccessToken(), $site->getId());
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
     * @When I attempt to add risk assessment to :site site with invalid data:
     */
    public function iAttemptToAddRiskAssessmentToSiteWithInvalidData(SiteDto $site, TableNode $table)
    {
        $hash = $table->getColumnsHash();

        if (count($hash) !== 1) {
            throw new \InvalidArgumentException(sprintf('Expected a single record but got: %d', count($hash)));
        }

        $this->riskAssessmentData = $this->prepareRiskAssessmentData($site->getName(), $site->getOrganisation()->getName(), $hash[0]);

        try {
            $response = $this->vehicleTestingStation->addRiskAssessment(
                $this->userData->getCurrentLoggedUser()->getAccessToken(),
                $site->getId(),
                $this->riskAssessmentData
            );

        } catch (UnexpectedResponseStatusCodeException $exception) {
            $response = $this->vehicleTestingStation->getLastResponse();
        }

        PHPUnit::assertTrue(isset($exception), "Exception not thrown");
        PHPUnit::assertInstanceOf(UnexpectedResponseStatusCodeException::class, $exception);
        PHPUnit::assertEquals(HttpResponse::STATUS_CODE_400, $response->getStatusCode());
    }

    /**
     * @Then risk assessment is not added to :site site
     */
    public function riskAssessmentIsNotAddedToSite(SiteDto $site)
    {
        try {
            $response = $this->vehicleTestingStation->getRiskAssessment(
                $this->userData->getCurrentLoggedUser()->getAccessToken(),
                $site->getId()
            );

        } catch (UnexpectedResponseStatusCodeException $exception) {
            $response = $this->vehicleTestingStation->getLastResponse();
        }

        PHPUnit::assertTrue(isset($exception), "Exception not thrown");
        PHPUnit::assertInstanceOf(UnexpectedResponseStatusCodeException::class, $exception);
        PHPUnit::assertEquals(HttpResponse::STATUS_CODE_404, $response->getStatusCode());
        PHPUnit::assertContains("No assessment found", $exception->getMessage());
    }

    /**
     * @When class :vtsClass is removed from site :site
     */
    public function classIsRemovedFromSite($vtsClass, SiteDto $site)
    {
        $classes = VehicleClassId::getAll();
        if (($key = array_search($vtsClass, $classes)) !== false) {
            unset($classes[$key]);
        }

        $this->siteData->updateSiteClasses($site->getId(), $classes);
    }

    /**
     * @When I attempt to add risk assessment to :site site with data:
     */
    public function iAttemptToAddRiskAssessmentToCurrentSiteWithData(SiteDto $site, TableNode $table)
    {
        $dataGeneratorHelper = $this->testSupportHelper->getDataGeneratorHelper();
        $suffixLength = 12;

        $siteName = $site->getName();
        $aeName = $site->getOrganisation()->getName();
        $aedm = $this->userData->getAedmByAeId($site->getOrganisation()->getId());
        $tester = $this->userData->createTesterAssignedWitSite($site->getId(), $dataGeneratorHelper->generateRandomString($suffixLength));
        $examiner = $this->userData->createVehicleExaminer($dataGeneratorHelper->generateRandomString($suffixLength));

        $default = [
            "aeRepresentativesUserId" => $aedm->getUsername(),
            "aeRepresentativesRole" => "Boss",
            "aeRepresentativesFullName" => "Agent Smith",
            "testerUserId" =>  $tester->getUsername(),
            "dvsaExaminersUserId" =>$examiner->getUsername()
        ];

        $rows = $table->getColumnsHash();
        foreach ($rows as $row) {
            $row = array_replace($default, $row);
            $this->addRiskAssessment($siteName, $aeName, $row);
        }
    }

    /**
     * @When I attempt to add risk assessment with data:
     */
    public function iAttemptToAddRiskAssessmentToWithData(TableNode $table)
    {
        $rows = $table->getColumnsHash();
        foreach ($rows as $row) {
            $site = $this->siteData->get($row["siteName"]);
            $this->iAttemptToAddRiskAssessmentToCurrentSiteWithData($site,$table);
        }
    }

    private function addRiskAssessment($siteName, $aeName, array $data)
    {
        $this->riskAssessmentData = $this->prepareRiskAssessmentData($siteName, $aeName, $data);
        $response = $this->vehicleTestingStation->addRiskAssessment(
            $this->userData->getCurrentLoggedUser()->getAccessToken(),
            $this->siteData->get($siteName)->getId(),
            $this->riskAssessmentData
        );

        PHPUnit::assertEquals(HttpResponse::STATUS_CODE_200, $response->getStatusCode());
    }

    /**
     * @Given every site has created risk assessments
     */
    public function everySiteHasCreatedRiskAssessments()
    {
        $sites = $this->siteData->getAll();
    }
}
