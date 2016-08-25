package uk.gov.dvsa.ui.feature.journey.roles;

import org.testng.annotations.BeforeMethod;
import org.testng.annotations.DataProvider;
import org.testng.annotations.Test;
import uk.gov.dvsa.domain.model.AeDetails;
import uk.gov.dvsa.domain.model.Site;
import uk.gov.dvsa.domain.model.User;
import uk.gov.dvsa.helper.RandomDataGenerator;
import uk.gov.dvsa.ui.DslTest;
import uk.gov.dvsa.ui.pages.dvsa.RolesAndAssociationsPage;
import uk.gov.dvsa.ui.pages.dvsa.UserSearchPage;
import uk.gov.dvsa.ui.pages.dvsa.UserSearchResultsPage;

import java.io.IOException;
import java.net.URISyntaxException;

import static org.hamcrest.MatcherAssert.assertThat;
import static org.hamcrest.Matchers.is;
import static org.hamcrest.core.StringContains.containsString;

public class DVSAManagingUserRolesTests extends DslTest {

    private User vehicleExaminer;
    private User areaOffice1User;
    private User csco;
    private User tester;
    private AeDetails aeDetails;
    private Site site;

    @BeforeMethod(alwaysRun = true)
    private void setup() throws IOException {
        aeDetails = aeData.createAeWithDefaultValues();
        site = siteData.createNewSite(aeDetails.getId(), "Test_Site");
        areaOffice1User = userData.createAreaOfficeOne("AreaOfficer");
        vehicleExaminer = userData.createVehicleExaminer("ft-Enf-", false);
        csco = userData.createCustomerServiceOfficer(false);
        tester = userData.createTester(site.getId());
    }

    @Test(groups = {"Regression"})
    public void areaOfficeUserAddsRoleToVeUser() throws IOException, URISyntaxException {

        //Given that I am on Manage roles page as a Area office 1 user
        pageNavigator.goToManageRolesPageViaUserSearch(areaOffice1User, vehicleExaminer);

        //When I add role of AO2
        motUI.manageRoles.addRole("AO2");

        //Then Ao2 role is added to user
        assertThat(motUI.manageRoles.confirmRemoveRoleAction(), containsString("added"));
    }

    @Test(groups = {"Regression"})
    public void areaOfficeUserRemovesRoleFromVeUser() throws IOException, URISyntaxException {

        //Given that I am on Manage roles page as a Area office 1 user
        pageNavigator.goToManageRolesPageViaUserSearch(areaOffice1User, vehicleExaminer);

        //When I remove role
        motUI.manageRoles.removeRole("VE");

        //Then VE role is removed from user
        assertThat(motUI.manageRoles.confirmRemoveRoleAction(), containsString("removed"));
    }

    @Test(groups = {"Regression", "VM-12318"},
            description = "Test that validates the authorised DVSA user can search for user by email with " +
                    "expanded additional search criteria section")
    public void areaOfficeUserCanSearchForUserByEmailExpandedSection() throws IOException, URISyntaxException {

        //Given that I am on Search user page as a Area office 1 user
        pageNavigator.navigateToPage(areaOffice1User, UserSearchPage.PATH, UserSearchPage.class);

        //When I search for user by email with expanded criteria section
        motUI.searchUser.searchForUserByUserEmail(vehicleExaminer.getEmailAddress(), true, UserSearchResultsPage.class);

        //Then I should see the user details
        assertThat(motUI.searchUser.isUserSearchResultAccurate(vehicleExaminer), is(true));
    }

    @Test(groups = {"Regression", "VM-12318"},
            description = "Test that validates the authorised DVSA user can't search for user by email with " +
                    "collapsed additional search criteria section")
    public void areaOfficeUserCantSearchForUserByEmailCollapsedSection() throws IOException, URISyntaxException {

        //Given that I am on Search user page as a Area office 1 user
        pageNavigator.navigateToPage(areaOffice1User, UserSearchPage.PATH, UserSearchPage.class);


        //When I search for user by email with collapsed criteria section
        motUI.searchUser.searchForUserByUserEmail(vehicleExaminer.getEmailAddress(), false, UserSearchPage.class);

        //Then I should see an Error message
        assertThat(motUI.searchUser.isErrorMessageDisplayed(), is(true));
    }

    @Test(groups = {"Regression", "VM-12168"},
            description = "Test that validates the authorised DVSA user cant search for user by invalid email")
    public void areaOfficeUserCantSearchForUserByInvalidEmail() throws IOException, URISyntaxException {

        //Given that I am on Search user page as a Area office 1 user
        pageNavigator.navigateToPage(areaOffice1User, UserSearchPage.PATH, UserSearchPage.class);

        //When I search for user by invalid email
        motUI.searchUser.searchForUserByUserEmail(RandomDataGenerator.generateEmail(20, System.nanoTime()), true, UserSearchPage.class);

        //Then I should see a Validation message
        assertThat(motUI.searchUser.isNoResultsMessageDisplayed(), is(true));
    }

    @Test(groups = {"VM-7646", "Regression"})
    public void dvsaUserCanSearchOnTown() throws IOException, URISyntaxException {

        //Given that I am on Search user page as a authorised DVSA user
        pageNavigator.navigateToPage(userData.createAreaOfficeOne("Ao11"), UserSearchPage.PATH, UserSearchPage.class);

        //When I search for user by town
        motUI.searchUser.searchForUserByTown("Bristol");

        //Then I should see the user details
        assertThat(motUI.searchUser.isSearchResultAccurateWhenSearchingByTown("Bristol"), is(true));
    }

    @Test(groups = {"VM-4741", "Regression"},
            description = "Verify that authorised dvsa user can search for user with valid date of birth")
    public void dvsaUserCanSearchForUserByDateOfBirth() throws IOException, URISyntaxException {

        //Given that I am on Search user page as a authorised DVSA user
        pageNavigator.navigateToPage(areaOffice1User, UserSearchPage.PATH, UserSearchPage.class);

        //When I search for user by valid date of birth
        motUI.searchUser.searchForUserByDateOfBirth("24-11-1961", true);

        //Then I should see the user details
        assertThat(motUI.searchUser.isSearchResultAccurateWhenSearchingByDOB("24-11-1961"), is(true));
    }

    @Test(groups = {"VM-4741", "Regression"},
            description = "Verify error message is displayed when search user with invalid format date")
    public void dvsaUserCantSearchForUserByInvalidFormatDateOfBirth() throws IOException, URISyntaxException {

        //Given that I am on Search user page as a authorised DVSA user
        pageNavigator.navigateToPage(areaOffice1User, UserSearchPage.PATH, UserSearchPage.class);

        //When I search for user by invalid format date of birth
        motUI.searchUser.searchForUserByDateOfBirth("1-1-1920", false);

        //Then I should see an Error message
        assertThat(motUI.searchUser.isErrorMessageDisplayed(), is(true));
    }

    @Test(groups = {"VM-4741", "Regression"},
            description = "Verify proper message was displayed when user search page return too many results")
    public void dvsaUserSearchTooManyResults() throws IOException, URISyntaxException {
        //Given that I am on Search user page as a Area office 1 user
        pageNavigator.navigateToPage(areaOffice1User, UserSearchPage.PATH, UserSearchPage.class);

        //When I search for user by email with expanded criteria section
        motUI.searchUser.searchForUserByUserEmail("dummy@email.com", true, UserSearchPage.class);

        //Then I should see Too many results message
        assertThat(motUI.searchUser.isTooManyResultsMessageDisplayed("dummy@email.com"), is(true));
    }

    @Test(groups = {"VM-4698", "VM-4842", "VM-7724", "Regression"},
            description = "Verify that authorised dvsa user can search for user by valid username")
    public void dvsaSearchUserByUsername() throws IOException, URISyntaxException {
        //Given that I am on Search user page as a Area office 1 user
        pageNavigator.navigateToPage(areaOffice1User, UserSearchPage.PATH, UserSearchPage.class);

        //When I search for user by username
        motUI.searchUser.searchForUserByUsername(vehicleExaminer.getUsername(), UserSearchResultsPage.class);

        //Then I should see the user details
        assertThat(motUI.searchUser.isUserSearchResultAccurate(vehicleExaminer), is(true));
    }

    @Test(groups = {"Regression", "BL-1336"},
            description = "Verify that authorised dvsa user can navigate back from user profile to user search for user with valid username")
    public void dvsaCanSeeSearchParametersAfterNavigatingBackFromUserProfile() throws IOException, URISyntaxException {
        //Given that I am on Search user page as a Area office 1 user
        pageNavigator.navigateToPage(areaOffice1User, UserSearchPage.PATH, UserSearchPage.class);

        //When I search for user by username and going back to user search form
        UserSearchPage userSearchPage =
                motUI.searchUser.searchForUserByUsername(vehicleExaminer.getUsername(), UserSearchResultsPage.class)
                .chooseUser(0)
                .clickCancelAndReturnToSearchResults()
                .clickBackToUserSearch();

        //Then I should see the user search parameters
        assertThat(userSearchPage.getUserNameFieldValue(), containsString(vehicleExaminer.getUsername()));
    }

    @Test(groups = {"Regression"}, description = "Verify that authorised dvsa user can search for user and " +
            "get back to user search page with Back to user search link")
    public void dvsaSearchUserByNameAndGetBackToUserSearchPage() throws IOException, URISyntaxException {
        //Given that I am on Search user page as a Area office 1 user
        pageNavigator.navigateToPage(areaOffice1User, UserSearchPage.PATH, UserSearchPage.class);

        //When I click Back to user search link on User search results page
        motUI.searchUser.searchForUserByUserFirstName(vehicleExaminer.getFirstName(), UserSearchResultsPage.class).clickBackToUserSearch();

        //Then I should see the Search button
        assertThat(motUI.searchUser.isSearchButtonDisplayed(), is(true));
    }

    @Test(groups = {"Regression", "VM-12321"},
            description = "Verifies that authorised user can check user roles via roles and associations link " +
                    "on user profile page")
    public void dvsaUserCanViewTradesUserRolesAndAssociationsFromUserSearch() throws IOException, URISyntaxException {

        //Given that I am on a user profile page as an authorised DVSA user
        motUI.profile.dvsaViewUserProfile(areaOffice1User, tester);

        //I expect to see roles displayed
        assertThat(motUI.profile.page().clickRolesAndAssociationsLink().getRoleValues().isEmpty(), is(false));
    }

    @Test(groups = {"Regression", "VM-12321"},
            description = "Verifies that authorised DVSA user can check roles via roles and associations link " +
                    "of trade user")
    public void dvsaUserCanViewTradeUsersRolesAndAssociations() throws IOException, URISyntaxException {

        //Given I'm on the profile page of a user as an authorised DVSA user
        motUI.profile.dvsaViewUserProfile(areaOffice1User, tester);

        //I expect Roles and Associations link should be displayed
        assertThat(motUI.profile.page().isRolesAndAssociationsLinkDisplayed(), is(true));
    }

    @Test(groups = {"Regression", "VM-12321"},
            description = "Verifies that authorised user can navigate back from roles and associations page " +
                    "to user user profile page via link")
    public void dvsaUserNavigatesBackFromRolesAndAssociationsPageViaLink() throws IOException, URISyntaxException {

        //Given that I am on User roles and associations page as an authorised DVSA user
        RolesAndAssociationsPage rolesAndAssociationsPage = pageNavigator.goToUserSearchedProfilePageViaUserSearch(areaOffice1User, tester)
                .clickRolesAndAssociationsLink();

        //I expect to be able to return to user profile page
        rolesAndAssociationsPage.clickReturnToUserProfile();
    }

    @DataProvider(name = "dvsaUserCanSearchForAUser")
    public Object[][] dvsaUserCanSearchForAUser() {
        return new Object[][]{{areaOffice1User}, {vehicleExaminer},
                {csco}};
    }

    @DataProvider(name = "dvsaUserCanSearchForAUserByTown")
    public Object[][] dvsaUserCanSearchForAUserByTown() {
        return new Object[][]{{areaOffice1User}, {vehicleExaminer}};
    }
}
