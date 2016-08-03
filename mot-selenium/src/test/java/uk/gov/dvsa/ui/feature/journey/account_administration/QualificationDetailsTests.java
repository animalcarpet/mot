package uk.gov.dvsa.ui.feature.journey.account_administration;

import org.testng.Assert;
import org.testng.annotations.BeforeClass;
import org.testng.annotations.Test;
import uk.gov.dvsa.domain.model.AeDetails;
import uk.gov.dvsa.domain.model.Site;
import uk.gov.dvsa.domain.model.User;
import uk.gov.dvsa.helper.RandomDataGenerator;
import uk.gov.dvsa.ui.DslTest;
import java.io.IOException;

public class QualificationDetailsTests extends DslTest {

    private User newUser;
    private Site testSite;
    private AeDetails aeDetails;
    private Site testSite2;
    private User areaOffice1User;

    @BeforeClass(alwaysRun = true)
    private void setup() throws IOException {
        aeDetails = aeData.createAeWithDefaultValues();
        testSite = siteData.createNewSite(aeDetails.getId(), "Test_Site");
        testSite2 = siteData.createNewSite(aeDetails.getId(), "Test_Site2");
        newUser = userData.createUserWithoutRole();
        areaOffice1User = userData.createAreaOfficeOne("AreaOffice1");
        qualificationDetailsData.createQualificationCertificateForGroupA(
            newUser, "1234123412341234", "2016-04-01", testSite.getSiteNumber());

    }

    @Test(groups = {"BVT"},
        testName = "Edit qualification details",
        description = "test that new newUser can edit his qualification details"
    )
    public void userCanEditHisQualificationDetails() throws IOException {
        // Given my certificate for group A is already added

        // When I'm on my profile page
        motUI.profile.viewYourProfile(newUser);

        // And I chage the details of this certificate
        String certificateNumber = RandomDataGenerator.generateRandomNumber(15, 12);
        motUI.profile.qualificationDetails()
            .changeQualificationDetailsForGroupA(certificateNumber, testSite2.getSiteNumber(), "3", "4", "2016");

        // Then the details should be saved
        Assert.assertTrue(motUI.profile.qualificationDetails().verifyDetailsChangedForGroupA(certificateNumber, "3 April 2016"));
    }

    @Test(groups = {"BVT"},
        testName = "Edit qualification details",
        description = "test that new newUser can edit his qualification details"
    )
    public void dvsaUserCanEditQualificationDetailsOfAnotherUser() throws IOException {
        // Given a certificate for group A is already added for user

        // When I visit user's page
        motUI.profile.dvsaViewUserProfile(areaOffice1User, newUser);

        // And I chage the details of this certificate
        String certificateNumber = RandomDataGenerator.generateRandomNumber(15, 12);
        motUI.profile.qualificationDetails()
            .changeQualificationDetailsForGroupA(certificateNumber, testSite2.getSiteNumber(), "4", "4", "2016");

        // Then the details should be saved
        Assert.assertTrue(motUI.profile.qualificationDetails().verifyDetailsChangedForGroupA(certificateNumber, "4 April 2016"));
    }

    @Test(groups = {"BVT"},
        testName = "Permitted user can view demo test requests"
    )
    public void permittedUserCanViewDemoTestRequests() throws IOException {
        // Given I add a new certificate for new user
        User userB = userData.createUserWithoutRole();
        String certificateNumberB = RandomDataGenerator.generateRandomNumber(15, 12);
        qualificationDetailsData.createQualificationCertificateForGroupB(
            userB, certificateNumberB, "2016-04-05", testSite.getSiteNumber());
        motUI.profile.dvsaViewUserProfile(areaOffice1User, userB);
        motUI.profile.qualificationDetails().goToQualificationDetailsPage();
        int certificateAmount = motUI.profile.qualificationDetails().countUserCertificates();

        // When I visit demo test requests page
        motUI.demoTestRequests.visitDemoTestRequestsPage(areaOffice1User);

        // Then I will be able to see the newly added certificates
        Assert.assertTrue(motUI.demoTestRequests.certificatesDisplayAmountCorrectForUser(userB, certificateAmount));
    }

    @Test(groups = {"BVT"},
            testName = "Remove qualification details",
            description = "test that dvsa user can remove qualification details"
    )
    public void dvsaUserCanRemoveQualificationDetailsOfAnotherUser() throws IOException {
        // Given a certificate for group B is added for user
        qualificationDetailsData.createQualificationCertificateForGroupB(
                newUser, "9994123412341234", "2016-04-01", testSite.getSiteNumber());

        // When I visit user's page
        motUI.profile.dvsaViewUserProfile(areaOffice1User, newUser);

        // And I remove certificate
        String certificateNumber = RandomDataGenerator.generateRandomNumber(15, 12);
        motUI.profile.qualificationDetails().removeQualificationDetailsForGroupB(certificateNumber);

        // Then the details should be saved
        Assert.assertTrue(motUI.profile.qualificationDetails().verifyDetailsAfterRemovedGroupBCertificate());
    }
}