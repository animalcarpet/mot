package uk.gov.dvsa.ui.views;

import org.joda.time.DateTime;
import org.testng.annotations.BeforeMethod;
import org.testng.annotations.Test;
import uk.gov.dvsa.domain.model.AeDetails;
import uk.gov.dvsa.domain.model.Site;
import uk.gov.dvsa.domain.model.User;
import uk.gov.dvsa.domain.model.mot.CancelTestReason;
import uk.gov.dvsa.domain.model.mot.ReasonForVehicleRefusal;
import uk.gov.dvsa.domain.model.mot.TestOutcome;
import uk.gov.dvsa.domain.api.response.Vehicle;
import uk.gov.dvsa.ui.DslTest;

import java.io.IOException;
import java.net.URISyntaxException;

import static org.hamcrest.MatcherAssert.assertThat;
import static org.hamcrest.core.Is.is;

public class DeclarationStatementViewTests extends DslTest {
    private Site site;
    private Vehicle vehicle;

    @BeforeMethod(alwaysRun = true)
    private void setupTestData() throws IOException {
        AeDetails aeDetails = aeData.createAeWithDefaultValues();
        site = siteData.createNewSite(aeDetails.getId(), "TestSite");
        User tester = motApi.user.createTester(site.getId());
        vehicle = vehicleData.getNewVehicle(tester);
    }

    @Test (testName="2faHardStopDisabled", groups = {"2fa"})
    public void displayStatementAtTestSummaryPage() throws IOException, URISyntaxException {

        //Given I complete a normal test as a tester
        motUI.normalTest.conductTestPass(motApi.user.createNon2FaTester(site.getId()), vehicle);

        //And I am on the Test Summary Page

        //Then I should be presented with the declaration statement
        assertThat(motUI.normalTest.isDeclarationStatementDisplayed(), is(true));
    }

    @Test (groups = {"2fa"})
    public void display2faStatementAtTestSummaryPage() throws IOException, URISyntaxException {

        //Given I complete a normal test as a 2FA tester
        User twoFactorTester = motApi.user.createTester(site.getId());
        motUI.normalTest.conductTestPass(twoFactorTester, vehicle);

        //And I am on the Test Summary Page

        //Then I should be presented with the 2fa declaration statement
        assertThat(motUI.normalTest.isDeclarationStatementFor2FaDisplayed(), is(true));
    }

    @Test (groups = {"BVT"})
    public void displayStatementAtReTestSummaryPage() throws IOException, URISyntaxException {

        //Given I have a vehicle with a failed MOT test
        User tester = motApi.user.createTester(site.getId());
        motApi.createTest(tester, site.getId(), vehicle, TestOutcome.FAILED, 12345, DateTime.now());

        //When I conduct a retest on the vehicle and view the summary page
        motUI.retest.conductRetestPass(vehicle, tester);

        //Then I should be presented with the declaration statement
        assertThat(motUI.retest.isDeclarationStatementDisplayed(), is(true));
    }

    @Test (testName="2faHardStopDisabled", groups = {"2fa"})
    public void statementShouldNotBeDisplayedForTrainingTest() throws IOException, URISyntaxException {

        //Given I am on the review Page of training test
        motUI.normalTest.conductTrainingTest(motApi.user.createNon2FaTester(site.getId()), vehicle);

        //Then I should NOT be presented with the declaration statement
        assertThat(motUI.normalTest.isDeclarationStatementDisplayed(), is(false));
    }

    @Test (groups = {"BVT"})
    public void displayStatementWhenAbortingTest() throws IOException, URISyntaxException {

        //Given I have an in progress Mot Test
        motUI.normalTest.startTest(motApi.user.createTester(site.getId()));

        //When I cancel the Test with [INSPECTION MAY DANGEROUS] Reason
        motUI.normalTest.cancelTestWithReason(CancelTestReason.DANGEROUS_OR_CAUSE_DAMAGE);

        //Then I should be presented with the declaration statement
        assertThat(motUI.normalTest.isDeclarationStatementDisplayed(), is(true));
    }

    @Test (groups = {"2fa"})
    public void display2faStatementWhenAbortingTest() throws IOException, URISyntaxException {

        //Given I have an in progress Mot Test
        User twoFactorTester = motApi.user.createTester(site.getId());
        motUI.normalTest.startTest(twoFactorTester);

        //When I cancel the Test with [INSPECTION MAY DANGEROUS] Reason
        motUI.normalTest.cancelTestWithReason(CancelTestReason.DANGEROUS_OR_CAUSE_DAMAGE);

        //Then I should be presented with the declaration statement
        assertThat(motUI.normalTest.isDeclarationStatementDisplayed(), is(true));
    }

    @Test (testName="2faHardStopDisabled", groups = {"2fa"})
    public void statementShouldNotBeDisplayedOnTestRefusal() throws IOException, URISyntaxException {

        //Given I refuse to test a vehicle
        motUI.normalTest.refuseToTestVehicle(motApi.user.createNon2FaTester(site.getId()), vehicle, ReasonForVehicleRefusal.INSPECTION_MAY_BE_DANGEROUS);

        //Then I should Not be presented with the declaration statement
        assertThat(motUI.normalTest.isDeclarationStatementDisplayed(), is(false));
    }

    @Test (groups = {"Regression"})
    public void displayStatementAtContingencySummaryPage() throws IOException, URISyntaxException {

        //Given I start a contingency test
        motUI.contingency.testPage(motApi.user.createTester(site.getId()));

        //When I complete a contingency test and view the summary page
        motUI.contingency.recordTest("12345A", DateTime.now().minusHours(1), vehicle);

        //Then I should be presented with the declaration statement
        assertThat(motUI.contingency.isDeclarationStatementDisplayed(), is(true));
    }

    @Test (testName = "2faHardStopDisabled", groups = {"2fa"})
    public void replacementCertificateDeclarationStatement() throws IOException, URISyntaxException {

        //Given I have completed an Mot Test
        User tester = motApi.user.createNon2FaTester(site.getId());
        String testId = motApi.createTest(tester, site.getId(), vehicle, TestOutcome.PASSED, 123456,
                DateTime.now()).getMotTestNumber();

        //When I create a replacement test certificate
        motUI.certificate.createReplacementCertificate(tester, vehicle, testId);

        //Then I should be presented with the declaration statement on the review page
        assertThat(motUI.certificate.isDeclarationStatementDisplayed(), is(true));
    }

    @Test (groups = {"2fa"})
    public void displayDeclarationStatementFor2faUserOnReplacementCertificatePage() throws IOException, URISyntaxException {

        //Given I have completed an Mot Test as 2fa user
        int siteId = siteData.createSite().getId();
        User twoFactorTester = motApi.user.createTester(siteId);

        String testId = motApi.createTest(twoFactorTester, siteId, vehicle, TestOutcome.PASSED, 123456,
                DateTime.now()).getMotTestNumber();

        //When I create a replacement test certificate
        motUI.certificate.createReplacementCertificate(twoFactorTester, vehicle, testId);

        //Then I should be presented with the 2fa declaration statement on the review page
        assertThat(motUI.certificate.isDeclarationStatementFor2FaDisplayed(), is(true));
    }
}
