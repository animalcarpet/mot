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

public class ChangeAddressTests extends DslTest {

    private User areaOffice1User;
    private User vehicleExaminerUser;
    private User tester;
    private User schemeManager;
    private Site testSite;
    private AeDetails aeDetails;
    private User aedm;
    private User siteManager;
    private User csco;

    @BeforeClass(alwaysRun = true)
    private void setup() throws IOException {
        aeDetails = aeData.createAeWithDefaultValues();
        testSite = siteData.createNewSite(aeDetails.getId(), "Test_Site");
        areaOffice1User = userData.createAreaOfficeOne("AreaOfficerOne");
        vehicleExaminerUser = userData.createVehicleExaminer("VehicleExaminer", false);
        tester = userData.createTester(testSite.getId());
        schemeManager = userData.createSchemeUser(false);
        aedm = userData.createAedm(aeDetails.getId(), "Test", false);
        siteManager = userData.createSiteManager(testSite.getId(), false);
        csco = userData.createCustomerServiceOfficer(false);
    }

    @Test(groups = {"Regression", "BL-929"},
            testName = "NewProfile",
            description = "Test that Authorised user can change address on other person profile",
            dataProvider = "dvsaUserChangeAddressProvider")
    public void dvsaUserCanChangeAddressOnOtherPersonProfile(User user) throws IOException {
        // Given I am on other person profile as an authorised user
        motUI.profile.dvsaViewUserProfile(user, tester);

        // When I am changing an address for a person
        motUI.profile.changeAddress().changeAddress("1 Portland street", "Manchester", "m1 4wb", "USER_PROFILE");

        // Then the success message should be displayed
        assertThat(motUI.profile.page().isSuccessMessageDisplayed(), is(true));
    }

    @Test(groups = {"Regression", "BL-929"},
            testName = "NewProfile",
            description = "Test that user can change address on own person profile",
            dataProvider = "userChangeOwnAddressProvider")
    public void userCanChangeAddressOnOwnPersonProfile(User user) throws IOException {
        // Given I am on own person profile
        motUI.profile.viewYourProfile(user);

        // When I am changing an address
        motUI.profile.changeAddress().changeAddress("1 Portland street", "Manchester", "m1 4wb", "PERSON_PROFILE");

        // Then the success message should be displayed
        assertThat(motUI.profile.page().isSuccessMessageDisplayed(), is(true));
    }

    @Test(groups = {"Regression", "BL-929"},
            testName = "NewProfile",
            description = "Test that authorised user can navigate through change address journey on other person profile")
    public void dvsaUserCanNavigateThroughChangeAddressJourneyOnOtherPersonProfile() throws IOException {
        // Given I am as authorised user on other person profile
        motUI.profile.dvsaViewUserProfile(schemeManager, tester);

        // When I am navigating to change address, review address and backward to person profile page
        motUI.profile.changeAddress()
                .navigateToReviewAddress()
                .navigateFromReviewAddressToChangeAddress()
                .navigateFromChangeAddressToPersonProfile(false);

        // Then the user profile page should be loaded
        assertThat(motUI.profile.page().isPageLoaded(), is(true));
    }

    @Test(groups = {"Regression", "BL-929"},
            testName = "NewProfile",
            description = "Test that user can navigate through change address journey on own person profile")
    public void userCanNavigateThroughChangeAddressJourneyOnOwnPersonProfile() throws IOException {
        // Given I am on own person profile
        motUI.profile.viewYourProfile(areaOffice1User);

        // When I am navigating to change address, review address and backward to person profile page
        motUI.profile.changeAddress()
                .navigateToReviewAddress()
                .navigateFromReviewAddressToChangeAddress()
                .navigateFromChangeAddressToPersonProfile(true);

        // Then the person profile page should be loaded
        assertThat(motUI.profile.page().isPageLoaded(), is(true));
    }

    @Test(groups = {"Regression", "BL-929"},
            testName = "NewProfile",
            description = "Test that user should provide correct address during change address journey on own person profile")
    public void userShouldProvideCorrectAddress() throws IOException {
        // Given I am on own person profile
        motUI.profile.viewYourProfile(areaOffice1User);

        // When I am trying to submit an address with incorrect address input
        motUI.profile.changeAddress().changeAddress("", "Manchester", "m1 4wb", "INVALID_INPUT");

        // Then the error validation message should be displayed
        assertThat(motUI.profile.changeAddress().isValidationMessageOnChangeAddressPageDisplayed("FIRST_LINE_INVALID"), is(true));
    }

    @Test(groups = {"Regression", "BL-929"},
            testName = "NewProfile",
            description = "Test that user should provide correct town during change address journey on own person profile")
    public void userShouldProvideCorrectTown() throws IOException {
        // Given I am on own person profile
        motUI.profile.viewYourProfile(csco);

        // When I am trying to submit an address with incorrect town input
        motUI.profile.changeAddress().changeAddress("1 Lane", "", "m1 4wb", "INVALID_INPUT");

        // Then the error validation message should be displayed
        assertThat(motUI.profile.changeAddress().isValidationMessageOnChangeAddressPageDisplayed("TOWN_INVALID"), is(true));
    }

    @Test(groups = {"Regression", "BL-929"},
            testName = "NewProfile",
            description = "Test that user should provide correct postcode during change address journey on own person profile")
    public void userShouldProvideCorrectPostcode() throws IOException {
        // Given I am on own person profile
        motUI.profile.viewYourProfile(siteManager);

        // When I am trying to submit an address with incorrect postcode input
        motUI.profile.changeAddress().changeAddress("1 Lane", "Manchester", "", "INVALID_INPUT");

        // Then the error validation message should be displayed
        assertThat(motUI.profile.changeAddress().isValidationMessageOnChangeAddressPageDisplayed("POSTCODE_INVALID"), is(true));
    }

    @Test(groups = {"Regression", "BL-929"},
            testName = "NewProfile",
            description = "Test that user should provide correct values during change address journey on own person profile")
    public void userShouldProvideCorrectValues() throws IOException {
        // Given I am on own person profile
        motUI.profile.viewYourProfile(aedm);

        // When I am trying to submit an address with incorrect input
        motUI.profile.changeAddress().changeAddress("", "", "", "INVALID_INPUT");

        // Then the error validation message should be displayed
        assertThat(motUI.profile.changeAddress().isValidationMessageOnChangeAddressPageDisplayed("INPUT_INVALID"), is(true));
    }

    @DataProvider
    private Object[][] dvsaUserChangeAddressProvider() {
        return new Object[][] {
                {areaOffice1User},
                {vehicleExaminerUser},
                {csco},
                {schemeManager}
        };
    }

    @DataProvider
    private Object[][] userChangeOwnAddressProvider() {
        return new Object[][] {
                {areaOffice1User},
                {vehicleExaminerUser},
                {csco},
                {tester},
                {aedm},
                {siteManager},
                {schemeManager}
        };
    }
}
