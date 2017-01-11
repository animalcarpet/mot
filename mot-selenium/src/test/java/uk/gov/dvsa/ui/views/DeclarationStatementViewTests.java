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
import uk.gov.dvsa.ui.BaseTest;

import java.io.IOException;
import java.net.URISyntaxException;

import static org.hamcrest.MatcherAssert.assertThat;
import static org.hamcrest.core.Is.is;

public class DeclarationStatementViewTests extends BaseTest {
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

    @Test (groups = {"BVT", "Regression"})
    public void displayStatementAtTestSummaryPage() throws IOException, URISyntaxException {

        //Given I complete a normal test
        motUI.normalTest.conductTestPass(tester, vehicle);

        //And I am on the Test Summary Page

        //Then I should be presented with the declaration statement
        assertThat(motUI.normalTest.isDeclarationStatementDisplayed(), is(true));
    }

    @Test (groups = {"BVT", "Regression"})
    public void displayStatementAtReTestSummaryPage() throws IOException, URISyntaxException {

        //Given I have a vehicle with a failed MOT test
        motApi.createTest(tester, site.getId(), vehicle, TestOutcome.FAILED, 12345, DateTime.now());

        //When I conduct a retest on the vehicle and view the summary page
        motUI.retest.conductRetestPass(vehicle, tester);

        //Then I should be presented with the declaration statement
        assertThat(motUI.retest.isDeclarationStatementDisplayed(), is(true));
    }

    @Test (groups = {"BVT", "Regression"})
    public void displayStatementAtChangeVehicleDetailsSummary() throws IOException, URISyntaxException {

        //Given I change the vehicle details
        motUI.normalTest.changeVehicleDetails(tester, vehicle);

        //When I submit the change

        //Then I should be presented with the declaration statement
        assertThat(motUI.normalTest.isDeclarationStatementDisplayed(), is(true));
    }

    @Test (groups = {"BVT", "Regression"})
    public void statementShouldNotBeDisplayedForTrainingTest() throws IOException, URISyntaxException {

        //Given I am on the review Page of training test
        motUI.normalTest.conductTrainingTest(tester, vehicle);

        //Then I should NOT be presented with the declaration statement
        assertThat(motUI.normalTest.isDeclarationStatementDisplayed(), is(false));
    }

    @Test (groups = {"BVT", "Regression"})
    public void displayStatementWhenAbortingTest() throws IOException, URISyntaxException {

        //Given I have an in progress Mot Test
        motUI.normalTest.startTest();

        //When I cancel the Test with [INSPECTION MAY DANGEROUS] Reason
        motUI.normalTest.cancelTestWithReason(CancelTestReason.DANGEROUS_OR_CAUSE_DAMAGE);

        //Then I should be presented with the declaration statement
        assertThat(motUI.normalTest.isDeclarationStatementDisplayed(), is(true));
    }

    @Test (groups = {"BVT", "Regression"})
    public void statementShouldNotBeDisplayedOnTestRefusal() throws IOException, URISyntaxException {

        //Given I have started an MOT test
        User tester = userData.createTester(1);
        Vehicle vehicle = vehicleData.getNewVehicle(tester);

        //When I refuse to test a vehicle
        motUI.normalTest.refuseToTestVehicle(tester, vehicle, ReasonForVehicleRefusal.INSPECTION_MAY_BE_DANGEROUS);

        //I should Not be presented with the declaration statement
        assertThat(motUI.normalTest.isDeclarationStatementDisplayed(), is(false));
    }
}
