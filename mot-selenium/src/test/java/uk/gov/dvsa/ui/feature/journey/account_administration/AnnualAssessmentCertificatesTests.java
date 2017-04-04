package uk.gov.dvsa.ui.feature.journey.account_administration;

import org.joda.time.DateTime;
import org.testng.Assert;
import org.testng.annotations.BeforeClass;
import org.testng.annotations.Test;
import uk.gov.dvsa.domain.model.User;
import uk.gov.dvsa.helper.RandomDataGenerator;
import uk.gov.dvsa.ui.DslTest;
import uk.gov.dvsa.ui.pages.profile.ProfilePage;
import uk.gov.dvsa.ui.pages.profile.annualassessmentcertificates.AnnualAssessmentCertificatesIndexPage;

import java.io.IOException;

public class AnnualAssessmentCertificatesTests extends DslTest {
    private User newUser;

    @Test(groups = {"BVT"},
            testName = "Add Annual Assessment Certificate",
            description = "test that user can add his annual assessment certificate"
    )
    public void userCanAddHisAnnualAssessment() throws IOException {
        //When I'm on my profile page
        newUser = motApi.user.createUserWithoutRole();
        ProfilePage page = motUI.profile.viewYourProfile(newUser);
        //And I add annual assessment certificate
        String certificateNumber = RandomDataGenerator.generateRandomNumber(15, 15);
        motUI.profile.annualAssessmentCertificates().
                addAnnualAssessmentCertificate(certificateNumber, 90, "4", "4", "2016");
        //Then the assessment should be saved
        Assert.assertTrue(motUI.profile.annualAssessmentCertificates()
                        .verifySavedAssessmentForGroupA(certificateNumber, "4 April 2016", "90%")
        );
    }

    @Test(groups = {"Regression"},
        testName = "Add Annual Assessment Certificate",
        description = "test that user can add his annual assessment certificate"
    )
    public void userCanEditHisAnnualAssessment() throws IOException {
        //When I'm on my profile page
        User user = motApi.user.createUserWithoutRole();
        ProfilePage page = motUI.profile.viewYourProfile(user);

        //And I edit annual assessment certificate
        String oldCertificateNumber = RandomDataGenerator.generateRandomNumber(15, 15);
        DateTime date = DateTime.now().minusMonths(1);
        annualAssessmentCertificatesData.createAnnualAssessmentCertificateForGroupB(
            user,
            oldCertificateNumber,
            date.toString("YYYY'-'MM'-'dd"),
            "80"
        );
        String newCertificateNumber = RandomDataGenerator.generateRandomNumber(15, 15);
        motUI.profile.annualAssessmentCertificates().
            editAnnualAssessmentCertificate(oldCertificateNumber, newCertificateNumber, 90, "4", "4", "2016");

        //Then the assessment changes should be saved
        String successMessage = "Group B annual assessment certificate changed successfully.";
        Assert.assertTrue(motUI.profile.annualAssessmentCertificates()
            .verifySavedAssessmentForGroupB(newCertificateNumber, "4 April 2016", "90%"));
        Assert.assertTrue(motUI.profile.annualAssessmentCertificates()
            .verifyChangedAssessment(successMessage));

    }

    @Test(groups = {"Regression"},
            testName = "Remove Annual Assessment Certificate",
            description = "test that user can remove his annual assessment certificate"
    )
    public void testerCanRemoveHisAnnualAssessment() throws IOException {
        User user = motApi.user.createUserWithoutRole();
        String certificateNumber = RandomDataGenerator.generateRandomNumber(15, 15);
        String date = "2016-05-02";
        String score = "27";
        String successfulMessage = "Group B annual assessment certificate removed successfully.";

        // Given an annual assessment certificate for group B is added for user
        annualAssessmentCertificatesData.createAnnualAssessmentCertificateForGroupB(
            user,
            certificateNumber,
            date,
            score
        );

        // When I remove annualAssessmentCertificate
        motUI.profile.viewYourProfile(user);
        AnnualAssessmentCertificatesIndexPage annualAssessmentCertificatesIndexPage =
            motUI.profile.annualAssessmentCertificates()
                .removeAnnualAssessmentCertificateGroupB(certificateNumber);

        //Then Certificate will be removed (so all table will not by displayed)
        //And correct message displayed
        Assert.assertTrue(annualAssessmentCertificatesIndexPage
            .verifySuccessfulMessageForRemoveGroupBCertificate(successfulMessage));
        Assert.assertTrue(motUI.profile.annualAssessmentCertificates()
            .verifyRemovedAssessment());
    }
}
