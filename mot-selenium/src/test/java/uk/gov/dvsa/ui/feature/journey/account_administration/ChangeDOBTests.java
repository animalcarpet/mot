package uk.gov.dvsa.ui.feature.journey.account_administration;

import org.testng.annotations.BeforeClass;
import org.testng.annotations.DataProvider;
import org.testng.annotations.Test;
import uk.gov.dvsa.domain.model.Site;
import uk.gov.dvsa.domain.model.User;
import uk.gov.dvsa.ui.DslTest;

import java.io.IOException;
import java.net.URISyntaxException;

import static org.hamcrest.MatcherAssert.assertThat;
import static org.hamcrest.Matchers.containsString;
import static org.hamcrest.core.Is.is;

public class ChangeDOBTests extends DslTest {
    private static final String DOB_ERROR_MESSAGE = "must be a valid date of birth";
    private Site site;

    @BeforeClass(alwaysRun = true)
    private void setUp() throws IOException {
        site = siteData.createSite();
    }

    @Test(groups = {"Regression", "BL-927"},
            testName = "NewProfile",
            description = "Test that Authorised user can change date of birth on other person profile",
            dataProvider = "dvsaUserChangeDOBProvider")
    public void dvsaUserCanChangeDOBOnOtherPersonProfile(User user) throws IOException {
        // Given I am on the profile page of a user as DVSA
        motUI.profile.dvsaViewUserProfile(user, motApi.user.createTester(site.getId()));

        // When I change the Date of Birth
        motUI.profile.changeDateOfBirthTo("10 Apr 1980");

        // Then the success message should be displayed
        assertThat(motUI.profile.page().isSuccessMessageDisplayed(), is(true));
    }

    @Test(groups = {"Regression", "BL-927"},
            testName = "NewProfile",
            description = "Test that user cant change date of birth on own person profile",
            dataProvider = "userCantSeeChangeDOBLinkProvider")
    public void userCantSeeChangeDOBLinkOnOwnProfile(User user) throws IOException, URISyntaxException {

        //Given I'm on the New Person Profile page as logged user
        motUI.profile.viewYourProfile(user);

        //Then Change date of birth link should not be displayed
        assertThat(motUI.profile.page().isChangeDOBLinkIsDisplayed(), is(false));
    }

    @Test(groups = {"Regression", "BL-927"},
            testName = "NewProfile",
            description = "Test that user should provide a valid day in order to change date of birth",
            dataProvider = "invalidDateData")
    public void validationMessageDisplayedForInvalidInput(String day, String month, String year) throws IOException, URISyntaxException {

        // Given I'm on the New Person Profile page as logged user
        motUI.profile.dvsaViewUserProfile(motApi.user.createAreaOfficeOne("ao1"), motApi.user.createTester(site.getId()));

        // When I am trying to change a date of birth for a person with invalid values
        String validationMessage = motUI.profile.changeDOBwithInvalidValues(day, month, year);

        // Then the appropriate validation message should be displayed
        assertThat(validationMessage, containsString(DOB_ERROR_MESSAGE));
    }

    @Test(groups = {"Regression", "BL-59"},
            testName = "NewProfile",
            description = "Test that Authorised user can navigate to Change date of birth page and backward")
    public void dvsaUserCanNavigateToAndBackwardDOBPage() throws IOException {
        // Given I am on other person profile as an authorised user
        motUI.profile.dvsaViewUserProfile(motApi.user.createAreaOfficeOne("ao1"), motApi.user.createTester(site.getId()));

        // When I am navigating to Change date of birth page and clicking on cancel and return link
        motUI.profile.page().clickChangeDOBLink().clickCancelAndReturnLink();

        // Then the person profile page should be displayed
        assertThat(motUI.profile.page().isPageLoaded(), is(true));
    }

    @DataProvider
    private Object[][] dvsaUserChangeDOBProvider() throws IOException {
        return new Object[][] {
                {motApi.user.createAreaOfficeOne("Ao1Test")},
                {motApi.user.createVehicleExaminer("vetest", false)},
                {motApi.user.createSchemeUser(false)}
        };
    }

    @DataProvider
    private Object[][] invalidDateData() {
        return new Object[][] {
                {"", "10", "1980"},
                {"10", "", "1980"},
                {"10", "01", ""}
        };
    }

    @DataProvider
    private Object[][] userCantSeeChangeDOBLinkProvider() throws IOException {
        return new Object[][] {
                {motApi.user.createUserAsAreaOfficeOneUser("areaOne")},
                {motApi.user.createVehicleExaminer("ve1", false)},
                {motApi.user.createSchemeUser(false)},
                {motApi.user.createAedm(false)},
                {motApi.user.createSiteManager(site.getId(), false)},
                {motApi.user.createTester(site.getId())}
        };
    }
}