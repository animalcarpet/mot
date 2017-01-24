<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Gherkin\Node\TableNode;
use Dvsa\Mot\Behat\Support\Response;
use PHPUnit_Framework_Assert as PHPUnit;
use Dvsa\Mot\Behat\Support\Api\SpecialNotice;
use Dvsa\Mot\Behat\Support\Api\Session;
use Dvsa\Mot\Behat\Support\Api\Session\AuthenticatedUser;
use Dvsa\Mot\Behat\Support\Helper\TestSupportHelper;
use Dvsa\Mot\Behat\Support\Data\SiteData;
use Dvsa\Mot\Behat\Support\Data\UserData;
use Zend\Http\Response as HttpResponse;

class SpecialNoticeContext implements Context
{
    /**
     * @var SpecialNotice
     */
    private $specialNotice;

    /**
     * @var Response
     */
    private $specialNoticeResponse;

    /**
     * @var AuthenticatedUser
     */
    private $tester;

    /**
     * @var AuthenticatedUser
     */
    private $aedm;

    /**
     * @var AuthenticatedUser
     */
    private $areaOffice1user;

    private $siteData;
    private $userData;
    private $specialNoticeId;

    /**
     * @param SpecialNotice $specialNotice
     */
    public function __construct(
        SpecialNotice $specialNotice,
        Session $session,
        SiteData $siteData,
        UserData $userData
    )
    {
        $this->specialNotice = $specialNotice;
        $this->siteData = $siteData;
        $this->userData = $userData;
    }

    /**
     * @When I send a new Special Notice broadcast
     */
    public function iSendANewSpecialNoticeBroadcast()
    {
        $this->specialNotice->sendBroadcast(
            $this->userData->getCurrentLoggedUser()->getAccessToken()
        );
    }

    /**
     * @Then I will see the broadcast was successful
     */
    public function iWillSeeTheBroadcastWasSuccessful()
    {
        $response = $this->specialNotice->getLastResponse();
        PHPUnit::assertTrue($response->getBody()->getData()["success"]);
    }

    /**
     * @When I create a Special Notice with data:
     */
    public function iCreateASpecialNoticeWithData(TableNode $table)
    {
        $hash = $table->getColumnsHash();

        if (count($hash) !== 1) {
            throw new \InvalidArgumentException(sprintf('Expected a single record but got: %d', count($hash)));
        }

        $data = $hash[0];

        $roles = array_map(function ($role) {
            return trim($role);
        }, explode(",", $data["targetRoles"]));

        $data["targetRoles"] = $roles;
        $data["internalPublishDate"] = (new \DateTime($data["internalPublishDate"]))->format("Y-m-d");
        $data["externalPublishDate"] = (new \DateTime($data["externalPublishDate"]))->format("Y-m-d");

        $token = $this->userData->getCurrentLoggedUser()->getAccessToken();
        $this->specialNoticeResponse = $this->specialNotice->createSpecialNotice($token, $data);

        $this->theSpecialNoticeIsCreated();
    }

    /**
     * @Given site with dvsa and vts users roles exists
     */
    public function siteWithDvsaAndVtsUsersRolesExists()
    {
        $site = $this->siteData->create("Popular Garage");
        $this->tester = $this->userData->createTesterAssignedWitSite($site->getId(), "Bob");
        $this->aedm = $this->userData->getAedmByAeId($site->getOrganisation()->getId());
        $this->areaOffice1user = $this->userData->createAreaOffice1User();
    }

    /**
     * @Given I publish Special Notice
     */
    public function iPublishSpecialNotice()
    {
        $token = $this->userData->getCurrentLoggedUser()->getAccessToken();
        $id = $this->specialNoticeResponse->getBody()->getData()["id"];

        $response = $this->specialNotice->publish($token, $id);

        PHPUnit_Framework_Assert::assertEquals(HttpResponse::STATUS_CODE_200, $response->getStatusCode());
    }

    /**
     * @When the Special Notice is broadcast
     */
    public function theSpecialNoticeIsBroadcast()
    {
        $cronUser = $this->userData->createCronUser();
        $response = $this->specialNotice->sendBroadcast($cronUser->getAccessToken());

        PHPUnit_Framework_Assert::assertTrue($response);
    }

    /**
     * @Then users received Special Notice
     */
    public function usersReceivedSpecialNotice()
    {
        $this->assertInternalSpecialNotice();
        $this->assertExternalSpecialNotice();
    }

    private function assertInternalSpecialNotice()
    {
        $dvsaUserSession = $this->areaOffice1user;
        $dvsaUserResponse = $this->specialNotice->getSpecialNotices($dvsaUserSession->getAccessToken(), $dvsaUserSession->getUserId());

        PHPUnit_Framework_Assert::assertEquals(HttpResponse::STATUS_CODE_200, $dvsaUserResponse->getStatusCode());

        $sn = $this->specialNoticeResponse->getBody()->getData();
        $snContentid = $sn["id"];

        $today = new \DateTime("");
        $today->setTime(0,0,0);

        $internalPublishDate = new \DateTime($sn["internalPublishDate"]);
        $internalPublishDate->setTime(0,0,0);

        $dvsaUserSpecialNotices = $dvsaUserResponse->getBody()->getData();
        $foundDvsaSpecialNotice = false;
        foreach ($dvsaUserSpecialNotices as $specialNotice) {
            if ($snContentid === $specialNotice["contentId"]) {
                $foundDvsaSpecialNotice = true;
                break;
            }
        }

        if ($internalPublishDate <= $today) {
            PHPUnit_Framework_Assert::assertTrue($foundDvsaSpecialNotice);
        } else {
            PHPUnit_Framework_Assert::assertFalse($foundDvsaSpecialNotice);
        }
    }

    private function assertExternalSpecialNotice()
    {
        $testerSession = $this->tester;
        $testerResponse = $this->specialNotice->getSpecialNotices($testerSession->getAccessToken(), $testerSession->getUserId());

        $vtsUserSession = $this->aedm;
        $vtsUserResponse = $this->specialNotice->getSpecialNotices($vtsUserSession->getAccessToken(), $vtsUserSession->getUserId());

        PHPUnit_Framework_Assert::assertEquals(HttpResponse::STATUS_CODE_200, $testerResponse->getStatusCode());
        PHPUnit_Framework_Assert::assertEquals(HttpResponse::STATUS_CODE_200, $vtsUserResponse->getStatusCode());

        $sn = $this->specialNoticeResponse->getBody()->getData();
        $snContentid = $sn["id"];

        $today = new \DateTime("");
        $today->setTime(0,0,0);

        $externalPublishDate = new \DateTime($sn["externalPublishDate"]);
        $externalPublishDate->setTime(0,0,0);

        $testerSpecialNotices = $testerResponse->getBody()->getData();
        $foundTesterSpecialNotice = false;
        foreach ($testerSpecialNotices as $specialNotice) {
            if ($snContentid === $specialNotice["contentId"]) {
                $foundTesterSpecialNotice = true;
                break;
            }
        }

        $vtsUserSpecialNotices = $vtsUserResponse->getBody()->getData();
        $foundVtsUserSpecialNotice = false;
        foreach ($vtsUserSpecialNotices as $specialNotice) {
            if ($snContentid === $specialNotice["contentId"]) {
                $foundVtsUserSpecialNotice = true;
                break;
            }
        }

        if ($externalPublishDate <= $today) {
            PHPUnit_Framework_Assert::assertTrue($foundTesterSpecialNotice);
            PHPUnit_Framework_Assert::assertTrue($foundVtsUserSpecialNotice);

        } else {
            PHPUnit_Framework_Assert::assertFalse($foundTesterSpecialNotice);
            PHPUnit_Framework_Assert::assertFalse($foundVtsUserSpecialNotice);
        }
    }

    /**
     * @Then /^the Special Notice is created$/
     */
    public function theSpecialNoticeIsCreated()
    {
        PHPUnit_Framework_Assert::assertNotEmpty($this->specialNoticeResponse->getBody()->getData()['id'], 'Special Notice Id was not returned in response');
        PHPUnit_Framework_Assert::assertTrue(is_int($this->specialNoticeResponse->getBody()->getData()['id']), 'Special Notice Id is not a number');
        PHPUnit_Framework_Assert::assertEquals(HttpResponse::STATUS_CODE_200, $this->specialNoticeResponse->getStatusCode(), 'Incorrect Status Code returned');
    }

    /**
     * @Given Special Notice has been broadcast to testers
     */
    public function specialNoticeHasBeenBroadcastToTesters()
    {
        $data = [];

        $date = (new \DateTime())->format("Y-m-d");

        $data["noticeTitle"] = "Warning!";
        $data["targetRoles"] = ["TESTER-CLASS-4"];
        $data["internalPublishDate"] = $date;
        $data["externalPublishDate"] = $date;

        $schemeUser = $this->userData->createSchemeUser();

        $this->specialNoticeResponse = $this->specialNotice->createSpecialNotice($schemeUser->getAccessToken(), $data);
        $this->theSpecialNoticeIsCreated();

        $id = $this->specialNoticeResponse->getBody()->getData()["id"];
        $this->specialNoticeId = $id;

        $response = $this->specialNotice->publish($schemeUser->getAccessToken(), $id);
        PHPUnit_Framework_Assert::assertEquals(HttpResponse::STATUS_CODE_200, $response->getStatusCode());

        $cronUser = $this->userData->createCronUser();
        $response = $this->specialNotice->sendBroadcast($cronUser->getAccessToken());
        PHPUnit_Framework_Assert::assertTrue($response);
    }

    /**
     * @When Schemeuser removes Special Notice
     */
    public function schemeuserRemovesSpecialNoticeWithTitle()
    {
        $schemeuser = $this->userData->createSchemeUser();
        $response = $this->specialNotice->getAllSpecialNotices($schemeuser->getAccessToken());

        $specialNotice = null;
        $specialNotices = $response->getBody()->getData();
        foreach ($specialNotices as $sn) {
            if ($this->specialNoticeId === $sn["id"]) {
                $specialNotice = $sn;
                break;
            }
        }

        PHPUnit_Framework_Assert::assertNotNull($specialNotice);

        $this->specialNotice->removeSpecialNotices($schemeuser->getAccessToken(), $specialNotice["id"]);
    }

    /**
     * @Then :tester does not see Special Notice
     */
    public function doesNotSeeSpecialNoticeWithTitle(AuthenticatedUser $tester)
    {
        $response = $this->specialNotice->getSpecialNotices($tester->getAccessToken(), $tester->getUserId());

        $specialNotice = null;
        $specialNotices = $response->getBody()->getData();
        foreach ($specialNotices as $sn) {
            if ($this->specialNoticeId === $sn["contentId"]) {
                $specialNotice = $sn;
                break;
            }
        }

        PHPUnit_Framework_Assert::assertNull($specialNotice);
    }
}
