<?php
use Behat\Behat\Context\Context;
use Dvsa\Mot\Behat\Support\Data\Exception\UnexpectedResponseStatusCodeException;
use Dvsa\Mot\Behat\Support\Api\Session\AuthenticatedUser;
use Dvsa\Mot\Behat\Support\Api\Person;
use Dvsa\Mot\Behat\Support\Data\AuthorisedExaminerData;
use Dvsa\Mot\Behat\Support\Data\UserData;
use Zend\Http\Response as HttpResponse;
use PHPUnit_Framework_Assert as PHPUnit;

class RoleContext implements Context
{
    private $person;
    private $userData;
    private $authorisedExaminerData;


    public function __construct(
        Person $person,
        UserData $userData,
        AuthorisedExaminerData $authorisedExaminerData
    )
    {
        $this->person = $person;
        $this->userData = $userData;
        $this->authorisedExaminerData = $authorisedExaminerData;
    }

    /**
     * @When I add the role of :role to :user
     * @When :user has the role :role
     */
    public function iAddTheRoleOfToAnotherUser($role, AuthenticatedUser $user)
    {
        $this->person->addPersonRole(
            $this->userData->getCurrentLoggedUser()->getAccessToken(),
            $user->getUserId(),
            $role
        );
    }

    /**
     * @When I try add the role of :role to :user
     */
    public function iTryAddTheRoleOfToAnotherUser($role, AuthenticatedUser $user)
    {
        try {
            $this->iAddTheRoleOfToAnotherUser($role, $user);
        } catch (UnexpectedResponseStatusCodeException $exception) {

        }

        PHPUnit::assertTrue(isset($exception), "Exception not thrown");
        PHPUnit::assertInstanceOf(UnexpectedResponseStatusCodeException::class, $exception);
    }

    /**
     * @When I try to add the role of :role to myself
     */
    public function iAddTheRoleOfRoleToMyself($role)
    {
        try {
            $this->person->addPersonRole(
                $this->userData->getCurrentLoggedUser()->getAccessToken(),
                $this->userData->getCurrentLoggedUser()->getUserId(),
                $role
            );

        } catch (UnexpectedResponseStatusCodeException $exception) {

        }

        PHPUnit::assertTrue(isset($exception), "Exception not thrown");
        PHPUnit::assertInstanceOf(UnexpectedResponseStatusCodeException::class, $exception);
    }

    /**
     * @When I try to remove the role of :role from myself
     */
    public function iRemoveTheRoleOfRoleFromMyself($role)
    {
        try {
            $this->person->removePersonRole(
                $this->userData->getCurrentLoggedUser()->getAccessToken(),
                $this->userData->getCurrentLoggedUser()->getUserId(),
                $role
            );

        } catch (UnexpectedResponseStatusCodeException $exception) {

        }

        PHPUnit::assertTrue(isset($exception), "Exception not thrown");
        PHPUnit::assertInstanceOf(UnexpectedResponseStatusCodeException::class, $exception);
    }

    /**
     * @When I remove the role of :role from :user
     */
    public function iRemoveTheRoleOfRoleFromAUserRole($role, AuthenticatedUser $user)
    {
        $response = $this->person->removePersonRole(
            $this->userData->getCurrentLoggedUser()->getAccessToken(),
            $user->getUserId(),
            $role
        );

        PHPUnit::assertEquals(HttpResponse::STATUS_CODE_200, $response->getStatusCode(), "Unable to remove role '{$role}'");
    }

    /**
     * @Then :user RBAC will have the role :role
     */
    public function theUserSRBACWillHaveTheRole(AuthenticatedUser $user, $role)
    {
        $rbacResponse = $this->person->getPersonRBAC(
            $user->getAccessToken(),
            $user->getUserId()
        );

        $rolesAssigned = $rbacResponse->getBody()->toArray();
        $rolesAssigned = $rolesAssigned['data']['normal']['roles'];
        PHPUnit::assertTrue(in_array($role, $rolesAssigned), sprintf("Role %s has not been assigned", $role));
    }

    /**
     * @Then :user RBAC will not have the role :role
     *
     */
    public function theUserSRBACWillNotHaveTheRole(AuthenticatedUser $user, $role)
    {
        $rbacResponse = $this->person->getPersonRBAC(
            $user->getAccessToken(),
            $user->getUserId()
        );

        $rolesAssigned = $rbacResponse->getBody()->toArray();
        $rolesAssigned = $rolesAssigned['data']['normal']['roles'];

        PHPUnit::assertFalse(in_array($role, $rolesAssigned), sprintf("Role %s has been assigned", $role));
    }

    /**
     * @Then my RBAC will still have the role :role
     * @Given my RBAC has the role :role
     */
    public function myRBACWillHaveTheRole($role)
    {
        $rbacResponse = $this->person->getPersonRBAC(
            $this->userData->getCurrentLoggedUser()->getAccessToken(),
            $this->userData->getCurrentLoggedUser()->getUserId()
        );

        $rolesAssigned = $rbacResponse->getBody()->toArray();
        $rolesAssigned = $rolesAssigned['data']['normal']['roles'];
        PHPUnit::assertTrue(in_array($role, $rolesAssigned), sprintf("Role %s has not been assigned", $role));
    }

    /**
     * @Then my RBAC will not contain the role :role
     */
    public function myRBACWillNotContainTheRole($role)
    {
        $rbacResponse = $this->person->getPersonRBAC(
            $this->userData->getCurrentLoggedUser()->getAccessToken(),
            $this->userData->getCurrentLoggedUser()->getUserId()
        );

        $rolesAssigned = $rbacResponse->getBody()->toArray();
        $rolesAssigned = $rolesAssigned['data']['normal']['roles'];
        PHPUnit::assertFalse(in_array($role, $rolesAssigned), sprintf("Role %s has been assigned", $role));
    }

    /**
     * @Then the nominated user :user has a pending organisation role :role
     */
    public function theNominatedUserHasAPendingOrganisationRole(AuthenticatedUser $user, $role)
    {
        $response = $this->person->getPendingRoles(
            $this->userData->getCurrentLoggedUser()->getAccessToken(),
            $user->getUserId()
        );

        $aeId = $this->authorisedExaminerData->get()->getId();
        $roles = $response->getBody()->getData();

        $orgRoles = $roles["organisations"][$aeId]["roles"];

        PHPUnit::assertTrue(in_array($role, $orgRoles), "Organisation role '$role' should be pending");
    }

}