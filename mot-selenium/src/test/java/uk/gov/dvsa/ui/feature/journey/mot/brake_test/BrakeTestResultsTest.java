package uk.gov.dvsa.ui.feature.journey.mot.brake_test;

import org.joda.time.DateTime;
import org.testng.annotations.BeforeMethod;
import org.testng.annotations.Test;

import uk.gov.dvsa.domain.model.AeDetails;
import uk.gov.dvsa.domain.model.Site;
import uk.gov.dvsa.domain.model.User;
import uk.gov.dvsa.domain.model.mot.Defect;
import uk.gov.dvsa.domain.model.mot.TestOutcome;
import uk.gov.dvsa.domain.api.response.Vehicle;
import uk.gov.dvsa.helper.DefectsTestsDataProvider;
import uk.gov.dvsa.ui.DslTest;
import uk.gov.dvsa.ui.pages.mot.DefectsPage;
import uk.gov.dvsa.ui.pages.mot.TestResultsEntryGroupAPageInterface;
import uk.gov.dvsa.ui.pages.mot.TestResultsEntryNewPage;

import java.io.IOException;
import java.net.URISyntaxException;

import static org.hamcrest.MatcherAssert.assertThat;
import static org.hamcrest.core.Is.is;

public class BrakeTestResultsTest extends DslTest {

    private Site site;
    private AeDetails aeDetails;
    private User tester;
    private Vehicle vehicle;

    @BeforeMethod(alwaysRun = true)
    private void setupTestData() throws IOException {
        aeDetails = aeData.createAeWithDefaultValues();
        site = siteData.createNewSite(aeDetails.getId(), "TestSite");
        tester = userData.createTester(site.getId());
        vehicle = vehicleData.getNewVehicle(tester);
    }

    @Test(testName = "TestResultEntryImprovements", groups = {"BVT"},
            description = "Verify that the brake test journey can be performed through the new MOT Test results page")
    public void saveBrakeTestResultsHappyPath() throws IOException, URISyntaxException {

        // Given I am on the Test Results Entry Page
        TestResultsEntryNewPage testResultsEntryNewPage = pageNavigator.gotoTestResultsEntryNewPage(tester,vehicle);

        // When I complete all Brake test Values with passing data
        testResultsEntryNewPage.completeBrakeTestWithPassValues();

        // Then I should see a pass on the test result page
        assertThat(testResultsEntryNewPage.isPassNoticeDisplayed(), is(true));
    }

    @Test(testName = "TestResultEntryImprovements", groups = {"Regression", "BL-1951"},
            description = "Verifies that tester is unable to see Edit brake test button after adding RFR Brake performance not tested")
    public void testEditBrakeTestButtonBehaviourForTester() throws IOException, URISyntaxException {
        Defect defect = DefectsTestsDataProvider.buildDefect("Brake performance not tested", "Brake performance not tested",
                                                        false, "Brakes", "Brake performance", "Brake performance not tested");

        // Given I am on the defects screen with a defect as a tester
        DefectsPage defectsPage = pageNavigator.gotoDefectsPageWithDefect(tester, vehicle, defect);

        // When I navigate to Test results entry page a defect page
        TestResultsEntryNewPage resultsEntryNewPage = defectsPage.clickFinishAndReturnButton();

        // Then the Add brake test button is not displayed
        assertThat(resultsEntryNewPage.isAddBrakeTestButtonDisplayed(), is(false));
    }

    @Test(testName = "TestResultEntryImprovements", groups = {"Regression", "BL-1951"},
            description = "Verifies that vehicle examiner still able to see " +
            "Edit brake test button after adding RFR Brake performance not tested")
    public void testEditBrakeTestButtonBehaviourForVe() throws IOException, URISyntaxException {
        Defect defect = DefectsTestsDataProvider.buildDefect("Brake performance not tested", "Brake performance not tested",
                                                        false, "Items not tested", "Brake performance");

        // Given a vehicle failed Mot
        String motTestId = motApi.createTest(tester, site.getId(), vehicleData.getNewVehicle(tester), TestOutcome.FAILED, 123456,
                                                            DateTime.now()).getMotTestNumber();

        // When I conduct a re-inspection as VE
        TestResultsEntryGroupAPageInterface resultsEntryPage =
                motUI.reInspection.startReInspection(userData.createVehicleExaminer("ft-Enf-", false), motTestId, "Targeted Reinspection");

        // When I navigate to Test results entry page with a brake test related defect
        TestResultsEntryNewPage resultsEntryNewPage = ((TestResultsEntryNewPage) resultsEntryPage).clickAddDefectButton()
                                                            .navigateToDefectCategory(defect.getCategoryPath())
                                                            .navigateToAddDefectPage(defect)
                                                            .clickAddDefectButton()
                                                            .clickFinishAndReturnButton();

        // Then the Add brake test button is displayed
        assertThat(resultsEntryNewPage.isAddBrakeTestButtonDisplayed(), is(true));
    }
}