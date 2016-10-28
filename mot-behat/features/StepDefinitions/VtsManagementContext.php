<?php

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Dvsa\Mot\Behat\Support\Api\Session;
use DvsaCommon\Model\VehicleTestingStation;
use PHPUnit_Framework_Assert as PHPUnit;
use Dvsa\Mot\Behat\Support\Api\Vts;
use Dvsa\Mot\Behat\Support\Data\Params\SiteParams;
use Dvsa\Mot\Behat\Support\Data\Params\FacilityParams;
use Dvsa\Mot\Behat\Support\Data\UserData;
use Dvsa\Mot\Behat\Support\Data\SiteData;
use Dvsa\Mot\Behat\Support\Data\VehicleData;
use Dvsa\Mot\Behat\Support\Data\MotTestData;
use DvsaCommon\Dto\Site\SiteDto;
use DvsaCommon\Enum\SiteStatusCode;
use DvsaCommon\Dto\Site\VehicleTestingStationDto;
use Dvsa\Mot\Behat\Support\Response;
use Zend\Http\Response as HttpResponse;

class VtsManagementContext implements Context
{
    protected $vehicleTestingStation;

    /**
     * @var Response
     */
    protected $response;

    /**
     * @var array
     */
    protected $testingFacilitiesData = [
        FacilityParams::OPTL => null,
        FacilityParams::TPTL => null,
    ];

    /**
     * @var string
     */
    protected $siteStatus;

    private $userData;

    private $siteData;

    private $vehicleData;

    private $motTestData;

    public function __construct(
        Vts $vehicleTestingStation,
        UserData $userData,
        SiteData $siteData,
        VehicleData $vehicleData,
        MotTestData $motTestData
    )
    {
        $this->vehicleTestingStation = $vehicleTestingStation;
        $this->userData = $userData;
        $this->siteData = $siteData;
        $this->vehicleData = $vehicleData;
        $this->motTestData = $motTestData;
    }

    /**
     * @Given a :name vehicle testing site exists
     */
    public function aVehicleTestingSiteExists($name)
    {
        $this->siteData->create($name);
    }

    /**
     * @When I configure :site test lines to:
     */
    public function iConfigureItsTestLinesTo(SiteDto $site,TableNode $table)
    {
        $data = $table->getColumnsHash();
        $numOptl = (int) $data[0]['number of one person test lanes'];
        $numTptl = (int) $data[0]['number of two person test lanes'];

        $this->testingFacilitiesData[FacilityParams::OPTL] = $numOptl;
        $this->testingFacilitiesData[FacilityParams::TPTL] = $numTptl;

        $response = $this->vehicleTestingStation->updateTestingFacilities(
            $this->userData->getCurrentLoggedUser()->getAccessToken(),
            $site->getId(),
            [SiteParams::NAME => $site->getName()],
            $numOptl,
            $numTptl
        );

        $this->response = $response;
    }

    /**
     * @Then site details for :site should be updated
     */
    public function siteDetailsShouldBeUpdated(SiteDto $site)
    {
        $facilities = $this->siteData->getVtsDetails($this->userData->getCurrentLoggedUser(), $site->getId());

        PHPUnit::assertCount($this->testingFacilitiesData[FacilityParams::TPTL], $facilities->getFacilities()[FacilityParams::TPTL]);
        PHPUnit::assertCount($this->testingFacilitiesData[FacilityParams::OPTL], $facilities->getFacilities()[FacilityParams::OPTL]);
    }

    /**
     * @Then site testing classes for :site should be removed
     */
    public function siteTestingClassesShoudBeRemoved(SiteDto $site)
    {
        $facilities = $this->siteData->getVtsDetails($this->userData->getCurrentLoggedUser(), $site->getId());
        PHPUnit::assertCount(0, $facilities->getTestClasses());
    }

    /**
     * @Then site details for :site should not be updated
     */
    public function siteDetailsForShouldNotBeUpdated(SiteDto $site)
    {
        PHPUnit::assertEquals(HttpResponse::STATUS_CODE_400, $this->response->getStatusCode());
        $expectedBody = [
            'errors' => [
                [
                    "message" => "A number for either OPTL or TPTL must be selected",
                    "code" => 60,
                    "displayMessage" => "A number for either OPTL or TPTL must be selected",
                    "field" => "facilityOptl",
                ],
            ],
        ];
        PHPUnit::assertSame($expectedBody, $this->response->getBody()->toArray());

        $facilities = $this->siteData->getVtsDetails($this->userData->getCurrentLoggedUser(), $site->getId());
        PHPUnit::assertCount(1, $facilities->getFacilities());
        PHPUnit::assertCount(1, $facilities->getFacilities()[FacilityParams::OPTL]);
    }

    /**
     * @Then Site details should not be updated
     */
    public function siteDetailsShouldNotBeUpdated()
    {
        PHPUnit::assertEquals(HttpResponse::STATUS_CODE_403, $this->response->getStatusCode());
        $expectedErrors = [
            [
                'message' => 'Forbidden',
                'code' => 160,
            ],
        ];

        $responseArray = $this->response->getBody()->toArray();
        PHPUnit::assertArrayHasKey('errors', $responseArray);
        PHPUnit::assertSame($expectedErrors, $responseArray['errors']);
    }

    /**
     * @When I change the :site site status to Applied
     */
    public function iChangeTheSiteStatusToApplied(SiteDto $site)
    {
        $this->updateSiteDetails($site, SiteStatusCode::APPLIED);
    }

    /**
     * @When I change the :site site status to Lapsed
     */
    public function iChangeTheSiteStatusToLapsed(SiteDto $site)
    {
        $this->updateSiteDetails($site, SiteStatusCode::LAPSED);
    }

    /**
     * @When I change the :site site status to Approved
     */
    public function iChangeTheSiteStatusToApproved(SiteDto $site)
    {
        $this->updateSiteDetails($site, SiteStatusCode::APPROVED);
    }

    /**
     * @When I change the :site site status to Rejected
     */
    public function iChangeTheSiteStatusToRejected(SiteDto $site)
    {
        $this->updateSiteDetails($site, SiteStatusCode::REJECTED);
    }

    /**
     * @When I change the :site site status to Retracted
     */
    public function iChangeTheSiteStatusToRetracted(SiteDto $site)
    {
        $this->updateSiteDetails($site, SiteStatusCode::RETRACTED);
    }

    /**
     * @When I change the :site site status to Extinct
     */
    public function iChangeTheSiteStatusToExtinct(SiteDto $site)
    {
        $this->updateSiteDetails($site, SiteStatusCode::EXTINCT);
    }

     /**
     * @When I remove all test classes from :site
     */
    public function iRemoveAllTestClassesFrom(SiteDto $site)
    {
        $this->response = $this->vehicleTestingStation->removeAllTestClasses(
            $this->userData->getCurrentLoggedUser()->getAccessToken(),
            $site->getId()
        );
    }


    protected function updateSiteDetails(SiteDto $site, $siteStatus)
    {
        $this->siteStatus = $siteStatus;
        $response = $this->vehicleTestingStation->updateSiteDetails(
            $this->userData->getCurrentLoggedUser()->getAccessToken(),
            $site->getId(),
            [
                VehicleTestingStation::PATCH_PROPERTY_STATUS => $this->siteStatus,
                '_class' => VehicleTestingStationDto::class,
            ]
        );

        $this->response = $response;
    }

    /**
     * @Then site status for :site should be updated
     */
    public function siteStatusShouldBeUpdated(SiteDto $site)
    {
        PHPUnit::assertEquals(HttpResponse::STATUS_CODE_200, $this->response->getStatusCode());
        $expectedBody = [
            'data' => [
                'success' => true,
            ],
        ];
        PHPUnit::assertSame($expectedBody, $this->response->getBody()->toArray());

        $vtsDto = $this->siteData->getVtsDetails($this->userData->getCurrentLoggedUser(), $site->getId());

        PHPUnit::assertEquals($this->siteStatus, $vtsDto->getStatus());
    }

    /**
     * @When /^I try to start MOT test$/
     */
    public function iTryToStartMOTTest()
    {
        try {
            $this->motTestData->create(
                $this->userData->getCurrentLoggedUser(),
                $this->vehicleData->create(),
                $this->siteData->get()
            );
        } catch (\Exception $e) {

        }
    }

    /**
     * @Then /^I am not permitted to do this$/
     */
    public function iAmNotPermittedToDoThis()
    {
        $data = $this->motTestData->getLastResponse()->getBody();
        PHPUnit::assertArrayHasKey('errors', $data);
    }
}