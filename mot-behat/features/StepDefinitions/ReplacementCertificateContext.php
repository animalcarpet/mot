<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Dvsa\Mot\Behat\Support\Api\MotTest;
use Dvsa\Mot\Behat\Support\Api\Vehicle;
use Dvsa\Mot\Behat\Support\Api\ReplacementCertificate;
use Dvsa\Mot\Behat\Support\Api\Session;
use PHPUnit_Framework_Assert as PHPUnit;

class ReplacementCertificateContext implements Context
{
    /**
     * @var MotTest
     */
    private $motTest;

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

    /**
     * @var array
     */
    private $draftData;

    public function __construct(ReplacementCertificate $replacementCertificate)
    {
        $this->replacementCertificate = $replacementCertificate;
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
        $motTestNumber = $this->motTestContext->getMotTestNumber();
        $draft = $this->createDraft($motTestNumber);

        $expiryDate = new \DateTime($draft["expiryDate"]);
        $expiryDate->modify($modify);
        $params =  ["expiryDate" => $expiryDate->format("Y-m-d")];
        $this->updateDraft($motTestNumber, $draft["id"], $params);
    }

    private function createDraft($motTestNumber)
    {
        $response = $this->replacementCertificate->createDraft(
            $this->sessionContext->getCurrentAccessToken(),
            $motTestNumber
        );

        $draftId = $response->getBody()->toArray()["data"]["id"];
        $draft = $this->replacementCertificate->getDraft(
            $motTestNumber,
            $draftId,
            $this->sessionContext->getCurrentAccessToken())
            ->getBody()
            ->toArray()["data"];

        $draft["id"] = $draftId;
        $vehicle = $this->vehicleContext->getCurrentVehicleData()->toArray()["data"];

        PHPUnit::assertEquals($vehicle["registration"], $draft["vrm"]);
        PHPUnit::assertEquals($vehicle["vin"], $draft["vin"]);
        PHPUnit::assertEquals($vehicle["makeName"], $draft["make"]["name"]);
        PHPUnit::assertEquals($vehicle["modelName"], $draft["model"]["name"]);

        $this->draftData = $draft;

        return $draft;
    }

    private function updateDraft($motTestNumber, $draftId, array $params)
    {
        $resp = $this->replacementCertificate->updateDraft(
            $motTestNumber,
            $draftId,
            $params,
            $this->sessionContext->getCurrentAccessToken()
        );

        PHPUnit::assertEquals(200, $resp->getStatusCode());

        return $resp;
    }

    private function applyDraft($motTestNumber, $draftId, array $params)
    {
        $resp = $this->replacementCertificate->applyDraft(
            $motTestNumber,
            $draftId,
            $params,
            $this->sessionContext->getCurrentAccessToken()
        );

        PHPUnit::assertEquals(200, $resp->getStatusCode());

        return $resp;
    }

    /**
     * @Then expiry date on replacement certificate draft for the vehicle should be changed to :modify
     */
    public function expiryDateOnReplacementCertificateDraftForTheVehicleShouldBeChangedTo($modify)
    {
        $updatedDraft = $this->replacementCertificate->getDraft(
            $this->motTestContext->getMotTestNumber(),
            $this->draftData["id"],
            $this->sessionContext->getCurrentAccessToken())
            ->getBody()
            ->toArray()["data"];

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
            $this->motTestContext->getMotTestNumber(),
            $this->draftData["id"],
            $params,
            $this->sessionContext->getCurrentAccessToken()
            );

        PHPUnit::assertEquals(200, $resp->getStatusCode());

        $resp = $this->applyDraft(
            $this->motTestContext->getMotTestNumber(),
            $this->draftData["id"],
            [],
            $this->sessionContext->getCurrentAccessToken()
        );
        PHPUnit::assertEquals(200, $resp->getStatusCode());
    }
}
