<?php

use Behat\Behat\Context\Context;
use Dvsa\Mot\Behat\Support\Api\ReplacementCertificate;
use Dvsa\Mot\Behat\Support\Data\Map\ColourMap;
use Dvsa\Mot\Behat\Support\Data\Map\CountryOfRegistrationMap;
use Dvsa\Mot\Behat\Support\Data\MotTestData;
use Dvsa\Mot\Behat\Support\Data\Params\ReplacementCertificateDraftUpdateParams as DraftParams;
use Dvsa\Mot\Behat\Support\Data\UserData;
use Dvsa\Mot\Behat\Support\Data\VehicleData;
use Dvsa\Mot\Behat\Support\Data\SiteData;
use Dvsa\Mot\Behat\Support\Helper\TestSupportHelper;
use Zend\Http\Response as HttpResponse;
use PHPUnit_Framework_Assert as PHPUnit;

class ReplacementCertificateContext implements Context
{
    /**
     * @var ReplacementCertificate
     */
    private $replacementCertificate;

    private $motTestData;

    private $userData;

    private $vehicleData;

    private $siteData;
    /**
     * @var TestSupportHelper
     */
    private $testSupportHelper;

    /**
     * @var array
     */
    private $draftData;
    private $draftParams;

    public function __construct(
        ReplacementCertificate $replacementCertificate,
        MotTestData $motTestData,
        UserData $userData,
        SiteData $siteData,
        VehicleData $vehicleData,
        TestSupportHelper $testSupportHelper
    )
    {
        $this->replacementCertificate = $replacementCertificate;
        $this->motTestData = $motTestData;
        $this->userData = $userData;
        $this->siteData = $siteData;
        $this->vehicleData = $vehicleData;
        $this->testSupportHelper = $testSupportHelper;
    }

    /**
     * @Given a replacement certificate exists
     */
    public function aReplacementCertificateDraftExists()
    {

        $motTestNumber = $this->motTestData->getLast()->getMotTestNumber();
        $this->draftData = $this->createDraft($motTestNumber);
    }

    /**
     * @When I edit this MOT test result
     */
    public function iUpdateReplacementCertificateDraft()
    {
        $motTestNumber = $this->draftData["motTestNumber"];
        $vts = $this->siteData->get("Some VTS");

        $this->draftParams = DraftParams::getDefaultParams();
        $this->draftParams["vtsSiteNumber"] = $vts->getSiteNumber();
        $this->draftParams["reasonForReplacement"] = "Because I can!";

        $this->updateDraft($motTestNumber, $this->draftData["id"], $this->draftParams);

    }

    /**
     * @Then the values on the replacement certificate review should be updated
     */
    public function theValuesOnTheReplacementCertificateReviewShouldBeUpdated()
    {

        $updatedDraft = $this->getUpdatedDraft();

        PHPUnit::assertEquals($this->draftParams[DraftParams::VTS_SITE_NUMBER], $updatedDraft["vts"]["siteNumber"]);
        PHPUnit::assertEquals($this->draftParams[DraftParams::ODOMETER_READING][DraftParams::ODOMETER_VALUE],
            $updatedDraft["odometerReading"]["value"]);
        PHPUnit::assertEquals($this->draftParams[DraftParams::ODOMETER_READING][DraftParams::ODOMETER_UNIT],
            $updatedDraft["odometerReading"]["unit"]);
        PHPUnit::assertEquals($this->draftParams[DraftParams::COUNTRY_OF_REGISTRATION],
            $updatedDraft["countryOfRegistration"]["id"]);
        PHPUnit::assertEquals($this->draftParams[DraftParams::VEHICLE_REGISTRATION_MARK], $updatedDraft["vrm"]);
        PHPUnit::assertEquals($this->draftParams[DraftParams::VEHICLE_IDENTIFICATION_NUMBER], $updatedDraft["vin"]);
        PHPUnit::assertEquals($this->draftParams[DraftParams::PRIMARY_COLOUR], $updatedDraft["primaryColour"]["code"]);
        PHPUnit::assertEquals($this->draftParams[DraftParams::SECONDARY_COLOUR], $updatedDraft["secondaryColour"]["code"]);
    }

    /**
     * @Then a replacement certificate with updated fields is created
     */
    public function aReplacementCertificateWithUpdatedFieldsIsCreated()
    {
        $resp = $this->applyDraft(
            $this->draftData["motTestNumber"],
            $this->draftData["id"],
            []
        );
        PHPUnit::assertEquals(HttpResponse::STATUS_CODE_200, $resp->getStatusCode());


        $certificateDocument = $this->testSupportHelper->getDocumentService()
            ->getByMotTestNumber($this->draftData["motTestNumber"]);


        PHPUnit::assertEquals($certificateDocument->TestStation, $this->draftParams[DraftParams::VTS_SITE_NUMBER]);

        $odometerReading = $this->draftParams[DraftParams::ODOMETER_READING][DraftParams::ODOMETER_VALUE] . " " .
            $this->draftParams[DraftParams::ODOMETER_READING][DraftParams::ODOMETER_UNIT];
        PHPUnit::assertEquals($certificateDocument->Odometer, $odometerReading);

        $countryOfRegistration = (new CountryOfRegistrationMap())->getNameById($this->draftParams[DraftParams::COUNTRY_OF_REGISTRATION]);
        PHPUnit::assertContains($certificateDocument->CountryOfRegistration, $countryOfRegistration);

        PHPUnit::assertEquals($certificateDocument->VRM, $this->draftParams[DraftParams::VEHICLE_REGISTRATION_MARK]);

        PHPUnit::assertEquals($certificateDocument->VIN, $this->draftParams[DraftParams::VEHICLE_IDENTIFICATION_NUMBER]);

        $primaryColour = (new ColourMap())->getNameByCode($this->draftParams[DraftParams::PRIMARY_COLOUR]);
        PHPUnit::assertEquals($certificateDocument->Colour, $primaryColour);
    }

    /**
     * @When I update expiry date :modify on replacement certificate for the vehicle
     */
    public function iUpdateExpiryDateOnReplacementCertificateForTheVehicleWithRegAndVin($modify)
    {
        $motTestNumber = $this->motTestData->getLast()->getMotTestNumber();
        $draft = $this->createDraft($motTestNumber);

        $expiryDate = new \DateTime($draft["expiryDate"]);
        $expiryDate->modify($modify);
        $params = ["expiryDate" => $expiryDate->format("Y-m-d")];
        $this->updateDraft($motTestNumber, $draft["id"], $params);

    }

    private function createDraft($motTestNumber)
    {
        $user = $this->userData->getCurrentLoggedUser();
        $response = $this->replacementCertificate->createDraft(
            $user->getAccessToken(),
            $motTestNumber
        );

        $draftId = $response->getBody()->toArray()["data"]["id"];
        $draft = $this->replacementCertificate->getDraft(
            $motTestNumber,
            $draftId,
            $user->getAccessToken()
        )
            ->getBody()
            ->getData();

        $draft["id"] = $draftId;
        $vehicleId = $this->vehicleData->getLast()->getId();
        $vehicle = $this->vehicleData->getVehicleDetails($vehicleId, $this->userData->getCurrentLoggedUser()->getUsername());

        PHPUnit::assertEquals($vehicle->getRegistration(), $draft["vrm"]);
        PHPUnit::assertEquals($vehicle->getVin(), $draft["vin"]);
        PHPUnit::assertEquals($vehicle->getMakeName(), $draft["make"]["name"]);
        PHPUnit::assertEquals($vehicle->getModelName(), $draft["model"]["name"]);

        $this->draftData = $draft;

        return $draft;
    }

    private function updateDraft($motTestNumber, $draftId, array $params)
    {
        $resp = $this->replacementCertificate->updateDraft(
            $motTestNumber,
            $draftId,
            $params,
            $this->userData->getCurrentLoggedUser()->getAccessToken()
        );

        PHPUnit::assertEquals(HttpResponse::STATUS_CODE_200, $resp->getStatusCode());

        return $resp;
    }

    private function applyDraft($motTestNumber, $draftId, array $params)
    {
        $resp = $this->replacementCertificate->applyDraft(
            $motTestNumber,
            $draftId,
            $params,
            $this->userData->getCurrentLoggedUser()->getAccessToken()
        );

        PHPUnit::assertEquals(HttpResponse::STATUS_CODE_200, $resp->getStatusCode());

        return $resp;
    }

    /**
     * @Then expiry date on replacement certificate draft for the vehicle should be changed to :modify
     */
    public function expiryDateOnReplacementCertificateDraftForTheVehicleShouldBeChangedTo($modify)
    {
        $updatedDraft = $this->replacementCertificate->getDraft(
            $this->motTestData->getLast()->getMotTestNumber(),
            $this->draftData["id"],
            $this->userData->getCurrentLoggedUser()->getAccessToken()
        )
            ->getBody()
            ->getData();

        $expiryDate = (new \DateTime($this->draftData["expiryDate"]))->modify($modify)->format("Y-m-d");

        PHPUnit::assertEquals($expiryDate, $updatedDraft["expiryDate"]);
    }

    /**
     * @Then a replacement certificate is created
     */
    public function aReplacementCertificateIsCreated()
    {
        $params = ["reasonForReplacement" => "Because I can!"];
        $resp = $this->updateDraft(
            $this->motTestData->getlast()->getMotTestNumber(),
            $this->draftData["id"],
            $params
        );

        PHPUnit::assertEquals(HttpResponse::STATUS_CODE_200, $resp->getStatusCode());

        $resp = $this->applyDraft(
            $this->motTestData->getLast()->getMotTestNumber(),
            $this->draftData["id"],
            []
        );
        PHPUnit::assertEquals(HttpResponse::STATUS_CODE_200, $resp->getStatusCode());
    }

    function getUpdatedDraft()
    {
        return $this->replacementCertificate->getDraft(
            $this->motTestData->getLast()->getMotTestNumber(),
            $this->draftData["id"],
            $this->userData->getCurrentLoggedUser()->getAccessToken()
        )
            ->getBody()
            ->getData();
    }
}
