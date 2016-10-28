<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Dvsa\Mot\Behat\Support\Api\Session;
use Dvsa\Mot\Behat\Support\Data\AuthorisedExaminerData;
use Dvsa\Mot\Behat\Support\Data\Collection\DataCollection;
use Dvsa\Mot\Behat\Support\Data\Params\SiteParams;
use Dvsa\Mot\Behat\Support\Data\SiteData;
use Dvsa\Mot\Behat\Support\Data\UserData;
use DvsaCommon\Dto\Organisation\OrganisationDto;
use DvsaCommon\Dto\Site\SiteDto;
use PHPUnit_Framework_Assert as PHPUnit;
use Zend\Http\Response as HttpResponse;

class AuthorisedExaminerContext implements Context
{
    const NEW_AE_NAME = "Need 4 Speed Ltd";
    const SITE_NAME_FOR_LINKING = "Garage number 4";

    /**
     * @var DataCollection
     */
    private $foundedAuthorisedExaminers;

    private $authorisedExaminerData;

    private $userData;

    private $siteData;

    public function __construct(
        AuthorisedExaminerData $authorisedExaminerData,
        UserData $userData,
        SiteData $siteData
    )
    {
        $this->authorisedExaminerData = $authorisedExaminerData;
        $this->userData = $userData;
        $this->siteData = $siteData;
    }

    /**
     * @BeforeScenario
     */
    public function setUp(BeforeScenarioScope $scope)
    {
        $this->foundedAuthorisedExaminers = new DataCollection(OrganisationDto::class);
    }

    /**
     * @When I search for an existing Authorised Examiner
     * @When I search for an existing Authorised Examiner with name :name
     */
    public function iSearchForAnAuthorisedExaminer($name = AuthorisedExaminerData::DEFAULT_NAME)
    {
        $ae = $this->authorisedExaminerData->get($name);
        $ref = $ae->getAuthorisedExaminerAuthorisation()->getAuthorisedExaminerRef();
        $ae = $this->authorisedExaminerData->search($this->userData->getCurrentLoggedUser(), $ref);

        $this->foundedAuthorisedExaminers->add($ae, $name);
    }

    /**
     * @When I search for an Invalid Authorised Examiner with number :number
     */
    public function iSearchForAnInvalidAuthorisedExaminer($number)
    {
        try {
            $ae = $this->authorisedExaminerData->search($this->userData->getCurrentLoggedUser(), $number);
        } catch (\Exception $e) {
            $ae = null;
        }

        PHPUnit::assertNull($ae);
    }

    /**
     * @Then I will see the Authorised Examiner's details
     * @Then I will see the Authorised Examiner's details with name :name
     */
    public function theAuthorisedExaminerDetailsAreReturned($name = AuthorisedExaminerData::DEFAULT_NAME)
    {
        $ae = $this->authorisedExaminerData->get($name);
        $ref = $ae->getAuthorisedExaminerAuthorisation()->getAuthorisedExaminerRef();

        $collection = $this->foundedAuthorisedExaminers->filter(function (OrganisationDto $ae) use ($ref) {
            return $ae->getAuthorisedExaminerAuthorisation()->getAuthorisedExaminerRef() === $ref;
        });

        PHPUnit::assertCount(1, $collection);
    }

    /**
     * @Then I am informed that Authorised Examiner with number :number does not exist
     */
    public function iAmInformedThatAuthorisedExaminerDoesNotExist($number)
    {
        $collection = $this->foundedAuthorisedExaminers->filter(function (OrganisationDto $ae) use ($number) {
            return $ae->getAuthorisedExaminerAuthorisation()->getAuthorisedExaminerRef() === $number;
        });

        PHPUnit::assertCount(0, $collection);
    }

    /**
     * @Then I should be able to create a new Authorised Examiner
     * @Then I should be able to create a new Authorised Examiner with name :name
     */
    public function iCreateANewAuthorisedExaminer($name = self::NEW_AE_NAME)
    {
        $ae = $this->authorisedExaminerData->createByUser($this->userData->getCurrentLoggedUser(), $name);

        PHPUnit::assertInstanceOf(OrganisationDto::class, $ae);
    }

    /**
     * @Given There is an Authorised Examiner with name :name
     */
    public function thereIsAnAuthorisedExaminerWithName($name)
    {
        $ae = $this->authorisedExaminerData->create($name);

        PHPUnit::assertInstanceOf(OrganisationDto::class, $ae);
    }

    /**
     * @Then /^I should be able to approve this Authorised Examiner$/
     * @Then I should be able to approve Authorised Examiner with name :name
     */
    public function approveAnAuthorisedExaminer($name = self::NEW_AE_NAME)
    {
        $ae = $this->authorisedExaminerData->get($name);
        $this->authorisedExaminerData->updateStatusToApprove($ae, $this->userData->getCurrentLoggedUser());
    }

    /**
     * @Then /^I should be able to create a site for linking$/
     * @Then I should be able to create a unassociated site with name :name
     */
    public function iShouldBeAbleToCreateASiteForLinking($name = self::SITE_NAME_FOR_LINKING)
    {
        $site = $this->siteData->createUnassociatedSiteByUser($this->userData->getCurrentLoggedUser(), [SiteParams::NAME => $name]);

        PHPUnit::assertInstanceOf(SiteDto::class, $site);
    }

    /**
     * @Then /^I should be able to link the new AE and site together$/
     */
    public function iShouldBeAbleToLinkTheNewAEAndSiteTogether()
    {
        $this->authorisedExaminerData->linkAuthorisedExaminerWithSiteByUser(
            $this->userData->getCurrentLoggedUser(),
            $this->authorisedExaminerData->get(self::NEW_AE_NAME),
            $this->siteData->get(self::SITE_NAME_FOR_LINKING)
        );
    }

    /**
     * @When site :site is unlinked from AE :ae on :endDate
     */
    public function siteIsUnlinkedFromAeFrom(SiteDto $site, OrganisationDto $ae, \DateTime $endDate)
    {
        $this->authorisedExaminerData->unlinkSiteFromAuthorisedExaminer($ae, $site);
        $this->siteData->changeEndDateOfAssociation($ae->getId(), $site->getId(), $endDate);
    }

    /**
     * @When site :site is linked to AE :ae on :startDate
     */
    public function siteIsLinkedToAeFrom(SiteDto $site, OrganisationDto $ae, \DateTime $startDate)
    {
        $this->authorisedExaminerData->linkAuthorisedExaminerWithSite($ae, $site);
        $this->siteData->changeAssociatedDate($ae->getId(), $site->getId(), $startDate);
    }
}
