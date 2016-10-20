package uk.gov.dvsa.ui.feature.journey.account_administration;

import org.testng.annotations.BeforeClass;
import org.testng.annotations.DataProvider;
import org.testng.annotations.Test;
import uk.gov.dvsa.domain.model.AeDetails;
import uk.gov.dvsa.domain.model.Site;
import uk.gov.dvsa.domain.model.User;
import uk.gov.dvsa.ui.DslTest;
import uk.gov.dvsa.ui.pages.profile.ProfilePage;

import java.io.IOException;

import static org.hamcrest.MatcherAssert.assertThat;
import static org.hamcrest.Matchers.containsString;
import static org.hamcrest.core.Is.is;

public class ChangePersonTelephoneTests extends DslTest {
    private static final String TELEPHONE_TOO_LARGE_MESSAGE = "Phone number - must be 24 characters or less";

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

    @Test(groups = {"Regression", "BL-931"},
            testName = "NewProfile",
            description = "Test that Trade users can edit their telephone from their profile page")
    public void tradeUserCanEditHisTelephone() throws Exception{
        // Given I am logged in as a Tester and I am on the My Profile Page
        ProfilePage profilePage = motUI.profile.viewYourProfile(motApi.user.createTester(testSite.getId()));

        // When I edit my Telephone number
        motUI.profile.changeYourTelephoneTo("+44 (0) 1225 200 123");

        // Then My Profile Telephone number will be amended with successful confirmation message
        assertThat(profilePage.isSuccessMessageDisplayed(), is(true));
    }

    @Test(groups = {"Regression", "BL-931"},
            testName = "NewProfile",
            description = "Test that Trade user can cancel their telephone update from change telephone page")
    public void tradeUserCanCancelHisTelephoneChange() throws IOException {
        // Given I am the Change telephone page as a tester
        ProfilePage profilePage = motUI.profile.viewYourProfile(tester);

        // When I Cancel my Telephone number edit
        motUI.profile.editTelephone("123456").cancelEdit(profilePage);

        // Then I will be returned to My Profile Page
        assertThat(motUI.profile.page().isPageLoaded(), is(true));
    }

    @Test(groups = {"Regression", "BL-931"},
            testName = "NewProfile",
            description = "Test that DVSA user can cancel amending a users telephone number change",
            dataProvider = "dvsaUserChangeTelephoneProvider")
    public void dvsaUserCanCancelUserTelephoneChange(User user) throws IOException {
        // Given I am logged in as a DVSA User and User Profile Page
        ProfilePage profilePage = motUI.profile.dvsaViewUserProfile(user, tester);

        // When I Cancel users Telephone number edit
        motUI.profile.editTelephone("111111").cancelEdit(profilePage);

        // Then I will be returned to User Page
        assertThat(motUI.profile.page().isPageLoaded(), is(true));
    }

    @Test(groups = {"Regression", "BL-931"},
            testName = "NewProfile",
            description = "Test that Authorised user can change telephone number on person profile",
            dataProvider = "dvsaUserChangeTelephoneProvider")
    public void dvsaUserCanChangeTelephoneNumberOnOtherPersonProfile(User user) throws IOException {
        // Given I am on a Users profile as an authorised user
        ProfilePage profilePage = motUI.profile.dvsaViewUserProfile(user, tester);

        // When I am changing telephone number for a person
        motUI.profile.changeUserTelephoneAsDvsaTo("+44 (0) 1225 200 123");

        // Then My Profile Telephone number will be amended with successful confirmation message
        assertThat(profilePage.isSuccessMessageDisplayed(), is(true));
    }

    @Test(groups = {"Regression", "BL-931"},
            testName = "NewProfile",
            description = "Test that Trade user cannot enter more than 24 Chars in the Telephone number")
    public void tradeUserShouldProvideValidTelephoneNumber() throws IOException {
        // Given I am logged in as a Tester and I am on the My Profile Page
        motUI.profile.viewYourProfile(motApi.user.createTester(testSite.getId()));

        // When I am trying to submit a telephone number greater than 24 Chars
        String validationMessage = motUI.profile.editTelephoneWithInvalidInput("A123456789B123456789C12345");

        // Then the error validation message should be displayed
        assertThat(validationMessage, containsString(TELEPHONE_TOO_LARGE_MESSAGE));
    }

    @Test(groups = {"Regression", "BL-931"},
            testName = "NewProfile",
            description = "Test that Authorised user should provide an appropriate sized telephone number in order to update user information")
    public void dvsaUserShouldNotProvideInvalidSizedTelephoneNumber() throws IOException {
        // Given I am on other person profile as an authorised user
        motUI.profile.dvsaViewUserProfile(areaOffice1User, tester);

        // When I am trying to submit a telephone number greater than 24 Chars
        String validationMessage = motUI.profile.editTelephoneWithInvalidInput("A123456789B123456789C12345");

        // Then the error validation message should be displayed
        assertThat(validationMessage, containsString(TELEPHONE_TOO_LARGE_MESSAGE));
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
    private Object[][] dvsaUserChangeTelephoneProvider() throws IOException {
        return new Object[][] {
                {motApi.user.createAreaOfficeOne("aoe")},
                {motApi.user.createVehicleExaminer("ves", false)},
                {motApi.user.createSchemeUser(false)},
                {motApi.user.createCustomerServiceOfficer(false)},
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


