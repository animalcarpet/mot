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
import uk.gov.dvsa.domain.model.vehicle.Vehicle;
import uk.gov.dvsa.ui.DslTest;

import java.io.IOException;
import java.net.URISyntaxException;

import static org.hamcrest.MatcherAssert.assertThat;
import static org.hamcrest.core.Is.is;

public class DeclarationStatementViewTests extends DslTest {
    private Site site;
    private User tester;
    private Vehicle vehicle;

    @BeforeMethod(alwaysRun = true)
    private void setupTestData() throws IOException {
        AeDetails aeDetails = aeData.createAeWithDefaultValues();
        site = siteData.createNewSite(aeDetails.getId(), "TestSite");
        tester = userData.createTester(site.getId());
        vehicle = vehicleData.getNewVehicle(tester);
    }

    @Test (testName = "OldRFRTest", groups = {"BVT"})
    public void displayStatementAtTestSummaryPage() throws IOException, URISyntaxException {

        //Given I complete a normal test as a tester
        motUI.normalTest.conductTestPass(userData.createTester(site.getId()), vehicle);

        //And I am on the Test Summary Page

        //Then I should be presented with the declaration statement
        assertThat(motUI.normalTest.isDeclarationStatementDisplayed(), is(true));
    }

    @Test (testName = "2fa", groups = {"BVT"})
    public void display2faStatementAtTestSummaryPage() throws IOException, URISyntaxException {

        //Given I complete a normal test as a 2FA tester
        User twoFactorTester = userData.createTester(site.getId());
        motUI.authentication.registerAndSignInTwoFactorUser(twoFactorTester);
        motUI.normalTest.conductTestPass(twoFactorTester, vehicle);

        //And I am on the Test Summary Page

        //Then I should be presented with the 2fa declaration statement
        assertThat(motUI.normalTest.isDeclarationStatementFor2FaDisplayed(), is(true));
    }

    @Test (testName = "OldRFRTest", groups = {"BVT"})
    public void displayStatementAtReTestSummaryPage() throws IOException, URISyntaxException {

        //Given I have a vehicle with a failed MOT test
        User tester = userData.createTester(site.getId());
        motApi.createTest(tester, site.getId(), vehicle, TestOutcome.FAILED, 12345, DateTime.now());

        //When I conduct a retest on the vehicle and view the summary page
        motUI.retest.conductRetestPass(vehicle, tester);

        //Then I should be presented with the declaration statement
        assertThat(motUI.retest.isDeclarationStatementDisplayed(), is(true));
    }

    @Test (testName = "OldRFRTest", groups = {"BVT"})
    public void displayStatementAtChangeVehicleDetailsSummary() throws IOException, URISyntaxException {
        User nonTwoFactorTester = userData.createTester(site.getId());

        //Given I change the vehicle details
        motUI.normalTest.changeVehicleDetails(nonTwoFactorTester, vehicle);

        //When I submit the change

        //Then I should be presented with the declaration statement
        assertThat(motUI.normalTest.isDeclarationStatementDisplayed(), is(true));
    }

    @Test (testName = "2fa", groups = {"BVT"})
    public void displayStatementFor2FaUserAtChangeVehicleDetailsSummary() throws IOException, URISyntaxException {

        //Given I am 2fa tester
        int siteId = siteData.createSite().getId();
        User twoFactorTester = userData.createTester(siteId);
        motUI.authentication.registerAndSignInTwoFactorUser(twoFactorTester);

        //Given I change the vehicle details
        motUI.normalTest.changeVehicleDetails(twoFactorTester, vehicle);

        //When I submit the change

        //Then I should be presented with 2fa declaration statement
        assertThat(motUI.normalTest.isDeclarationStatementFor2FaDisplayed(), is(true));
    }

    @Test (testName = "OldRFRTest", groups = {"Regression"})
    public void statementShouldNotBeDisplayedForTrainingTest() throws IOException, URISyntaxException {

        //Given I am on the review Page of training test
        motUI.normalTest.conductTrainingTest(userData.createTester(site.getId()), vehicle);

        //Then I should NOT be presented with the declaration statement
        assertThat(motUI.normalTest.isDeclarationStatementDisplayed(), is(false));
    }

    @Test (testName = "OldRFRTest", groups = {"BVT"})
    public void displayStatementWhenAbortingTest() throws IOException, URISyntaxException {

        //Given I have an in progress Mot Test
        motUI.normalTest.startTest();

        //When I cancel the Test with [INSPECTION MAY DANGEROUS] Reason
        motUI.normalTest.cancelTestWithReason(CancelTestReason.DANGEROUS_OR_CAUSE_DAMAGE);

        //Then I should be presented with the declaration statement
        assertThat(motUI.normalTest.isDeclarationStatementDisplayed(), is(true));
    }

    @Test (testName = "2fa", groups = {"BVT"})
    public void display2faStatementWhenAbortingTest() throws IOException, URISyntaxException {

        //Given I have an in progress Mot Test
        User twoFactorTester = userData.createTester(site.getId());
        motUI.authentication.registerAndSignInTwoFactorUser(twoFactorTester);
        motUI.normalTest.startTest(twoFactorTester);

        //When I cancel the Test with [INSPECTION MAY DANGEROUS] Reason
        motUI.normalTest.cancelTestWithReason(CancelTestReason.DANGEROUS_OR_CAUSE_DAMAGE);

        //Then I should be presented with the declaration statement
        assertThat(motUI.normalTest.isDeclarationStatementDisplayed(), is(true));
    }

    @Test (groups = {"Regression"})
    public void statementShouldNotBeDisplayedOnTestRefusal() throws IOException, URISyntaxException {

        //Given I refuse to test a vehicle
        motUI.normalTest.refuseToTestVehicle(userData.createTester(site.getId()), vehicle, ReasonForVehicleRefusal.INSPECTION_MAY_BE_DANGEROUS);

        //Then I should Not be presented with the declaration statement
        assertThat(motUI.normalTest.isDeclarationStatementDisplayed(), is(false));
    }

    @Test (testName = "OldRFRTest", groups = {"Regression"})
    public void displayStatementAtContingencySummaryPage() throws IOException, URISyntaxException {

        //Given I start a contingency test
        motUI.contingency.testPage(userData.createTester(site.getId()));

        //When I complete a contingency test and view the summary page
        motUI.contingency.recordTest("12345A", DateTime.now().minusHours(1), vehicle);

        //Then I should be presented with the declaration statement
        assertThat(motUI.contingency.isDeclarationStatementDisplayed(), is(true));
    }

    @Test (groups = {"Regression"})
    public void displayStatementAtCreateNewVehicleRecord() throws IOException, URISyntaxException {

        //When I create a new vehicle record within a test
        motUI.normalTest.createNewVehicleRecord(userData.createTester(site.getId()), vehicle);

        //Then I should be presented with the declaration statement
        assertThat(motUI.normalTest.isDeclarationStatementDisplayed(), is(true));
    }

    @Test (testName = "2fa", groups = {"BVT"})
    public void displayStatementFor2FaUserAtCreateNewVehicleRecord() throws IOException, URISyntaxException {

        //Given I am 2fa tester
        int siteId = siteData.createSite().getId();
        User twoFactorTester = userData.createTester(siteId);
        motUI.authentication.registerAndSignInTwoFactorUser(twoFactorTester);

        //When I create a new vehicle record within a test
        motUI.normalTest.createNewVehicleRecord(twoFactorTester, vehicle);

        //Then I should be presented with 2fa declaration statement
        assertThat(motUI.normalTest.isDeclarationStatementFor2FaDisplayed(), is(true));
    }

    @Test (groups = {"BVT", "Regression"})
    public void replacementCertificateDeclarationStatement() throws IOException, URISyntaxException {

        //Given I have completed an Mot Test
        User tester = userData.createTester(site.getId());
        String testId = motApi.createTest(tester, site.getId(), vehicle, TestOutcome.PASSED, 123456,
                DateTime.now()).getMotTestNumber();

        //When I create a replacement test certificate
        motUI.certificate.createReplacementCertificate(tester, vehicle, testId);

        //Then I should be presented with the declaration statement on the review page
        assertThat(motUI.certificate.isDeclarationStatementDisplayed(), is(true));
    }

    @Test (testName = "2fa", groups = {"BVT"})
    public void displayDeclarationStatementFor2faUserOnReplacementCertificatePage() throws IOException, URISyntaxException {

        //Given I have completed an Mot Test as 2fa user
        int siteId = siteData.createSite().getId();
        User twoFactorTester = userData.createTester(siteId);
        motUI.authentication.registerAndSignInTwoFactorUser(twoFactorTester);

        String testId = motApi.createTest(twoFactorTester, siteId, vehicle, TestOutcome.PASSED, 123456,
                DateTime.now()).getMotTestNumber();

        //When I create a replacement test certificate
        motUI.certificate.createReplacementCertificate(twoFactorTester, vehicle, testId);

        //Then I should be presented with the 2fa declaration statement on the review page
        assertThat(motUI.certificate.isDeclarationStatementFor2FaDisplayed(), is(true));
    }
}