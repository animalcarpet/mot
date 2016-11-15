<?php

use Behat\Behat\Context\Context;
use DvsaCommon\Enum\LicenceCountryCode;
use Dvsa\Mot\Behat\Support\Api\Session\AuthenticatedUser;
use Dvsa\Mot\Behat\Support\Api\CustomerService;
use Dvsa\Mot\Behat\Support\Data\Params\PersonParams;
use Dvsa\Mot\Behat\Support\Data\UserData;
use Zend\Http\Response as HttpResponse;
use PHPUnit_Framework_Assert as PHPUnit;

class PersonDrivingLicenceContext implements Context
{
    private $userData;
    private $customerService;

    public function __construct(CustomerService $customerService, UserData $userData)
    {
        $this->userData = $userData;
        $this->customerService = $customerService;
    }

    /**
     * @Given I have selected a user with name :username who needs to have a licence added to their profile
     */
    public function selectUserWithoutLicence($username)
    {
        $this->userData->createTesterWithoutLicence($username);
    }

    /**
     * @Given I have selected a user with name :username who needs to have their licence edited
     * @Given I have selected a user with name :username who needs to have their licence deleted
     */
    public function selectUserWithLicence($username)
    {
        $this->userData->createTester($username);
    }

    /**
     * @When I add a licence :licenceNumber to :user profile
     * @When I update :user licence to :licenceNumber
     * @var string $licenceNumber
     */
    public function updateUserLicence($licenceNumber, AuthenticatedUser $user)
    {
        $this->updateUserLicenceWithRegion($licenceNumber, LicenceCountryCode::GREAT_BRITAIN_ENGLAND_SCOTLAND_AND_WALES, $user);
    }

    /**
     * @When I add a licence :licenceNumber with the region :licenceRegion to :user profile
     * @When I update :user licence to :licenceNumber and the region :licenceRegion
     */
    public function updateUserLicenceWithRegion($licenceNumber, $licenceRegion, AuthenticatedUser $user)
    {
        $params = [
            "drivingLicenceNumber" => $licenceNumber,
            "drivingLicenceRegion" => $licenceRegion
        ];

        $response = $this->customerService->updateLicence(
            $this->userData->getCurrentLoggedUser()->getAccessToken(),
            $user->getUserId(),
            $params
        );

        PHPUnit::assertEquals(HttpResponse::STATUS_CODE_200, $response->getStatusCode());
    }

    /**
     * @Then :user licence should match :licenceNumber
     */
    public function licenceNumbersMatch(AuthenticatedUser $user, $licenceNumber)
    {
        $testerId = $user->getUserId();

        $testerDetails = $this->customerService->helpDeskProfile(
            $this->userData->getCurrentLoggedUser()->getAccessToken(),
            $testerId
        );

        PHPUnit::assertEquals($licenceNumber, $testerDetails->getBody()->getData()[PersonParams::DRIVING_LICENCE]);
    }

    /**
     * @Then :user should not have a licence associated with their account
     */
    public function licenceDoesNotExist(AuthenticatedUser $user)
    {
        $testerDetails = $this->customerService->helpDeskProfile(
            $this->userData->getCurrentLoggedUser()->getAccessToken(),
            $user->getUserId()
        );

        PHPUnit::assertEquals('', $testerDetails->getBody()->getData()[PersonParams::DRIVING_LICENCE]);
    }

    /**
     * @Then :user licence should not match :licenceNumber
     */
    public function theirLicenceShouldNotMatch(AuthenticatedUser $user, $licenceNumber)
    {
        $testerDetails = $this->customerService->helpDeskProfile(
            $this->userData->getCurrentLoggedUser()->getAccessToken(),
            $user->getUserId()
        );

        PHPUnit::assertNotEquals($licenceNumber, $testerDetails->getBody()->getData()[PersonParams::DRIVING_LICENCE]);
    }

    /**
     * @When I delete :user licence
     */
    public function removeUserLicence(AuthenticatedUser $user)
    {
        $response = $this->customerService->deleteLicence(
            $this->userData->getCurrentLoggedUser()->getAccessToken(),
            $user->getUserId()
        );

        PHPUnit::assertEquals(HttpResponse::STATUS_CODE_200, $response->getStatusCode());
    }
}
