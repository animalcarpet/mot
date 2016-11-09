package uk.gov.dvsa.ui.views;

import org.testng.annotations.BeforeClass;
import org.testng.annotations.DataProvider;
import org.testng.annotations.Test;
import uk.gov.dvsa.domain.model.AeDetails;
import uk.gov.dvsa.domain.model.Site;
import uk.gov.dvsa.domain.model.User;
import uk.gov.dvsa.helper.RandomDataGenerator;
import uk.gov.dvsa.ui.DslTest;
import uk.gov.dvsa.ui.pages.profile.ProfilePage;

import java.io.IOException;
import java.net.URISyntaxException;

import static org.hamcrest.MatcherAssert.assertThat;
import static org.hamcrest.core.Is.is;

public class ProfilePageViewTests extends DslTest {

    private User tester;
    private User areaOffice2User;
    private Site site;
    private String randomName = RandomDataGenerator.generateRandomString(5, System.nanoTime());

    @BeforeClass(alwaysRun = true)
    private void setup() throws IOException {
        AeDetails aeDetails = aeData.createAeWithDefaultValues();
        site = siteData.createNewSite(aeDetails.getId(), "Test_Site");
        tester = motApi.user.createTester(site.getId());
        areaOffice2User = motApi.user.createAreaOfficeTwo(randomName);
    }

    @Test(groups = {"BVT"}, description = "VM-10334")
    public void testerQualificationStatusDisplayedOnProfilePage() throws IOException {

        //Given I'm on the Your Profile Details page
        motUI.profile.viewYourProfile(motApi.user.createTester(site.getId()));

        //Then I should be able to see the qualification status
        assertThat(motUI.profile.isTesterQualificationStatusDisplayed(), is(true));
    }

    @Test(groups = {"BVT", "VM-12321"},
            description = "Verifies that trade user can check own roles via roles and associations link " +
                    "on it's own profile page")
    public void tradeUserCanViewHisOwnRolesAndAssociations() throws IOException, URISyntaxException {

        //Given I'm on the Your Profile Details page
        motUI.profile.viewYourProfile(motApi.user.createTester(site.getId()));

        //When I click on Roles and Associations link
        motUI.profile.page().clickRolesAndAssociationsLink();

        //Then roles should be displayed
        assertThat(motUI.manageRoles.isRolesTableContainsValidTesterData(), is(true));
    }

    @Test(groups = {"BVT", "BL-448"},
            description = "Verifies that authorised dvsa user can see change driving licence link and date of birth " +
                    "information on trade user profile",
            dataProvider = "dvsaUserForPersonalDetails")
    public void dvsaUserCanSeeUserPersonalDetails(User user, boolean isChangeLinkDisplayed) throws IOException, URISyntaxException {

        //Given I'm on the Trade user New Profile Details page as authorised DVSA user
        motUI.profile.dvsaViewUserProfile(user, tester);

        //Then Driving licence information should be displayed
        assertThat(motUI.profile.page().isDrivingLicenceInformationIsDisplayed(), is(true));

        //And Change driving licence link should be displayed if appropriate
        assertThat(motUI.profile.page().isChangeDrivingLicenceLinkIsDisplayed(), is(isChangeLinkDisplayed));
    }

    @Test(groups = {"BVT", "BL-448"},
            description = "Verifies that authorised dvsa user can change trade user email on trade user profile",
            dataProvider = "dvsaUserForContactDetails")
    public void dvsaUserCanSeeUserContactDetails(User user) throws IOException, URISyntaxException {

        //Given I'm on the Trade user New Profile Details page as authorised DVSA user
        motUI.profile.dvsaViewUserProfile(user, tester);

        //Then Change email link should be displayed
        assertThat(motUI.profile.page().isChangeEmailLinkIsDisplayed(), is(true));
    }

    @Test(groups = {"BVT", "BL-448"},
            description = "Verifies that authorised user can see dvsa roles on dvsa user profile",
            dataProvider = "dvsaUserForContactDetails")
    public void dvsaUserCanSeeDvsaUserRoles(User user) throws IOException, URISyntaxException {

        //Given I'm on the Trade user New Profile Details page as authorised DVSA user
        motUI.profile.dvsaViewUserProfile(user, areaOffice2User);

        //Then Dvsa roles section should be displayed
        assertThat(motUI.profile.page().isDvsaRolesSectionIsDisplayed(), is(true));
    }

    @Test(groups = {"BVT", "BL-448"},
            description = "Verifies that authorised user can see qualification section on user profile")
    public void anyUserCanSeeQualificationsSection() throws IOException, URISyntaxException {

        //Given I'm on the Trade user New Profile Details page as area office user
        motUI.profile.dvsaViewUserProfile(motApi.user.createAreaOfficeOne("Ao1"), tester);

        //Then Qualification section should be displayed
        assertThat(motUI.profile.page().isQualificationStatusSectionIsDisplayed(), is(true));
    }

    @Test(groups = {"BVT", "BL-448"},
            description = "Verifies that user can see account security section on own user profile")
    public void anyUserCanSeeAccountSecuritySectionOnOwnProfile() throws IOException, URISyntaxException {

        //Given I'm on the New Profile Details page as a tester
        motUI.profile.viewYourProfile(motApi.user.createTester(site.getId()));

        //Then Account security section should be displayed
        assertThat(motUI.profile.page().isAccountSecuritySectionDisplayed(), is(true));
    }

    @Test(groups = {"BVT", "BL-448"},
            description = "Verifies that csco user can see account management section on other user profile")
    public void cscoUserCanSeeAccountManagementSectionOnAnyProfile() throws IOException, URISyntaxException {

        //Given I'm on the New Profile Details page as a csco
        motUI.profile.dvsaViewUserProfile(
                motApi.user.createCustomerServiceOfficer(false), motApi.user.createUserAsAreaOfficeTwo("ao2")
        );

        //Then Account management section should be displayed
        assertThat(motUI.profile.page().isAccountManagementSectionDisplayed(), is(true));
    }

    @Test(groups = {"BVT", "BL-448"},
            description = "Verifies that authorised dvsa user can see change qualification links " +
                    "on trade user profile",
            dataProvider = "dvsaUser")
    public void dvsaUserCanSeeChangeQualificationLinksOnTradeProfile(User user) throws IOException, URISyntaxException {

        //Given I'm on the New Profile Details page as logged user
        motUI.profile.dvsaViewUserProfile(user, tester);

        //Then Change qualification links should be displayed
        assertThat(motUI.profile.page().isChangeDrivingLicenceLinkIsDisplayed(), is(true));
    }

    @Test(groups = {"BVT", "BL-448"},
            description = "Verifies that authorised dvsa user can see manage roles link " +
                    "on other dvsa user profile",
            dataProvider = "dvsaUser")
    public void dvsaUserCanSeeManageRolesOnUserProfile(User user) throws IOException, URISyntaxException {

        //Given I'm on the New Profile Details page as logged user
        motUI.profile.dvsaViewUserProfile(user, tester);

        //Then Manage roles link should be displayed
        assertThat(motUI.profile.page().isChangeQualificationLinksDisplayed(), is(true));
    }

    @Test(testName = "2fa", groups = {"BVT", "BL-1963"})
    public void registered2faTradeUserCanSeeSecurityCardPanelOnOwnProfile()  throws IOException, URISyntaxException {

        // Given I have registered for two factor authentication
        User twoFactorTester = motApi.user.createTester(site.getId());
        motUI.authentication.registerAndSignInTwoFactorUser(twoFactorTester);

        // When I view my own profile
        motUI.profile.viewYourProfile(twoFactorTester);

        // Then the security card panel should be displayed
        assertThat(motUI.profile.page().isSecurityCardPanelDisplayed(), is(true));
    }

    @Test(testName = "2fa", groups = {"BVT", "BL-1963"})
    public void notRegistered2faTradeUserCanNotSeeSecurityCardPanelOnOwnProfile()  throws IOException, URISyntaxException {

        // Given I have not registered for two factor authentication
        User nonTwoFactorTester = motApi.user.createTester(site.getId());

        // When I view my own profile
        motUI.profile.viewYourProfile(nonTwoFactorTester);

        // Then the security card panel should not be displayed
        assertThat(motUI.profile.page().isSecurityCardPanelDisplayed(), is(false));
    }

    @Test(testName = "2fa", groups = {"BVT", "BL-1963"}, dataProvider = "dvsaUserForSecurityCard")
    public void dvsaCanSeeSecurityCardPanelOnRegistered2faTradeUserProfile(User dvsaUser)  throws IOException, URISyntaxException {

        step("Given a tester who has registered for two factor authentication");
        User twoFactorTester = motApi.user.createTester(site.getId());
        motUI.authentication.registerAndSignInTwoFactorUser(twoFactorTester);

        step("When I log in as a DVSA user and view their profile page");
        motUI.profile.dvsaViewUserProfile(dvsaUser, twoFactorTester);

        step("Then the security card panel should be displayed");
        assertThat(motUI.profile.page().isSecurityCardPanelDisplayed(), is(true));
    }

    @Test(testName = "2fa", groups = {"BVT", "BL-1963"}, dataProvider = "dvsaUserForSecurityCard")
    public void dvsaCannotSeeSecurityCardPanelOnNonRegistered2faTradeUserProfile(User dvsaUser)  throws IOException, URISyntaxException {

        // Given a tester who has not registered for two factor authentication

        // When I log in as a DVSA user and view their profile page
        motUI.profile.dvsaViewUserProfile(dvsaUser, tester);

        // Then the security card panel should not be displayed
        assertThat(motUI.profile.page().isSecurityCardPanelDisplayed(), is(false));
    }

    @Test(testName = "2fa", groups = {"BVT", "BL-1963"}, dataProvider = "dvsaUserForSecurityCard")
    public void dvsaCanNotSeeSecurityCardPanelOnOwnProfile(User dvsaUser)  throws IOException, URISyntaxException {
        // Given I am logged in as DVSA user

        // When I view my own profile
        motUI.profile.viewYourProfile(dvsaUser);

        // Then the security card panel should not be displayed
        assertThat(motUI.profile.page().isSecurityCardPanelDisplayed(), is(false));
    }

    @Test(testName = "2fa", groups = {"BVT", "BL-2354"})
    public void orderSecurityCardLinkShownToUserWhoGoneThroughLostForgottenJourney()  throws IOException {
        User twoFactorTester = motApi.user.createTester(site.getId());

        step("Given that I am on the Security card PIN page");
            motUI.authentication.gotoTwoFactorPinEntryPage(twoFactorTester);

        step("When I view my own profile");
            motUI.authentication.securityCard.signInWithoutSecurityCard(twoFactorTester);
            ProfilePage profilePage = motUI.profile.viewYourProfile(twoFactorTester);

        step("Then the order security card link displayed on profile");
            assertThat(profilePage.isOrderSecurityCardDisplayed(), is(true));
    }

    @DataProvider(name = "dvsaUserForPersonalDetails")
    private Object[][] dvsaUserForPersonalDetails() throws IOException {
        return new Object[][]{
                {motApi.user.createSchemeUser(false), true},
                {motApi.user.createAreaOfficeOne("Ao1"), true},
                {motApi.user.createVehicleExaminer("veguy", false), true},
                {motApi.user.createCustomerServiceOfficer(false), false}};
    }

    @DataProvider(name = "dvsaUserForContactDetails")
    private Object[][] dvsaUserForContactDetails() throws IOException {
        return new Object[][]{
                {motApi.user.createSchemeUser(false)},
                {motApi.user.createAreaOfficeOne("Ao1")},
                {motApi.user.createVehicleExaminer("veguy", false)},
                {motApi.user.createCustomerServiceOfficer(false)}};
    }

    @DataProvider(name = "dvsaUser")
    private Object[][] dvsaUserForChangeQualifications() throws IOException {
        return new Object[][]{
                {motApi.user.createSchemeUser(false)},
                {motApi.user.createAreaOfficeOne("Ao1")},
                {motApi.user.createVehicleExaminer("veguy", false)}};
    }

    @DataProvider(name = "dvsaUserForSecurityCard")
    private Object[][] dvsaUserForSecurityCard() throws IOException {
        return new Object[][]{
                {motApi.user.createSchemeUser(false)},
                {motApi.user.createSchemeManagerUser(false)},
                {motApi.user.createAreaOfficeOne("Ao1")},
                {motApi.user.createVehicleExaminer("veguy", false)},
                {motApi.user.createCustomerServiceOfficer(false)},
                {motApi.user.createCustomerServiceManager(false)}};
    }
}
