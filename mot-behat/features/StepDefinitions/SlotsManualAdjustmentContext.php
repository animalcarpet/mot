<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Dvsa\Mot\Behat\Support\Api\SlotPurchase;
use Dvsa\Mot\Behat\Support\Response;
use PHPUnit_Framework_Assert as PHPUnit;
use Dvsa\Mot\Behat\Support\Helper\TestSupportHelper;
use TestSupport\Service\AEService;
use Dvsa\Mot\Behat\Support\Data\AuthorisedExaminerData;
use Dvsa\Mot\Behat\Support\Data\UserData;
use Zend\Http\Response as HttpResponse;

class SlotsManualAdjustmentContext implements Context
{
    const AE_NAME = "Slots AE";

    private $testSupportHelper;

    /**
     * @var SlotPurchase
     */
    private $slotPurchaseApi;

    /**
     * @var Response
     */
    private $response;

    /**
     * @var AEService
     */
    private $aeService;

    private $authorisedExaminerData;

    private $userData;

    /**
     * SlotsManualAdjustmentContext constructor.
     * @param SlotPurchase $slotPurchaseApi
     * @param TestSupportHelper $testSupportHelper
     */
    public function __construct(
        SlotPurchase $slotPurchaseApi,
        TestSupportHelper $testSupportHelper,
        AuthorisedExaminerData $authorisedExaminerData,
        UserData $userData
    )
    {
        $this->slotPurchaseApi = $slotPurchaseApi;
        $this->authorisedExaminerData = $authorisedExaminerData;
        $this->userData = $userData;
        $this->testSupportHelper = $testSupportHelper;
    }

    /**
     * @BeforeScenario @manual-adjustments
     */
    public function setUp(BeforeScenarioScope $scope)
    {
        $this->aeService = $this->testSupportHelper->getAeService();
    }

    /**
     *  @Given /^An AE has a slot balance of (.*)$/
     */
    public function anAeHasASlotBalanceOf($initialBalance)
    {
        $this->authorisedExaminerData->createWithCustomSlots($initialBalance, self::AE_NAME);
    }

    /**
     *  @Given An AE requires a manual slot balance adjustment
     */
    public function anAeRequiresAManualAdjustment()
    {
        $this->authorisedExaminerData->create(self::AE_NAME);
    }

    /**
     * @When I submit a top-up manual adjustment with negative type
     */
    public function iSubmitATopUpManualAdjustmentWithNegativeType()
    {
        $token = $this->userData->getCurrentLoggedUser()->getAccessToken();
        $this->response = $this->slotPurchaseApi->makeManualAdjustment(
            $token,
            $this->authorisedExaminerData->get(self::AE_NAME)->getId(),
            SlotPurchase::MANUAL_ADJUSTMENT_TYPE_NEGATIVE,
            'R105',
            null,
            15
        );
    }

    /**
     * @When I submit a manual adjustment with no reason
     */
    public function iSubmitAManualAdjustmentWithNoReason()
    {
        $token = $this->userData->getCurrentLoggedUser()->getAccessToken();
        $this->response = $this->slotPurchaseApi->makeManualAdjustment(
            $token,
            $this->authorisedExaminerData->get(self::AE_NAME),
            SlotPurchase::MANUAL_ADJUSTMENT_TYPE_POSITIVE,
            null,
            'comment',
            15
        );
    }

    /**
     * /**
     * @When /^I submit a valid (negative|positive) manual adjustment of (.*) slots$/
     */
    public function iSubmitAValidTypeManualAdjustmentWithNumberOfSlots($type, $numberOfSlots)
    {
        $slots = intval($numberOfSlots);
        $token = $this->userData->getCurrentLoggedUser()->getAccessToken();
        $this->response = $this->slotPurchaseApi->makeManualAdjustment(
            $token,
            $this->authorisedExaminerData->get(self::AE_NAME)->getId(),
            $type,
            'MOT01',
            "Test comment",
            $slots
        );
    }

    /**
     * /**
     * @When /^I submit a (negative|positive) manual adjustment with (.*) slots$/
     */
    public function iSubmitAnInvalidManualAdjustmentWithNumberOfSlots($type, $numberOfSlots)
    {
        $slots = intval($numberOfSlots);
        $token = $this->userData->getCurrentLoggedUser()->getAccessToken();
        $this->response = $this->slotPurchaseApi->makeManualAdjustment(
            $token,
            $this->authorisedExaminerData->get(self::AE_NAME),
            $type,
            'MOT01',
            "Test comment",
            $slots
        );
    }

    /**
     * @Then /^the AE slot balance should be updated to (.*)$/
     */
    public function aeSlotBalanceShouldBeUpdatedTo($updatedBalance)
    {
        $actual = (int) $this->aeService->getSlotBalanceForAE($this->authorisedExaminerData->get(self::AE_NAME)->getId());
        PHPUnit::assertSame($updatedBalance, $actual);
    }

    /**
     * @Then /^I should see the validation error "(.*)"$/
     */
    public function iShouldSeeTheValidationMessage($message)
    {
        if (!$this->response instanceof Response) {
            throw new \Exception('Response object must be of type ' . Response::class);
        }

        $body = $this->response->getBody()->toArray();
        PHPUnit::assertEquals(HttpResponse::STATUS_CODE_200, $this->response->getStatusCode());
        PHPUnit::assertArrayHasKey('data', $body);
        PHPUnit::assertArrayHasKey('errors', $body['data']);

        $foundError = false;

        foreach($body['data']['errors'] as $error) {
            if($error == $message) {
                $foundError = true;
            }
        }
        PHPUnit::assertTrue($foundError, 'Error Message not found: ' . $message);
    }
}
