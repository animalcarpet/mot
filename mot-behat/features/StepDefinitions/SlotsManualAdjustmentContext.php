<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Dvsa\Mot\Behat\Support\Api\SlotPurchase;
use Dvsa\Mot\Behat\Support\Response;
use PHPUnit_Framework_Assert as PHPUnit;
use Dvsa\Mot\Behat\Support\Helper\TestSupportHelper;
use TestSupport\Service\AEService;

class SlotsManualAdjustmentContext implements Context
{
    /**
     * @var SessionContext
     */
    private $sessionContext;

    /**
     * @var AuthorisedExaminerContext
     */
    private $authorisedExaminerContext;

    /**
     * @var SlotPurchase
     */
    private $slotPurchaseApi;

    /**
     * @var Response
     */
    private $response;

    private $authorisedExaminerService;

    /**
     * @var TestSupportHelper
     */
    private $testSupportHelper;

    /**
     * @var AEService
     */
    private $aeService;

    private $authorisedExaminer;

    /**
     * SlotsManualAdjustmentContext constructor.
     * @param SlotPurchase $slotPurchaseApi
     * @param TestSupportHelper $testSupportHelper
     */
    public function __construct(SlotPurchase $slotPurchaseApi, TestSupportHelper $testSupportHelper)
    {
        $this->slotPurchaseApi = $slotPurchaseApi;
        $this->testSupportHelper = $testSupportHelper;
    }

    /**
     * @BeforeScenario @manual-adjustments
     */
    public function gatherContexts(BeforeScenarioScope $scope)
    {
        $this->sessionContext = $scope->getEnvironment()->getContext(SessionContext::class);
        $this->sessionContext->iAmLoggedInAsAFinanceUser();
        $this->aeService = $this->testSupportHelper->getAeService();
        $this->authorisedExaminerContext = $scope->getEnvironment()->getContext(AuthorisedExaminerContext::class);
    }

    /**
     * @When I submit a valid manual adjustment
     */
    public function iSubmitAValidManualAdjustment()
    {
        PHPUnit::assertEquals(['id', 'aeRef', 'aeName'], array_keys($this->authorisedExaminer));

        $token = $this->sessionContext->getCurrentAccessToken();
        $this->response = $this->slotPurchaseApi->makeManualAdjustment(
            $token,
            $this->authorisedExaminer['id'],
            SlotPurchase::MANUAL_ADJUSTMENT_TYPE_POSITIVE,
            'R101',
            null,
            15
        );
    }


    /**
     *  @Given /^An AE has a slot balance of (.*)$/
     */
    public function anAeHasASlotBalanceOf($initialBalance)
    {
        $this->authorisedExaminer = $this->authorisedExaminerContext->createAE($initialBalance);
        PHPUnit::assertEquals(['id', 'aeRef', 'aeName'], array_keys($this->authorisedExaminer));
    }

    /**
     *  @Given An AE requires a manual slot balance adjustment
     */
    public function anAeRequiresAManualAdjustment()
    {
        $this->authorisedExaminer = $this->authorisedExaminerContext->createAE();
        PHPUnit::assertEquals(['id', 'aeRef', 'aeName'], array_keys($this->authorisedExaminer));
    }

    /**
     * @When I submit a top-up manual adjustment with negative type
     */
    public function iSubmitATopUpManualAdjustmentWithNegativeType()
    {
        PHPUnit::assertEquals(['id', 'aeRef', 'aeName'], array_keys($this->authorisedExaminer));

        $token = $this->sessionContext->getCurrentAccessToken();
        $this->response = $this->slotPurchaseApi->makeManualAdjustment(
            $token,
            $this->authorisedExaminer['id'],
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
        PHPUnit::assertEquals(['id', 'aeRef', 'aeName'], array_keys($this->authorisedExaminer));

        $token = $this->sessionContext->getCurrentAccessToken();
        $this->response = $this->slotPurchaseApi->makeManualAdjustment(
            $token,
            $this->authorisedExaminer['id'],
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
        PHPUnit::assertEquals(['id', 'aeRef', 'aeName'], array_keys($this->authorisedExaminer));

        $slots = intval($numberOfSlots);
        $token = $this->sessionContext->getCurrentAccessToken();
        $this->response = $this->slotPurchaseApi->makeManualAdjustment(
            $token,
            $this->authorisedExaminer['id'],
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
        PHPUnit::assertEquals(['id', 'aeRef', 'aeName'], array_keys($this->authorisedExaminer));

        $slots = intval($numberOfSlots);
        $token = $this->sessionContext->getCurrentAccessToken();
        $this->response = $this->slotPurchaseApi->makeManualAdjustment(
            $token,
            $this->authorisedExaminer['id'],
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
        PHPUnit::assertEquals(['id', 'aeRef', 'aeName'], array_keys($this->authorisedExaminer));
        $actual = $this->aeService->getSlotBalanceForAE($this->authorisedExaminer['id']);
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
        PHPUnit::assertEquals(200, $this->response->getStatusCode());
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
