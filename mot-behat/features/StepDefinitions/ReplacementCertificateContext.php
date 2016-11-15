<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Dvsa\Mot\Behat\Support\Api\ReplacementCertificate;
use Dvsa\Mot\Behat\Support\Api\Session;
use Dvsa\Mot\Behat\Support\Data\MotTestData;
use Dvsa\Mot\Behat\Support\Data\UserData;
use Dvsa\Mot\Behat\Support\Data\VehicleData;
use Zend\Http\Response as HttpResponse;
use PHPUnit_Framework_Assert as PHPUnit;

class ReplacementCertificateContext implements Context
{
    /**
     * @var ReplacementCertificate
     */
    private $replacementCertificate;

    /**
     * @var SessionContext
     */
    private $sessionContext;

    /**
     * @var VehicleContext
     */
    private $vehicleContext;

    /**
     * @var MotTestContext
     */
    private $motTestContext;

    private $motTestData;

    private $userData;

    private $vehicleData;

    /**
     * @var array
     */
    private $draftData;

    public function __construct(
        ReplacementCertificate $replacementCertificate,
        MotTestData $motTestData,
        UserData $userData,
        VehicleData $vehicleData
    )
    {
        $this->replacementCertificate = $replacementCertificate;
        $this->motTestData = $motTestData;
        $this->userData = $userData;
        $this->vehicleData = $vehicleData;
    }

    /**
     * @BeforeScenario
     */
    public function gatherContexts(BeforeScenarioScope $scope)
    {
        $this->sessionContext = $scope->getEnvironment()->getContext(SessionContext::class);
        $this->vehicleContext = $scope->getEnvironment()->getContext(VehicleContext::class);
        $this->motTestContext = $scope->getEnvironment()->getContext(MotTestContext::class);
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
        $params =  ["expiryDate" => $expiryDate->format("Y-m-d")];
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
}
