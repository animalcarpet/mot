<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Dvsa\Mot\Behat\Support\Api\Person;
use Dvsa\Mot\Behat\Support\Api\Session;
use Dvsa\Mot\Behat\Support\Api\MotTestingCertificate;
use Dvsa\Mot\Behat\Support\Data\Params\SiteParams;
use Dvsa\Mot\Behat\Support\Data\SiteData;
use DvsaCommon\Model\VehicleClassGroup;
use Dvsa\Mot\Behat\Support\Helper\TestSupportHelper;
use DvsaCommon\Enum\AuthorisationForTestingMotStatusCode;
use DvsaCommon\Utility\ArrayUtils;
use DvsaCommon\Enum\VehicleClassGroupCode;
use Zend\Http\Response as HttpResponse;
use PHPUnit_Framework_Assert as PHPUnit;

class MotTestingCertificateContext implements Context, \Behat\Behat\Context\SnippetAcceptingContext
{
    /** @var TestSupportHelper  */
    private $testSupportHelper;

    /** @var MotTestingCertificate  */
    private $motTestingCertificate;

    /** @var Person  */
    private $person;

    /** @var SessionContext */
    private $sessionContext;

    /** @var VtsContext */
    private $vtsContext;

    /** @var PersonContext */
    private $personContext;

    private $personId;

    private $data;

    private $siteData;

    public function __construct(
        TestSupportHelper $testSupportHelper,
        MotTestingCertificate $motTestingCertificate,
        Person $person,
        SiteData $siteData
    ) {
        $this->testSupportHelper = $testSupportHelper;
        $this->motTestingCertificate = $motTestingCertificate;
        $this->person = $person;
        $this->siteData = $siteData;
    }

    /**
     * @BeforeScenario
     */
    public function gatherContexts(BeforeScenarioScope $scope)
    {
        $this->sessionContext = $scope->getEnvironment()->getContext(SessionContext::class);
        $this->vtsContext = $scope->getEnvironment()->getContext(VtsContext::class);
        $this->personContext = $scope->getEnvironment()->getContext(PersonContext::class);
    }

    /**
     * @When I enter mot testing certificate details
     */
    public function iEnterMotTestingCertificateDetails()
    {
        $this->enterMotTestingCertificateDetailsForPerson($this->sessionContext->getCurrentUserId());
    }

    /**
     * @When I enter mot testing certificate details for person
     */
    public function iEnterMotTestingCertificateDetailsForPerson()
    {
        $this->enterMotTestingCertificateDetailsForPerson($this->personId);
    }

    private function enterMotTestingCertificateDetailsForPerson($personId)
    {
        $this->personId = $personId;

        $siteNumber = $this->siteData->get()->getSiteNumber();

        $this->data = [
            VehicleClassGroupCode::BIKES => [
                "id" => null,
                "vehicleClassGroupCode" => VehicleClassGroupCode::BIKES,
                SiteParams::SITE_NUMBER => $siteNumber,
                "certificateNumber" => "certNumA1234",
                "dateOfQualification" => "2015-12-12"
            ],

            VehicleClassGroupCode::CARS_ETC => [
                "id" => null,
                "vehicleClassGroupCode" => VehicleClassGroupCode::CARS_ETC,
                SiteParams::SITE_NUMBER => "",
                "certificateNumber" => "certNumB1234",
                "dateOfQualification" => "2015-12-12"
            ],
        ];
    }

    /**
     * @Then Mot Testing Certificate details for class :vehicleClassGroup is created
     */
    public function motTestingCertificateDetailsForClassIsCreated($vehicleClassGroup)
    {
        $this->validateVehicleClassGroup($vehicleClassGroup);

        $data = ArrayUtils::tryGet($this->data, $vehicleClassGroup, []);

        $response = $this
            ->motTestingCertificate
            ->createCertificate($this->sessionContext->getCurrentAccessToken(), $this->personId, $data);

        PHPUnit::assertEquals(HttpResponse::STATUS_CODE_200, $response->getStatusCode());
    }

    /**
     * @Then Qualification status for group :vehicleClassGroup is set to :qualificationStatus
     */
    public function qualificationStatusForGroupIsSetTo($vehicleClassGroup, $qualificationStatus)
    {
        $this->validateVehicleClassGroup($vehicleClassGroup);

        $allowedStatuses = [
            "DEMO_TEST_NEEDED" => AuthorisationForTestingMotStatusCode::DEMO_TEST_NEEDED,
            "NOT_APPLIED" => null,
        ];

        $expectedStatus = ArrayUtils::get($allowedStatuses, $qualificationStatus);

        $response = $this
            ->person
            ->getPersonMotTestingClasses($this->sessionContext->getCurrentAccessToken(), $this->personId)
        ;

        $qualifications = $response->getBody()->getData();

        $vehicleClasses = VehicleClassGroup::getClassesForGroup($vehicleClassGroup);
        foreach ($vehicleClasses as $class) {
            $status = $qualifications["class" . $class];
            PHPUnit::assertEquals($expectedStatus, $status);
        }
    }

    private function validateVehicleClassGroup($vehicleClassGroup)
    {
        if (!VehicleClassGroupCode::exists($vehicleClassGroup)) {
            throw new InvalidArgumentException("Vehicle class group '" . $vehicleClassGroup . "' does not exist.");
        }
    }

    /**
     * @Given person with :qualificationStatus status for group :vehicleClassGroup has account
     */
    public function personWithStatusForGroupHasAccount($qualificationStatus, $vehicleClassGroup)
    {
        if ($this->personId === null) {
            $service = $this->testSupportHelper->getUserService();
            $user = $service->create([]);

            $this->personId = $user->data["personId"];
        }

        $this->personContext->setQualificationStatus($this->personId, $qualificationStatus, $vehicleClassGroup);
    }

    /**
     * @Given person with Mot Testing Certificate for group :vehicleClassGroup exists
     */
    public function personWithMotTestingCertificateForGroupExists($vehicleClassGroup)
    {
        $this->personWithStatusForGroupHasAccount("INITIAL_TRAINING_NEEDED", $vehicleClassGroup);
        $this->iEnterMotTestingCertificateDetailsForPerson();

        if ($vehicleClassGroup === "A and B") {
            $this->motTestingCertificateDetailsForClassIsCreated(VehicleClassGroupCode::BIKES);
            $this->motTestingCertificateDetailsForClassIsCreated(VehicleClassGroupCode::CARS_ETC);
        } else {
            $this->motTestingCertificateDetailsForClassIsCreated($vehicleClassGroup);
        }

    }

    /**
     * @Given I have Mot Testing Certificate for group :vehicleClassGroup
     */
    public function iHaveMotTestingCertificateForGroup($vehicleClassGroup)
    {
        $this->personContext->iHaveStatusForGroup("INITIAL_TRAINING_NEEDED", $vehicleClassGroup);
        $this->enterMotTestingCertificateDetailsForPerson($this->sessionContext->getCurrentUserId());

        if ($vehicleClassGroup === "A and B") {
            $this->motTestingCertificateDetailsForClassIsCreated(VehicleClassGroupCode::BIKES);
            $this->motTestingCertificateDetailsForClassIsCreated(VehicleClassGroupCode::CARS_ETC);
        } else {
            $this->motTestingCertificateDetailsForClassIsCreated($vehicleClassGroup);
        }

    }

    /**
     * @When I remove Mot Testing Certificate for group :vehicleClassGroup
     */
    public function iRemoveMotTestingCertificateForGroup($vehicleClassGroup)
    {
        $response = $this
            ->motTestingCertificate
            ->removeCertificate($this->sessionContext->getCurrentAccessToken(), $this->personId, strtolower($vehicleClassGroup));

        PHPUnit::assertEquals(HttpResponse::STATUS_CODE_200, $response->getStatusCode());
    }
}
