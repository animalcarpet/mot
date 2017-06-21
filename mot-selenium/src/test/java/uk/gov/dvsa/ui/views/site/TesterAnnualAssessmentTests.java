package uk.gov.dvsa.ui.views.site;

import org.joda.time.DateTime;
import org.testng.Assert;
import org.testng.annotations.BeforeMethod;
import org.testng.annotations.Test;
import uk.gov.dvsa.domain.model.Site;
import uk.gov.dvsa.domain.model.User;
import uk.gov.dvsa.ui.DslTest;
import uk.gov.dvsa.ui.pages.profile.annualassessmentcertificates.AnnualAssessmentCertificatesIndexPage;
import uk.gov.dvsa.ui.pages.vts.TestersAnnualAssessmentPage;

import java.io.IOException;
import java.net.URISyntaxException;

public class TesterAnnualAssessmentTests extends DslTest {

    private User tester;
    private Site site;
    private User ao1;

    @BeforeMethod(alwaysRun = true)
    private void setup() throws IOException {
        site = siteData.createSite();
        tester = motApi.user.createTester(site.getId());
        ao1 = motApi.user.createAreaOfficeOne("aooo1");
    }

    @Test(groups = {"Regression"},
        description = "Verifies that areaofficer can view Tester Annual Assessments")
    public void viewOneTesterAnnualAssessments() throws IOException, URISyntaxException {
        String certificateForGroupANumber = "123456789";
        String certificateForGroupAScore = "50";

        annualAssessmentCertificatesData.createAnnualAssessmentCertificateForGroupA(tester, certificateForGroupANumber, new DateTime().toString(), certificateForGroupAScore);
        annualAssessmentCertificatesData.createAnnualAssessmentCertificateForGroupB(tester, "123412341", new DateTime().toString(), "55");
        motUI.site.gotoPage(ao1, site.getIdAsString());

        AnnualAssessmentCertificatesIndexPage annualAssessmentCertificatesIndexPage = motUI.site
            .getVehicleTestingStationPage()
            .clickOnTestersAnnualAssessmentLink()
            .goToAnnualAssessmentCertificatesIndexPage(tester.getUsername(), "a");

        Assert.assertTrue(
                annualAssessmentCertificatesIndexPage.getFirstCertificateGroupANumber().equals(certificateForGroupANumber),
                "Group A table contains incorrect certificate number"
        );
        Assert.assertTrue(
                annualAssessmentCertificatesIndexPage.getFirstCertificateGroupAScore().equals(certificateForGroupAScore + "%"),
                "Group A table contains incorrect certificate score"
        );
        Assert.assertTrue(
                annualAssessmentCertificatesIndexPage.gerReturnButtonText().equals("Return to testers annual assessment"),
                "Incorrect text on return link"
        );
    }

    @Test(groups = {"Regression"},
            description = "Verifies that areaofficer can view Tester Annual Assessments")
    public void viewTesterAnnualAssessments() throws IOException, URISyntaxException {
        String certificateForGroupANumber = "123456789";
        String certificateForGroupAScore = "50";
        String dateAwarded = new DateTime().minusDays(1).toString("dd MMMM yyyy");

        annualAssessmentCertificatesData.createAnnualAssessmentCertificateForGroupA(tester, certificateForGroupANumber, dateAwarded, certificateForGroupAScore);
        annualAssessmentCertificatesData.createAnnualAssessmentCertificateForGroupB(tester, "123412341", new DateTime().toString(), "55");
        motUI.site.gotoPage(ao1, site.getIdAsString());

        TestersAnnualAssessmentPage testersAnnualAssessmentPage = motUI.site
                .getVehicleTestingStationPage()
                .clickOnTestersAnnualAssessmentLink();

        Assert.assertTrue(
                testersAnnualAssessmentPage.getFirstCertificateGroupAUserInformation().contains(tester.getUsername()),
                "Group A table contains incorrect certificate number"
        );
        Assert.assertTrue(
                testersAnnualAssessmentPage.getFirstCertificateGroupADateAwarded().equals(dateAwarded),
                "Group A table contains incorrect certificate score"
        );
    }
}
