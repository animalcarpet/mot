package uk.gov.dvsa.ui.feature.journey.account_administration;

import org.testng.Assert;
import org.testng.annotations.BeforeClass;
import org.testng.annotations.Test;
import uk.gov.dvsa.domain.model.AeDetails;
import uk.gov.dvsa.domain.model.Site;
import uk.gov.dvsa.domain.model.User;
import uk.gov.dvsa.helper.RandomDataGenerator;
import uk.gov.dvsa.ui.DslTest;
import uk.gov.dvsa.ui.pages.profile.qualificationdetails.QualificationDetailsConfirmationPage;

import java.io.IOException;

import static org.hamcrest.MatcherAssert.assertThat;
import static org.hamcrest.core.Is.is;

public class QualificationDetailsTests extends DslTest {

    private Site testSite;
    private Site testSite2;

    @BeforeClass(alwaysRun = true)
    private void setup() throws IOException {
        AeDetails aeDetails = aeData.createAeWithDefaultValues();
        testSite = siteData.createNewSite(aeDetails.getId(), "Test_Site");
        testSite2 = siteData.createNewSite(aeDetails.getId(), "Test_Site2");
    }

    @Test(groups = {"BVT", "Regression"},
            testName = "2fa",
            description = "test that a user with no qialification can add their qualification details")
    public void userCanAddNewQualificationDetails() throws IOException
    {
        step("Given I am on my profile page as user who has a new certificate");
        User tester = userData.createUserWithoutRole();
        motUI.profile.viewYourProfile(tester);

        step("And I add the details of the certificate");
        String certificateNumber = RandomDataGenerator.generateRandomNumber(15, 12);
        QualificationDetailsConfirmationPage confirmationPage = motUI.profile.qualificationDetails()
                .addQualificationDetailsForGroupA(certificateNumber, testSite2.getSiteNumber(), "3", "4", "2016");
        confirmationPage.returnToQualificationDetailsPage();

        step("Then the certificate should be saved");
        Assert.assertTrue(motUI.profile.qualificationDetails().verifyCertificateAddedForGroupA(certificateNumber, "3 April 2016"));
    }

    @Test(groups = {"BVT", "Regression"},
            testName = "non-2fa",
            description = "test that a user with no qialification can add their qualification details")
    public void userCanAddNewQualificationDetails2faOff() throws IOException
    {
        step("Given I am on my profile page as user who has a new certificate");
        User tester = userData.createUserWithoutRole();
        motUI.profile.viewYourProfile(tester);

        step("And I add the details of the certificate");
        String certificateNumber = RandomDataGenerator.generateRandomNumber(15, 12);
        motUI.profile.qualificationDetails()
                .addQualificationDetailsForGroupA(certificateNumber, testSite2.getSiteNumber(), "3", "4", "2016");

        step("Then the certificate should be saved");
        Assert.assertTrue(motUI.profile.qualificationDetails().verifyCertificateAddedForGroupA(certificateNumber, "3 April 2016"));
    }

    @Test(groups = {"BVT"})
    public void orderCardLinkNotDisplayedWhereUserNavigatesDirectlyToConfirmationPage() throws IOException {
        step("Given that a user navigates directly to confirmation page");
        User tester = userData.createTester(testSite.getId());
        motUI.profile.viewYourProfile(tester);
        motUI.profile.qualificationDetails().confirmationPage(tester);

        step("Then the Order card link should not be displayed");
        assertThat("Order card link should not be visible", motUI.profile.qualificationDetails()
            .isOrderCardLinkDisplayed(), is(false));
    }

    @Test(testName = "2fa", groups = {"BVT", "Regression"})
    public void userWithNoSecurityCardOrdersSeesOrderSectionOnConfirmation() throws IOException {

        step("Given I am a user who has a new certificate with no security card orders");
        User tester = userData.createUserWithoutRole();

        step("When I'm on my profile page");
        motUI.profile.viewYourProfile(tester);

        step("And I add the details of the certificate");
        String certificateNumber = RandomDataGenerator.generateRandomNumber(15, 12);
        QualificationDetailsConfirmationPage confirmationPage = motUI.profile.qualificationDetails()
                .addQualificationDetailsForGroupA(certificateNumber, testSite2.getSiteNumber(), "3", "4", "2016");

        step("Then I will be presented with the order security card section");
        Assert.assertTrue(confirmationPage.isOrderCardLinkDisplayed());
    }

    @Test(groups = {"BVT"},
        testName = "Edit qualification details",
        description = "test that new newUser can edit his qualification details"
    )
    public void userCanEditHisQualificationDetails() throws IOException {
        step("Given I am a user who already has added a certificate for step A");
        User user = userData.createUserWithoutRole();
        qualificationDetailsData.createQualificationCertificateForGroupA(
                user, "1234123412341234", "2016-04-01", testSite.getSiteNumber()
        );

        step("When I navigate to my profile page");
        motUI.profile.viewYourProfile(user);

        step("And I change the details of that certificate");
        String certificateNumber = RandomDataGenerator.generateRandomNumber(15, 12);
        motUI.profile.qualificationDetails()
            .changeQualificationDetailsForGroupA(certificateNumber, testSite2.getSiteNumber(), "3", "4", "2016");

        step("Then the details should be saved");
        Assert.assertTrue(motUI.profile.qualificationDetails().verifyDetailsChangedForGroupA(certificateNumber, "3 April 2016"));
    }

    @Test(groups = {"BVT"},
        testName = "Edit qualification details",
        description = "test that new newUser can edit his qualification details"
    )
    public void dvsaUserCanEditQualificationDetailsOfAnotherUser() throws IOException {
        step("Given a user who already has added a certificate for step A");
        User user = userData.createUserWithoutRole();
        qualificationDetailsData.createQualificationCertificateForGroupA(
                user, "1234123412341234", "2016-04-01", testSite.getSiteNumber()
        );

        step("When I visit the user's page as a DVSA user");
        User areaOffice1User = userData.createAreaOfficeOne("AreaOffice1");
        motUI.profile.dvsaViewUserProfile(areaOffice1User, user);

        step("And I change the details of this certificate");
        String certificateNumber = RandomDataGenerator.generateRandomNumber(15, 12);
        motUI.profile.qualificationDetails()
            .changeQualificationDetailsForGroupA(certificateNumber, testSite2.getSiteNumber(), "4", "4", "2016");

        step("Then the details should be saved");
        Assert.assertTrue(motUI.profile.qualificationDetails().verifyDetailsChangedForGroupA(certificateNumber, "4 April 2016"));
    }

    @Test(groups = {"BVT"},
        testName = "Permitted user can view demo test requests"
    )
    public void permittedUserCanViewDemoTestRequests() throws IOException {
        step("Given I add a new certificate for new user");
        User userB = userData.createUserWithoutRole();
        String certificateNumberB = RandomDataGenerator.generateRandomNumber(15, 12);
        qualificationDetailsData.createQualificationCertificateForGroupB(
            userB, certificateNumberB, "2016-04-05", testSite.getSiteNumber()
        );
        motUI.profile.dvsaViewUserProfile(userData.createAreaOfficeOne("BAo1"), userB);
        motUI.profile.qualificationDetails().goToQualificationDetailsPage();
        int certificateAmount = motUI.profile.qualificationDetails().countUserCertificates();

        step("When I visit demo test requests page as a DVSA user");
        User areaOffice1User = userData.createAreaOfficeOne("AreaOffice1");
        motUI.demoTestRequests.visitDemoTestRequestsPage(areaOffice1User);

        step("Then I will be able to see the newly added certificates");
        Assert.assertTrue(motUI.demoTestRequests.certificatesDisplayAmountCorrectForUser(userB, certificateAmount));
    }

    @Test(groups = {"BVT"},
            testName = "Remove qualification details",
            description = "test that dvsa user can remove qualification details"
    )
    public void dvsaUserCanRemoveQualificationDetailsOfAnotherUser() throws IOException {
        step("Given a certificate for group B is added for user");
        User user = userData.createUserWithoutRole();
        qualificationDetailsData.createQualificationCertificateForGroupB(
                user, "9994123412341234", "2016-04-01", testSite.getSiteNumber()
        );

        step("When I visit user's page as a DVSA user");
        motUI.profile.dvsaViewUserProfile(userData.createAreaOfficeOne("certAo1"), user);

        step("And I remove certificate");
        String certificateNumber = RandomDataGenerator.generateRandomNumber(15, 12);
        motUI.profile.qualificationDetails()
                .removeQualificationDetailsForGroupB(certificateNumber);

        step("Then the details should be saved");
        Assert.assertTrue(motUI.profile.qualificationDetails().verifyDetailsAfterRemovedGroupBCertificate());
    }
}
