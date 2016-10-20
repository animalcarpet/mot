package uk.gov.dvsa.ui.feature.journey.account_administration;

import org.testng.annotations.BeforeClass;
import org.testng.annotations.DataProvider;
import org.testng.annotations.Test;
import uk.gov.dvsa.domain.model.AeDetails;
import uk.gov.dvsa.domain.model.Site;
import uk.gov.dvsa.domain.model.User;
import uk.gov.dvsa.ui.DslTest;

import java.io.IOException;

import static org.hamcrest.MatcherAssert.assertThat;
import static org.hamcrest.core.Is.is;

public class ChangePersonNameTests extends DslTest {

    private User areaOffice1User;
    private User vehicleExaminerUser;
    private User tester;
    private User schemeManager;
    private Site testSite;
    private AeDetails aeDetails;
    private User csco;
    private User aedm;
    private User siteManager;

    @BeforeClass(alwaysRun = true)
    private void setup() throws IOException {
        aeDetails = aeData.createAeWithDefaultValues();
        testSite = siteData.createNewSite(aeDetails.getId(), "Test_Site");
        areaOffice1User = motApi.user.createAreaOfficeOne("AreaOfficerOne");
        vehicleExaminerUser = motApi.user.createVehicleExaminer("VehicleExaminer", false);
        tester = motApi.user.createTester(testSite.getId());
        schemeManager = motApi.user.createSchemeUser(false);
        aedm = motApi.user.createAedm(aeDetails.getId(), "Test", false);
        siteManager = motApi.user.createSiteManager(testSite.getId(), false);
        csco = motApi.user.createCustomerServiceOfficer(false);
    }

    @Test(groups = {"BVT", "BL-59"},
            testName = "NewProfile",
            description = "Test that Authorised user can see change name link on person profile",
            dataProvider = "dvsaUserProvider")
    public void dvsaUserCanSeeChangeNameLinkOnOtherPersonProfile(User user, boolean isLinkVisible) throws IOException {
        // Given I am on other person profile as an authorised user
        motUI.profile.dvsaViewUserProfile(user, tester);

        // Then the change name link should be displayed
        assertThat(motUI.profile.page().isChangeNameLinkDisplayed(), is(isLinkVisible));
    }

    @Test(groups = {"BVT", "BL-59"},
            testName = "NewProfile",
            description = "Test that Authorised user can change name on person profile",
            dataProvider = "dvsaUserFroOwnProfileProvider")
    public void dvsaUserCanChangeNameOnOtherPersonProfile(User user) throws IOException {
        // Given I am on other person profile as an authorised user
        motUI.profile.dvsaViewUserProfile(user, tester);

        // When I am changing a name for a person
        motUI.profile.changeName().changePersonName("Test", "Test", true);

        // Then the success message should be displayed
        assertThat(motUI.profile.page().isSuccessMessageDisplayed(), is(true));
    }

    @Test(groups = {"Regression", "BL-59"},
            testName = "NewProfile",
            description = "Test that Authorised user should provide a first name in order to update user information")
    public void dvsaUserShouldProvideFirstName() throws IOException {
        // Given I am on other person profile as an authorised user
        motUI.profile.dvsaViewUserProfile(motApi.user.createAreaOfficeOne("ao1"), motApi.user.createTester(testSite.getId()));

        // When I am trying to submit an empty name for a person
        motUI.profile.changeName().changePersonName("", "Test", false);


        // Then the error validation message should be displayed
        assertThat(motUI.profile.changeName().isValidationMessageOnChangeNamePageDisplayed("FIRST_NAME"), is(true));
    }

    @Test(groups = {"Regression", "BL-59"},
            testName = "NewProfile",
            description = "Test that Authorised user should provide a last name in order to update user information")
    public void dvsaUserShouldProvideLastName() throws IOException {
        // Given I am on other person profile as an authorised user
        motUI.profile.dvsaViewUserProfile(motApi.user.createVehicleExaminer("ved", false), tester);

        // When I am trying to submit an empty last name for a person
        motUI.profile.changeName().changePersonName("Test", "", false);

        // Then the error validation message should be displayed
        assertThat(motUI.profile.changeName().isValidationMessageOnChangeNamePageDisplayed("LAST_NAME"), is(true));
    }

    @Test(groups = {"Regression", "BL-59"},
            testName = "NewProfile",
            description = "Test that Authorised user can navigate to Change name page and backward")
    public void dvsaUserCanNavigateToAndBackwardChangeNamePage() throws IOException {
        // Given I am on other person profile as an authorised user
        motUI.profile.dvsaViewUserProfile(motApi.user.createAreaOfficeOne("Ao1"), motApi.user.createTester(testSite.getId()));

        // When I am navigating to Change name page and clicking on cancel and return link
        motUI.profile.page().clickChangeNameLink().clickCancelAndReturnLink();

        // Then the person profile page should be displayed
        assertThat(motUI.profile.page().isPageLoaded(), is(true));
    }

    @Test(groups = {"Regression", "BL-59"},
            testName = "NewProfile",
            description = "Test that Authorised user can not see change name link on it's own profile",
            dataProvider = "dvsaUserFroOwnProfileProvider")
    public void dvsaUserCantSeeChangeNameLinkOnOwnProfile(User user) throws IOException {
        // Given I am on my own person profile as a dvsa user
        motUI.profile.viewYourProfile(user);

        // Then the change name link should not be displayed
        assertThat(motUI.profile.page().isChangeNameLinkDisplayed(), is(false));
    }

    @Test(groups = {"Regression", "BL-59"},
            testName = "NewProfile",
            description = "Test that Trade user can't see change name link on person profile",
            dataProvider = "tradeUserProvider")
    public void userCantSeeChangeNameLinkOnOtherPersonProfile(User user) throws IOException {
        // Given I am on other person profile as a trade user
        motUI.profile.tradeViewUserProfile(user, tester);

        // Then the change name link should not be displayed
        assertThat(motUI.profile.page().isChangeNameLinkDisplayed(), is(false));
    }

    @DataProvider
    private Object[][] dvsaUserProvider() {
        return new Object[][] {
                {areaOffice1User, true},
                {vehicleExaminerUser, true},
                {csco, false},
                {schemeManager, true}
        };
    }

    @DataProvider
    private Object[][] dvsaUserFroOwnProfileProvider() throws IOException {
        return new Object[][] {
                {motApi.user.createAreaOfficeOne("ao")},
                {motApi.user.createVehicleExaminer("ve", false)},
                {motApi.user.createSchemeUser(false)}
        };
    }

    @DataProvider
    private Object[][] tradeUserProvider() {
        return new Object[][] {
                {aedm},
                {siteManager}
        };
    }
}