<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Dvsa\Mot\Behat\Datasource\Random;
use Dvsa\Mot\Behat\Support\Api\AccountClaim;
use Dvsa\Mot\Behat\Support\Data\Params\PersonParams;
use TestSupport\Service\AccountService;
use PHPUnit_Framework_Assert as PHPUnit;

class AccountClaimContext implements Context
{
    /**
     * @var AccountClaim
     */
    private $accountClaim;

    /**
     * @var SessionContext
     */
    private $sessionContext;

    /**
     * @param AccountClaim $accountClaim
     */
    public function __construct(AccountClaim $accountClaim)
    {
        $this->accountClaim = $accountClaim;
    }

    /**
     * @BeforeScenario
     */
    public function gatherContexts(BeforeScenarioScope $scope)
    {
        $this->sessionContext = $scope->getEnvironment()->getContext(SessionContext::class);
    }

    public function myAccountHasNotYetBeenClaimed()
    {
        if (!$this->isAccountClaimRequired()) {
            throw new Exception('Expected an unclaimed account, but it was already claimed');
        }
    }

    /**
     * @When I claim my Account
     */
    public function iClaimMyAccount()
    {
        $account = [
            PersonParams::EMAIL => 'bo@didley.co',
            PersonParams::SECURITY_QUESTION_ONE_ID => AccountService::SECURITY_QUESTION_ID_FIRST_KISS,
            PersonParams::SECURITY_ANSWER_ONE => 'Barby',
            PersonParams::SECURITY_QUESTION_TWO_ID => AccountService::SECURITY_QUESTION_ID_NAME_OF_DOG,
            PersonParams::SECURITY_ANSWER_TWO=> 'Kingston',
            PersonParams::EMAIL_OPT_OUT => false,
            PersonParams::PASSWORD => Random::password(),
        ];

        $this->accountClaim->updateAccountClaim($this->sessionContext->getCurrentAccessToken(), $this->sessionContext->getCurrentUserId(), $account);
    }

    /**
     * @Then I should not be able to test vehicles
     */
    public function iShouldNotBeAbleToTestVehicles()
    {
        PHPUnit::assertTrue($this->isAccountClaimRequired(), 'Account is already claimed');
    }

    /**
     * @Then I should be able to test vehicles
     */
    public function iShouldBeAbleToTestVehicles()
    {
        PHPUnit::assertFalse($this->isAccountClaimRequired(), 'Account was not claimed');
    }

    /**
     * @return bool
     */
    private function isAccountClaimRequired()
    {
        $response = $this->accountClaim->getIdentityData($this->sessionContext->getCurrentAccessToken());

        return (bool) $response->getBody()->getData()['user']['isAccountClaimRequired'];
    }
}
