package uk.gov.dvsa.ui.views;

import org.testng.annotations.BeforeClass;
import org.testng.annotations.DataProvider;
import org.testng.annotations.Test;
import uk.gov.dvsa.domain.model.AeDetails;
import uk.gov.dvsa.domain.model.Site;
import uk.gov.dvsa.domain.model.User;
import uk.gov.dvsa.helper.RandomDataGenerator;
import uk.gov.dvsa.ui.DslTest;

import java.io.IOException;
import java.net.URISyntaxException;

import static org.hamcrest.MatcherAssert.assertThat;
import static org.hamcrest.core.Is.is;

public class ProfilePageViewTests extends DslTest {

    private User tester;
    private User schemeManager;
    private User areaOffice1User;
    private User areaOffice2User;
    private User vehicleExaminer;
    private User csco;
    private User aedm;
    private String randomName = RandomDataGenerator.generateRandomString(5, System.nanoTime());

    @BeforeClass(alwaysRun = true)
    private void setup() throws IOException {
        AeDetails aeDetails = aeData.createAeWithDefaultValues();
        Site site = siteData.createNewSite(aeDetails.getId(), "Test_Site");
        tester = userData.createTester(site.getId());
        schemeManager = userData.createSchemeUser(false);
        areaOffice1User = userData.createAreaOfficeOne(randomName);
        areaOffice2User = userData.createAreaOfficeTwo(randomName);
        vehicleExaminer = userData.createVehicleExaminer(randomName, false);
        csco = userData.createCustomerServiceOfficer(false);
        aedm = userData.createAedm(aeDetails.getId(), randomName, false);
    }

    @Test(groups = {"BVT"}, description = "VM-10334")
    public void testerQualificationStatusDisplayedOnProfilePage() throws IOException {

        //Given I'm on the Your Profile Details page
        motUI.profile.viewYourProfile(tester);

        //Then I should be able to see the qualification status
        assertThat(motUI.profile.isTesterQualificationStatusDisplayed(), is(true));
    }

    @Test(groups = {"BVT", "VM-12321"},
            description = "Verifies that trade user can check own roles via roles and associations link " +
                    "on it's own profile page")
    public void tradeUserCanViewHisOwnRolesAndAssociations() throws IOException, URISyntaxException {

        //Given I'm on the Your Profile Details page
        motUI.profile.viewYourProfile(tester);

        //When I click on Roles and Associations link
        motUI.profile.page().clickRolesAndAssociationsLink();

        //Then roles should be displayed
        assertThat(motUI.manageRoles.isRolesTableContainsValidTesterData(), is(true));
    }

    @Test(testName = "NewProfile", groups = {"BVT", "BL-448"},
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

    @Test(testName = "NewProfile", groups = {"BVT", "BL-448"},
            description = "Verifies that authorised dvsa user can change trade user email on trade user profile",
            dataProvider = "dvsaUserForContactDetails")
    public void dvsaUserCanSeeUserContactDetails(User user) throws IOException, URISyntaxException {

        //Given I'm on the Trade user New Profile Details page as authorised DVSA user
        motUI.profile.dvsaViewUserProfile(user, tester);

        //Then Change email link should be displayed
        assertThat(motUI.profile.page().isChangeEmailLinkIsDisplayed(), is(true));
    }

    @Test(testName = "NewProfile", groups = {"BVT", "BL-448"},
            description = "Verifies that authorised user can see dvsa roles on dvsa user profile",
            dataProvider = "dvsaUserForContactDetails")
    public void dvsaUserCanSeeDvsaUserRoles(User user) throws IOException, URISyntaxException {

        //Given I'm on the Trade user New Profile Details page as authorised DVSA user
        motUI.profile.dvsaViewUserProfile(user, areaOffice2User);

        //Then Dvsa roles section should be displayed
        assertThat(motUI.profile.page().isDvsaRolesSectionIsDisplayed(), is(true));
    }

    @Test(testName = "NewProfile", groups = {"BVT", "BL-448"},
            description = "Verifies that authorised user can see qualification section on user profile",
            dataProvider = "anyUserForQualification")
    public void anyUserCanSeeQualificationsSection(User user) throws IOException, URISyntaxException {

        //Given I'm on the Trade user New Profile Details page as authorised DVSA user
        motUI.profile.dvsaViewUserProfile(user, tester);

        //Then Qualification section should be displayed
        assertThat(motUI.profile.page().isQualificationStatusSectionIsDisplayed(), is(true));
    }

    @Test(testName = "NewProfile", groups = {"BVT", "BL-448"},
            description = "Verifies that user can see account security section on own user profile",
            dataProvider = "anyUserForAccountSecurity")
    public void anyUserCanSeeAccountSecuritySectionOnOwnProfile(User user) throws IOException, URISyntaxException {

        //Given I'm on the New Profile Details page as logged user
        motUI.profile.viewYourProfile(user);

        //Then Account security section should be displayed
        assertThat(motUI.profile.page().isAccountSecuritySectionDisplayed(), is(true));
    }

    @Test(testName = "NewProfile", groups = {"BVT", "BL-448"},
            description = "Verifies that csco user can see account management section on other user profile",
            dataProvider = "anyUserForAccountManagement")
    public void cscoUserCanSeeAccountManagementSectionOnAnyProfile(User user) throws IOException, URISyntaxException {

        //Given I'm on the New Profile Details page as logged user
        motUI.profile.dvsaViewUserProfile(csco, user);

        //Then Account management section should be displayed
        assertThat(motUI.profile.page().isAccountManagementSectionDisplayed(), is(true));
    }

    @Test(testName = "NewProfile", groups = {"BVT", "BL-448"},
            description = "Verifies that authorised dvsa user can see change qualification links " +
                    "on trade user profile",
            dataProvider = "dvsaUserForChangeQualifications")
    public void dvsaUserCanSeeChangeQualificationLinksOnTradeProfile(User user) throws IOException, URISyntaxException {

        //Given I'm on the New Profile Details page as logged user
        motUI.profile.dvsaViewUserProfile(user, tester);

        //Then Change qualification links should be displayed
        assertThat(motUI.profile.page().isChangeDrivingLicenceLinkIsDisplayed(), is(true));
    }

    @Test(testName = "NewProfile", groups = {"BVT", "BL-448"},
            description = "Verifies that authorised dvsa user can see manage roles link " +
                    "on other dvsa user profile",
            dataProvider = "dvsaUserForManageRoles")
    public void dvsaUserCanSeeManageRolesOnUserProfile(User user) throws IOException, URISyntaxException {

        //Given I'm on the New Profile Details page as logged user
        motUI.profile.dvsaViewUserProfile(user, tester);

        //Then Manage roles link should be displayed
        assertThat(motUI.profile.page().isChangeQualificationLinksDisplayed(), is(true));
    }

    @DataProvider(name = "dvsaUserForPersonalDetails")
    private Object[][] dvsaUserForPersonalDetails() throws IOException {
        return new Object[][]{
                {schemeManager, true},
                {areaOffice1User, true},
                {vehicleExaminer, true},
                {csco, false}};
    }

    @DataProvider(name = "dvsaUserForContactDetails")
    private Object[][] dvsaUserForContactDetails() throws IOException {
        return new Object[][]{
                {schemeManager},
                {areaOffice1User},
                {vehicleExaminer},
                {csco}};
    }

    @DataProvider(name = "dvsaUserForChangeQualifications")
    private Object[][] dvsaUserForChangeQualifications() throws IOException {
        return new Object[][]{
                {schemeManager},
                {areaOffice1User},
                {vehicleExaminer}};
    }

    @DataProvider(name = "dvsaUserForManageRoles")
    private Object[][] dvsaUserForManageRoles() throws IOException {
        return new Object[][]{
                {schemeManager},
                {areaOffice1User},
                {vehicleExaminer}};
    }

    @DataProvider(name = "anyUserForQualification")
    private Object[][] anyUserForQualification() throws IOException {
        return new Object[][]{
                {schemeManager},
                {areaOffice1User},
                {vehicleExaminer},
                {aedm},
                {csco}};
    }

    @DataProvider(name = "anyUserForAccountSecurity")
    private Object[][] anyUserForAccountSecurity() throws IOException {
        return new Object[][]{
                {schemeManager},
                {areaOffice1User},
                {vehicleExaminer},
                {csco},
                {aedm},
                {tester}};
    }

    @DataProvider(name = "anyUserForAccountManagement")
    private Object[][] anyUserForAccountManagement() throws IOException {
        return new Object[][]{
                {schemeManager},
                {areaOffice1User},
                {vehicleExaminer},
                {aedm},
                {tester}};
    }
}
