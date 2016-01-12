package uk.gov.dvsa.ui.feature.journey.roles;

import org.testng.annotations.BeforeMethod;
import org.testng.annotations.Test;
import uk.gov.dvsa.domain.model.AeDetails;
import uk.gov.dvsa.domain.model.Site;
import uk.gov.dvsa.domain.model.User;
import uk.gov.dvsa.domain.model.vehicle.Vehicle;
import uk.gov.dvsa.ui.BaseTest;
import uk.gov.dvsa.ui.pages.HomePage;
import uk.gov.dvsa.ui.pages.RemoveRolePage;
import uk.gov.dvsa.ui.pages.dvsa.RolesAndAssociationsPage;
import uk.gov.dvsa.ui.pages.profile.ProfilePage;
import uk.gov.dvsa.ui.pages.vts.VehicleTestingStationPage;

import java.io.IOException;
import java.net.URISyntaxException;

import static org.hamcrest.MatcherAssert.assertThat;
import static org.hamcrest.Matchers.is;

public class TradeUserManagingRolesTests extends BaseTest {

    private User siteManager;
    private AeDetails aeDetails;
    private Site site;
    private User tester;
    private Vehicle testVehicle;
    private User areaOffice1User;

    @BeforeMethod(alwaysRun = true)
    private void setup() throws IOException {
        aeDetails = aeData.createAeWithDefaultValues();
        site = siteData.createNewSite(aeDetails.getId(), "Test_Site");
        tester = userData.createTester(site.getId());
        siteManager = userData.createSiteManager(site.getId(), false);
        testVehicle = vehicleData.getNewVehicle(tester);
        areaOffice1User = userData.createAreaOfficeOne("AreaOfficer");
    }

    @Test(groups = {"BVT", "Regression", "VM-12321"},
            description = "Verifies that trade user cant check roles via roles and associations link " +
                    "of other trade user")
    public void tradeUserCantViewAeAssociationOfOtherTradeUser() throws IOException, URISyntaxException {

        //Given I'm on the Vts details page
        VehicleTestingStationPage vehicleTestingStationPage = pageNavigator.goToPage(siteManager,  HomePage.PATH, HomePage.class).selectRandomVts();

        //When I click on assigned user
        vehicleTestingStationPage.chooseAssignedToVtsUser(tester.getId());

        //Then Roles and Associations link should not be displayed
        assertThat(motUI.manageRoles.isRolesAndAssociationsLinkDisplayedOnProfileOfPage(), is(false));
    }


    @Test(groups = {"BVT", "Regression", "BL-94"},
            description = "Verifies that trade user can navigate back from Remove role page to " +
                    "Roles and Associations page via link")
    public void tradeUserCanNavigateFromDeleteRolePageViaLink() throws IOException, URISyntaxException {

        //Given I'm logged in as Trade user and I am on Remove role page
        RemoveRolePage removeRolePage = pageNavigator.goToPage(tester, ProfilePage.PATH, ProfilePage.class)
                .clickRolesAndAssociationsLink().removeRole();

        //When I click on Cancel and return to roles and associations link
        removeRolePage.cancelRoleRemoval();

        //Then roles should be displayed
        assertThat(motUI.manageRoles.isRolesTableContainsValidTesterData(), is(true));
    }

    @Test(groups = {"BVT", "Regression", "BL-94"},
            description = "Verifies that trade user can't remove his own role if he has test in progress")
    public void tradeUserCantRemoveOwnTradeRoleWithTestInProgress() throws IOException, URISyntaxException {
        vehicleReinspectionWorkflow().startMotTestAsATester(tester, testVehicle);

        //Given I'm logged in as Trade user with test in progress and I am on Remove role page
        RemoveRolePage removeRolePage = pageNavigator.goToPage(tester, ProfilePage.PATH, ProfilePage.class)
                .clickRolesAndAssociationsLink().removeRole();

        //When I click on Confirm button
        removeRolePage.confirmRemoveRole(RolesAndAssociationsPage.class);

        //Then Error message should be displayed
        assertThat(motUI.manageRoles.isErrorMessageDisplayedOnRolesAndAssociationsPage(), is(true));
    }

    @Test(groups = {"BVT", "Regression", "BL-94"},
            description = "Verifies that trade user can remove his own role")
    public void tradeUserCanRemoveOwnTradeRole() throws IOException, URISyntaxException {

        //Given I am logged in as Trade user and I am on Remove role page
        RemoveRolePage removeRolePage = pageNavigator.goToPage(tester, ProfilePage.PATH, ProfilePage.class)
                .clickRolesAndAssociationsLink().removeRole();

        //When I click on Confirm button
        removeRolePage.confirmRemoveRole(RolesAndAssociationsPage.class);

        //Then role should be removed
        assertThat(motUI.manageRoles.isSuccessMessageDisplayedOnRolesAndAssociationsPage(), is(true));
    }

    @Test(groups = {"BVT", "Regression", "BL-94"},
            description = "Verifies that when trade user removes his own role it's not assigned to vts")
    public void tradeUserRoleIsNotAssignedToVtsAfterDeletion() throws IOException, URISyntaxException {

        tradeUserCanRemoveOwnTradeRole();

        //Given I am logged in as an authorised DVSA user and I am on the Site search page
        motUI.site.vtsSearchPage(areaOffice1User);

        //When I search for expected vts
        motUI.searchSite.searchForSiteBySiteId(site.getSiteNumber(), VehicleTestingStationPage.class);

        //Then removed role should not be present
        assertThat(motUI.manageRoles.isUserAssignedToVts(tester), is(false));
    }
}