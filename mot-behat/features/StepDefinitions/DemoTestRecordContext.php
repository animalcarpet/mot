<?php

use Dvsa\Mot\Behat\Support\Data\UserData;
use Dvsa\Mot\Behat\Support\Data\SiteData;
use Dvsa\Mot\Behat\Support\Data\Params\PersonParams;
use Dvsa\Mot\Behat\Support\Api\Tester;
use Behat\Behat\Context\Context;
use Dvsa\Mot\Behat\Support\Data\Exception\UnexpectedResponseStatusCodeException;
use PHPUnit_Framework_Assert as PHPUnit;

class DemoTestRecordContext implements Context
{
    private $userData;
    private $siteData;
    private $tester;

    private $testerQualificationResponse;

    public function __construct(UserData $userData, SiteData $siteData, Tester $tester)
    {
        $this->userData = $userData;
        $this->siteData = $siteData;
        $this->tester = $tester;
    }

    /**
     * @When I change a :testerName group :group tester qualification status from :authForTestingMotStatus to Qualified
     */
    public function iChangeAUsersGroupTesterQualificationStatusFromToQualified($testerName, $group, $authForTestingMotStatus)
    {
        $params = [
            PersonParams::SITE_IDS => [$this->siteData->get()->getId()],
            "qualifications"=> [
                "A"=> $authForTestingMotStatus,
                "B"=> $authForTestingMotStatus
            ]
        ];
        $tester = $this->userData->createTesterWithParams($params, $testerName);

        $this->testerQualificationResponse = $this->tester->updateTesterQualification(
            $this->userData->getCurrentLoggedUser()->getAccessToken(),
            $group,
            $tester->getUserId()
        );
    }

    /**
     * @When I try change a :testerName group :group tester qualification status from :authForTestingMotStatus to Qualified
     */
    public function iTryChangeAUsersGroupTesterQualificationStatusFromToQualified($testerName, $group, $authForTestingMotStatus)
    {
        try {
            $this->iChangeAUsersGroupTesterQualificationStatusFromToQualified($testerName, $group, $authForTestingMotStatus);
        } catch (UnexpectedResponseStatusCodeException $e) {
            $exception = $e;
            $this->testerQualificationResponse = $this->tester->getLastResponse();
        }

        PHPUnit::assertTrue(isset($exception), "Exception not thrown");
        PHPUnit::assertInstanceOf(UnexpectedResponseStatusCodeException::class, $exception);
    }

    /**
     * @Then an error occurs
     */
    public function anErrorOccurs()
    {
        $errors = $this->testerQualificationResponse->getBody()->getErrors();
        PHPUnit::assertCount(1, $errors);
    }
}