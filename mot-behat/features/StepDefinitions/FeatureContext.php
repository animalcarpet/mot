<?php

use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Dvsa\Mot\Behat\Support\Data\Collection\SharedDataCollection;
use Dvsa\Mot\Behat\Support\Data\Transformer\AeNameToOrganisationDtoTransformer;
use Dvsa\Mot\Behat\Support\Data\Transformer\SiteNameToSiteDtoTransformer;
use Dvsa\Mot\Behat\Support\Data\Transformer\TypeConversion;
use Dvsa\Mot\Behat\Support\Data\Transformer\UsernameToAuthenticatedUserTransformer;
use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\AfterScenarioScope;
use Behat\Behat\Hook\Scope\AfterStepScope;
use Dvsa\Mot\Behat\Support\Api\Session;
use Dvsa\Mot\Behat\Support\History;
use Dvsa\Mot\Behat\Support\Scope\BeforeBehatScenarioScope;
use PHPUnit_Framework_Assert as PHPUnit;

class FeatureContext implements Context
{
    use AeNameToOrganisationDtoTransformer, SiteNameToSiteDtoTransformer, TypeConversion, UsernameToAuthenticatedUserTransformer;

    /**
     * @var History
     */
    private $history;

    /**
     * @param History $history
     */
    public function __construct(History $history)
    {
        $this->history = $history;
    }

    /**
     * @AfterStep
     */
    public function debug(AfterStepScope $scope)
    {
        if ($scope->getTestResult()->isPassed() || $scope->getTestResult()->getResultCode() == 2) {
            return;
        }

        foreach ($this->history->getAllResponses() as $response) {
            echo "> > >\n";
            echo preg_replace('/^/sm', '      ', $response->getRequest())."\n";
            echo "< < <\n";
            echo preg_replace('/^/sm', '      ', $response)."\n";
        }
    }

    /**
     * @AfterScenario
     */
    public function clearHistory(AfterScenarioScope $scope)
    {
        $this->history->clear();
    }

    /**
     * @BeforeScenario
     */
    public function cleanupContexts(BeforeScenarioScope $scope)
    {
        (new ContextCleanup())->cleanup($scope->getEnvironment()->getContexts());
    }

    /**
     * @BeforeScenario
     */
    public function setUp(BeforeScenarioScope $scope)
    {
        BeforeBehatScenarioScope::set($scope);
    }

    /**
     * @AfterScenario
     */
    public function cleanupSharedData(AfterScenarioScope $scope)
    {
        SharedDataCollection::clear();
    }

    /**
     * @Then /^I should receive an Unauthorised response$/
     *
     * @deprecated this is only temporarly here and will be removed as soon as scenarios are reworded to not include implementation details
     */
    public function iShouldReceiveAnUnauthorisedResponse()
    {
        $response = $this->history->getLastResponse();

        PHPUnit::assertEquals(401, $response->getStatusCode(), 'Did not receive 401 Unauthorised response');
    }

    /**
     * @Given /^I should not see any data in the response body$/
     *
     * @deprecated this is only temporarly here and will be removed as soon as scenarios are reworded to not include implementation details
     */
    public function iShouldNotSeeAnyDataInTheResponseBody()
    {
        $response = $this->history->getLastResponse();

        PHPUnit::assertFalse(isset($response->getBody()['data']), 'Data key found in response body');
    }

    /**
     * @Then /^I should receive a Forbidden response$/
     *
     * @deprecated this is only temporarly here and will be removed as soon as scenarios are reworded to not include implementation details
     */
    public function iShouldReceiveAForbiddenResponse()
    {
        $response = $this->history->getLastResponse();

        PHPUnit::assertEquals(403, $response->getStatusCode(), 'Did not receive 403 Forbidden response');
    }

    /**
     * @Then /^I should receive a Bad Request response$/
     *
     * @deprecated this is only temporarly here and will be removed as soon as scenarios are reworded to not include implementation details
     */
    public function iShouldReceiveABadRequestResponse()
    {
        $response = $this->history->getLastResponse();

        PHPUnit::assertEquals(400, $response->getStatusCode(), 'Did not receive 400 Bad Request response');
    }


    /**
     * @Then /^I should receive an Unprocessable Entity response$/
     *
     * @deprecated this is only temporarly here and will be removed as soon as scenarios are reworded to not include implementation details
     */
    public function iShouldReceiveAnUnprocessableEntityResponse()
    {
        $response = $this->history->getLastResponse();

        PHPUnit::assertEquals(422, $response->getStatusCode(), 'Did not receive 422 Unprocessable Entity response');
    }

    /**
     * @Then /^I should receive a validation error "(.*)" "(.*)"$/
     */
    public function iShouldReceiveAValidationError($key, $message)
    {
        $response = $this->history->getLastResponse();

        if (!empty($key) && !empty($message)) {
            try {
                $returnedMessage = $response->getBody()
                    ->offsetGet('errors')
                    ->offsetGet('problem')
                    ->offsetGet('validation_messages')
                    ->offsetGet($key);

                PHPUnit::assertEquals($message, $returnedMessage);
            } catch (\LogicException $e) {
                PHPUnit::fail('Validation message with key ' . $key . ' not found in response');
            }
        }
    }

    /**
     * @Then I should receive a Success response
     */
    public function iShouldReceiveASuccessResponse()
    {
        $response = $this->history->getLastResponse();
        PHPUnit::assertEquals(200, $response->getStatusCode(), 'Did not receive 200 OK response');
    }

    /**
     * @Then /^I should receive the response code ([0-9]+)$/
     * @param $code
     */
    public function iShouldReceiveTheResponseCode($code)
    {
        $responseCode = $this->history->getLastResponse()->getStatusCode();
        PHPUnit::assertEquals($code, $responseCode, 'Did not receive ' . $code . ' response code');
    }
}
