<?php

use Dvsa\Mot\Behat\Support\Data\UserData;
use Dvsa\Mot\Behat\Support\Data\AuthorisedExaminerData;
use Dvsa\Mot\Behat\Support\Api\Session\AuthenticatedUser;
use Dvsa\Mot\Behat\Support\Api\Vts;
use Dvsa\Mot\Behat\Support\Api\AuthorisedExaminer;
use Dvsa\Mot\Behat\Support\Api\Person;
use DvsaCommon\Dto\Site\SiteDto;
use DvsaCommon\Enum\RoleCode;
use Behat\Behat\Context\Context;
use Zend\Http\Response as HttpResponse;
use PHPUnit_Framework_Assert as PHPUnit;

class NominationContext implements Context
{
    private $userData;
    private $vts;
    private $person;
    private $authorisedExaminerData;
    private $authorisedExaminer;

    /**
     * @var AuthenticatedUser
     */
    private $nominatedUser;

    public function __construct(
        UserData $userData,
        Vts $vts,
        Person $person,
        AuthorisedExaminerData $authorisedExaminerData,
        AuthorisedExaminer $authorisedExaminer
    )
    {
        $this->userData = $userData;
        $this->vts = $vts;
        $this->person = $person;
        $this->authorisedExaminerData = $authorisedExaminerData;
        $this->authorisedExaminer = $authorisedExaminer;
    }

    /**
     * @Given I nominate user to TESTER role at :site site
     */
    public function iNominateUserToTesterRole(SiteDto $site)
    {
        $this->nominatedUser = $this->userData->createTester("One Site Tester");
        $this->nominateToSiteRole($site, RoleCode::TESTER);
    }

    /**
     * @Given I nominate user to SITE-MANAGER role at :site site
     */
    public function iNominateUserToSiteManagerRole(SiteDto $site)
    {
        $this->nominatedUser = $this->userData->createTester("Site Tester");
        $this->nominateToSiteRole($site, RoleCode::SITE_MANAGER);
    }

    /**
     * @Given I nominate user to SITE-ADMIN role at :site site
     */
    public function iNominateUserToSiteAdminRole(SiteDto $site)
    {
        $this->nominatedUser = $this->userData->createTester("Site Tester");
        $this->nominateToSiteRole($site, RoleCode::SITE_ADMIN);
    }

    private function nominateToSiteRole(SiteDto $site, $role)
    {
        $token = $this->userData->getCurrentLoggedUser()->getAccessToken();
        $response = $this->vts->nominateToRole($this->nominatedUser->getUserId(), $role, $site->getId(), $token);
        PHPUnit::assertEquals(HttpResponse::STATUS_CODE_200, $response->getStatusCode());
    }

    /**
     * @Then the nominated user has a pending site role :role at :site
     */
    public function theNominatedUserHasAPendingSiteRole($role, SiteDto $site)
    {
        $response = $this->person->getPendingRoles(
            $this->userData->getCurrentLoggedUser()->getAccessToken(),
            $this->nominatedUser->getUserId()
        );

        $siteId = $site->getId();
        $roles = $response->getBody()->getData();
        $siteRoles = $roles["sites"][$siteId]["roles"];

        PHPUnit::assertTrue(in_array($role, $siteRoles), "Site role '$role' should be pending");
    }

    /**
     * @Given I nominate :user to Authorised Examiner Delegate role
     */
    public function iNominateUserToAedRole(AuthenticatedUser $user)
    {
        $this->nominateToOrganisationRole($user, RoleCode::AUTHORISED_EXAMINER_DELEGATE);
    }

    private function nominateToOrganisationRole(AuthenticatedUser $user, $role)
    {
        $aeId = $this->authorisedExaminerData->get()->getId();
        $token = $this->userData->getCurrentLoggedUser()->getAccessToken();
        $response = $this->authorisedExaminer->nominate($user->getUserId(), $role, $aeId, $token);
        PHPUnit::assertEquals(HttpResponse::STATUS_CODE_200, $response->getStatusCode());
    }


}
