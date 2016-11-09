package uk.gov.dvsa.ui.views;

import org.testng.annotations.DataProvider;
import org.testng.annotations.Test;
import uk.gov.dvsa.domain.model.User;
import uk.gov.dvsa.ui.DslTest;
import uk.gov.dvsa.ui.pages.exception.PageInstanceNotFoundException;

import java.io.IOException;
import java.net.URISyntaxException;

import static org.hamcrest.MatcherAssert.assertThat;
import static org.hamcrest.core.Is.is;
import static org.hamcrest.core.StringContains.containsString;

public class DriverLicenseViewTests extends DslTest {

    private static final String DRIVING_LICENCE_CHANGE_SUCCESS = "Driving licence has been changed successfully.";
    private static final String DRIVING_LICENCE_WRONG_NI_FORMAT = "Driving licence - must be a valid Northern Ireland driving licence";
    private static final String DRIVING_LICENCE_WRONG_GB_FORMAT = "Driving licence - must be a valid Great Britain driving licence";
    private static final String DRIVING_LICENCE_EMPTY = "you must enter a driving licence number";

    @Test(groups = {"Regression"},
            description = "Test that a validation error message is displayed to DVSA user when submitting wrong information",
            dataProvider = "invalidLicenceInputTestCases")
    public void dvsaUserSeesValidationErrorWithInvalidInput(String number, String country, String expectedMessage)
            throws IOException, URISyntaxException {
        //Given I am viewing a trade user profile as DVSA officer
        motUI.profile.dvsaViewUserProfile(motApi.user.createAreaOfficeOne("ao1"),
                motApi.user.createTester(siteData.createSite().getId()));

        // And I submit incorrect driver licence information
        String validationMessage = motUI.profile.submitInvalidLicenseDetails(number, country).getValidationSummary();

        // Then a validation error is displayed
        assertThat(validationMessage, containsString(expectedMessage));
    }

    @Test(groups = {"Regression"},
            description = "Test that validates the authorised DVSA user can see driver licence Number for Trade Users",
            dataProvider = "dvsaUserProvider")
    public void dvsaUserCanViewTradeUserLicenseDetails(User dvsaUser) throws IOException, URISyntaxException{
        //Given I am viewing a trade user profile as DVSA officer
        motUI.profile.dvsaViewUserProfile(dvsaUser, motApi.user.createTester(siteData.createSite().getId()));

        //Then I expect the Driver License number to be displayed
        assertThat(motUI.profile.page().drivingLicenceIsDisplayed(), is(true));
    }

    @Test(groups = {"Regression"},
            description = "DVSA user can't see driver licence section on it's own profile page",
            dataProvider = "dvsaUserProvider")
    public void dvsaUsersCannotViewTheirPersonalLicenseDetails(User dvsaUser) throws IOException, URISyntaxException{
        //Given I am viewing my profile as DVSA User
        motUI.profile.viewYourProfile(dvsaUser);

        //Then my driving licence number should not displayed
        assertThat( motUI.profile.page().isDrivingLicenceInformationIsDisplayed(), is(false));
    }

    @Test(groups = {"Regression"},
            description = "Authorised DVSA user can't see driver licence section on other DVSA user")
    public void dvsaUserCantSeeDriverLicenceForOtherDvsaUser() throws IOException, URISyntaxException {
        // Given that I'm on a DVSA user profile as authorised DVSA user
        motUI.profile.dvsaViewUserProfile(motApi.user.createAreaOfficeOne("Ao1"), motApi.user.createAreaOfficeTwo("Ao2"));

        // Then the driving licence number element is not displayed
        assertThat(motUI.profile.page().isDrivingLicenceInformationIsDisplayed(), is(false));
    }

    @Test(groups = {"Regression"},
            description = "Test that DVSA user can edit non-DVSA user driving licence successfully",
            dataProvider = "validLicenceInputTestCases")
    public void dvsaUserCanChangeTradeUserLicenseDetails(String number, String country) throws IOException, URISyntaxException {
        //setup
        final User areaOffice1user = motApi.user.createAreaOfficeOne("ao1");
        final User tester = motApi.user.createTester(siteData.createSite().getId());

        // Given that I am viewing user profile as authorised DVSA user
        motUI.profile.dvsaViewUserProfile(areaOffice1user, tester);

        //When I edit and submit license details
        motUI.profile.editAndSubmitLicenseDetails(number, country);

        //Then details should be saved successfully
        assertThat(motUI.profile.page().getMessageSuccess(), containsString(DRIVING_LICENCE_CHANGE_SUCCESS));
    }

    @Test(groups = {"Regression"},
            expectedExceptions = PageInstanceNotFoundException.class,
            description = "Test that URL cannot be modified by DVSA user to change DVSA user driving licence")
    public void dvsaUserCannotModifyUrlToChangeOtherDvsaUserDrivingLicence() throws IOException {
        // Given I attempt to modify the ID fragment of the change driving licence page
        motUI.profile.hackChangeLicenseUrl(motApi.user.createAreaOfficeOne("ao1"), motApi.user.createAreaOfficeTwo("ao2"));

        // Then the application will display an error page
    }

    @Test(groups = {"Regression"},
            description = "Test that DVSA user can remove non-DVSA user driving licence")
    public void dvsaUserCanRemoveTradeUserDrivingLicence() throws IOException, URISyntaxException {
        //setup
        final User tester = motApi.user.createTester(siteData.createSite().getId());

        // Given I am viewing a trade user profile as authorised DVSA user
        motUI.profile.dvsaViewUserProfile(motApi.user.createAreaOfficeOne("ao1"), tester);

        // When I remove the driver license details
        motUI.profile.removeLicense();

        //Then Driver license should now read None recorded
        assertThat(motUI.profile.page().getDrivingLicenceForPerson(), containsString("None recorded"));}

    @DataProvider
    private Object[][] dvsaUserProvider() throws IOException{
        return new Object[][] {
                {motApi.user.createAreaOfficeOne("AreaOfficerOne")},
                {motApi.user.createVehicleExaminer("VehicleExaminer", false)},
                {motApi.user.createCustomerServiceOfficer(false)},
                {motApi.user.createSchemeUser(false)}};
    }

    @DataProvider
    private Object[][] validLicenceInputTestCases() {
        return new Object[][] {
//                {"AAAAA807217BM9PC", "GB"},
//                {"12345678", "NI"},
                {"123-456-789", "NU"}
        };
    }

    @DataProvider
    private Object[][] invalidLicenceInputTestCases() {
        return new Object[][] {
                {"AAAA8807217BM9PC", "GB", DRIVING_LICENCE_WRONG_GB_FORMAT},
                {"123456789", "NI", DRIVING_LICENCE_WRONG_NI_FORMAT},
                {"", "NI", DRIVING_LICENCE_EMPTY},
        };
    }
}
