package uk.gov.dvsa.ui.feature.journey.account_administration;

import org.testng.annotations.BeforeClass;
import org.testng.annotations.DataProvider;
import org.testng.annotations.Test;
import uk.gov.dvsa.domain.model.AeDetails;
import uk.gov.dvsa.domain.model.Site;
import uk.gov.dvsa.domain.model.User;
import uk.gov.dvsa.helper.ContactDetailsHelper;
import uk.gov.dvsa.helper.RandomDataGenerator;
import uk.gov.dvsa.ui.DslTest;
import uk.gov.dvsa.ui.pages.profile.ProfilePage;

import java.io.IOException;

import static org.hamcrest.MatcherAssert.assertThat;
import static org.hamcrest.Matchers.containsString;
import static org.hamcrest.Matchers.equalToIgnoringCase;
import static org.hamcrest.core.Is.is;

public class ChangePersonEmailTests extends DslTest {

    private static final String EMAIL_MUST_BE_VALID_MESSAGE = "Enter a valid email address";
    private static final String EMAIL_MUST_MATCH_MESSAGE = "The email addresses must be the same";

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

    @Test(groups = {"Regression", "BL-270"},
            testName = "NewProfile",
            description = "Test that Trade user can edit their email from their profile page")
    public void tradeUserCanEditTheirEmailAddress() throws Exception {
        //Given I am logged in as a Tester and I am on the My Profile Page
        motUI.profile.viewYourProfile(motApi.user.createTester(testSite.getId()));

        //When I edit my Email address
        ProfilePage profilePage = motUI.profile.changeYourEmailTo(ContactDetailsHelper.getEmail());

        //Then My Profile Email address will be amended
        assertThat(profilePage.isSuccessMessageDisplayed(), is(true));
    }

    @Test(groups = {"Regression", "BL-270"},
            testName = "NewProfile",
            description = "Test that Trade user can cancel their email from change email page")
    public void tradeUserCanCancelTheirEmailChange() throws IOException {
        //Given I am logged in as a Tester and I am on the My Profile Page
        motUI.profile.viewYourProfile(tester);

        //When I Cancel my Email address edit
        ProfilePage page = motUI.profile.page().clickChangeEmailLink().clickCancelButton(true);

        //Then I will be returned to My Profile Page
        assertThat(page.isPageLoaded(), is(true));
    }

    @Test(groups = {"Regression", "BL-270"},
            testName = "NewProfile",
            description = "Test that DVSA user can cancel amending a users email change",
            dataProvider = "dvsaUserChangeEmailProvider")
    public void dvsaUserCanCancelTheirUserEmailChange(User user) throws IOException {
        //Given I am logged in as a Tester and I am on the My Profile Page
        motUI.profile.dvsaViewUserProfile(user, tester);

        //When I Cancel my Email address edit
        motUI.profile.page().clickChangeEmailLink().clickCancelButton(false);

        //Then I will be returned to My Profile Page
        assertThat(motUI.profile.page().isPageLoaded(), is(true));
    }

    @Test(groups = {"Regression", "BL-270"},
            testName = "NewProfile",
            description = "Test that Authorised user can change email on person profile",
            dataProvider = "dvsaUserChangeEmailProvider")
    public void dvsaUserCanChangeEmailOnOtherPersonProfile(User user) throws IOException {
        // Given I am on other person profile as an authorised user
        motUI.profile.dvsaViewUserProfile(user, tester);

        // When I am changing a email for a person
        String emailAddress = RandomDataGenerator.generateEmail(20, System.nanoTime());
        motUI.profile.changeUserEmailAsDvsaTo(emailAddress);

        // Then the success message should be displayed
        assertThat(motUI.profile.page().isSuccessMessageDisplayed(), is(true));
    }

    @Test(groups = {"Regression", "BL-270"},
            testName = "NewProfile",
            dataProvider = "emailValidationTestCaseValues",
            description = "Test that Authorised user should provide a valid email in order to update user information")
    public void validationMessageShouldBeDisplayedForIncorrectInputValue(
            String email,
            String confirmationEmail,
            String expectedvalidationMessage) throws IOException {

        // Given I am on other person profile as an authorised user
        motUI.profile.dvsaViewUserProfile(motApi.user.createAreaOfficeOne("ValidAo1"), tester);

        // When I am trying to submit an an invalid email
        String validationMessage = motUI.profile.changeEmailWithInvalidInputs(email, confirmationEmail);

        // Then the error validation message should be displayed
        assertThat(validationMessage, containsString(expectedvalidationMessage));
    }

    @Test(groups = {"BVT"})
    public void userCannotChangeEmailAddressIfItIsAlreadyInUse() throws IOException {
        step("Given there is a user in the system with email tester1@email.com");
        motApi.user.createUserWithCustomData("emailAddress", "tester1@email.com");

        step("When I log in to update my email to tester1@email.com");
        motUI.profile.viewYourProfile(tester);
        String validationMessage = motUI.profile.changeEmailWithInvalidInputs("tester1@email.com","tester1@email.com");

        step("Then I am receive a validation message advising that this email is already in use");
        assertThat(validationMessage, equalToIgnoringCase
            ("Email address - This email address is already in use. Each account must have a different email address."));
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
    private Object[][] emailValidationTestCaseValues() {
        return new Object[][] {
                {"ad%^&*£lkjfhadslkjhf", "ad%^&*£lkjfhadslkjhf", EMAIL_MUST_BE_VALID_MESSAGE },
                {"fred@bloggs.com", "barry@bloggs.com", EMAIL_MUST_MATCH_MESSAGE},
        };
    }

    @DataProvider
    private Object[][] dvsaUserChangeEmailProvider() throws IOException {
        return new Object[][] {
                {motApi.user.createAreaOfficeOne("Ao1")},
                {motApi.user.createVehicleExaminer("veguy", false)},
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
