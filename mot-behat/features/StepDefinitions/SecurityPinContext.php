<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Dvsa\Mot\Behat\Support\Api\SecurityPin;
use Dvsa\Mot\Behat\Support\Api\Session;
use Dvsa\Mot\Behat\Support\Data\SiteData;
use PHPUnit_Framework_Assert as PHPUnit;
use Dvsa\Mot\Behat\Support\Helper\TestSupportHelper;
use Zend\Http\Response as HttpResponse;
use Dvsa\Mot\Behat\Support\Data\Params\PersonParams;

/**
 * Class ContingencyContext.
 */
class SecurityPinContext implements Context
{
    /**
     * @var SecurityPin
     */
    private $securityPin;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var TestSupportHelper
     */
    private $testSupportHelper;

    /**
     * @var SiteData
     */
    private $siteData;

    /**
     * @var SessionContext
     */
    private $sessionContext;

    /**
     * @var Response
     */
    private $resetPinResponse;

    /**
     * @param SecurityPin $securityPin
     * @param Session     $session
     */
    public function __construct(
        SecurityPin $securityPin,
        Session $session,
        TestSupportHelper $testSupportHelper,
        SiteData $siteData
    )
    {
        $this->securityPin = $securityPin;
        $this->session = $session;
        $this->testSupportHelper = $testSupportHelper;
        $this->siteData = $siteData;
    }

    /**
     * @BeforeScenario
     */
    public function gatherContexts(BeforeScenarioScope $scope)
    {
        $this->sessionContext = $scope->getEnvironment()->getContext(SessionContext::class);
    }

    /**
     * @When /^I request a new security pin$/
     */
    public function iRequestANewSecurityPin()
    {
        $userId = $this->sessionContext->getCurrentUserIdOrNull();
        $accessToken = $this->sessionContext->getCurrentAccessTokenOrNull();
        $this->resetPinResponse = $this->securityPin->resetPin($userId, $accessToken);
    }

    /**
     * @Given /^the generated pin should be a number$/
     */
    public function theGeneratedPinShouldBeANumber()
    {
        $pin = $this->resetPinResponse->getBody()->getData()['pin'];
        PHPUnit::assertTrue(is_numeric($pin), 'Security pin is not a number.');
    }

    /**
     * @When /^I request a new security pin for a (.*) user$/
     */
    public function iRequestANewSecurityPinForAUser($role)
    {
        if ($role == 'ao1') {
            $areaOffice1Service = $this->testSupportHelper->getAreaOffice1Service();
            $user = $areaOffice1Service->create([]);
        } elseif ($role == 'ao2') {
            $areaOffice2Service = $this->testSupportHelper->getAreaOffice2Service();
            $user = $areaOffice2Service->create([]);
        } elseif ($role == 'tester') {
            $testerService = $this->testSupportHelper->getTesterService();
            $user = $testerService->create([PersonParams::SITE_IDS => [$this->siteData->get()->getId()]]);
        } elseif ($role == 'csco') {
            $cscoService = $this->testSupportHelper->getCscoService();
            $user = $cscoService->create([]);
        } else {
            throw new \InvalidArgumentException('Role ' . $role . ' has not been implemented');
        }

        $user = $this->session->startSession($user->data[PersonParams::USERNAME], $user->data[PersonParams::PASSWORD]);

        $accessToken = $this->sessionContext->getCurrentAccessToken();
        $this->resetPinResponse = $this->securityPin->resetPin($user->getUserId(), $accessToken);
    }

    /**
     * @Then /^I should not receive a new security pin$/
     */
    public function iShouldNotReceiveANewSecurityPin()
    {
        PHPUnit::assertEquals($this->resetPinResponse->getBody()->getErrors()['message'], 'Can only reset your own PIN');
    }

    /**
     * @Given /^the generated pin should be (\d+) digits long$/
     * @param $length
     */
    public function theGeneratedPinShouldBeDigitsLong($length)
    {
        PHPUnit::assertEquals(HttpResponse::STATUS_CODE_200, $this->resetPinResponse->getStatusCode(), 'Did not receive 200 OK response');

        $pin = $this->resetPinResponse->getBody()->getData()['pin'];
        PHPUnit::assertEquals($length, strlen($pin), 'Pin is not ' . $length . ' digits long.');
    }
}
