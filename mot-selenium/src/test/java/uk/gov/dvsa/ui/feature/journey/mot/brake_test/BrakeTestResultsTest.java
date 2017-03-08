package uk.gov.dvsa.ui.feature.journey.mot.brake_test;

import org.joda.time.DateTime;
import org.openqa.selenium.By;
import org.testng.annotations.BeforeMethod;
import org.testng.annotations.Test;

import uk.gov.dvsa.domain.model.AeDetails;
import uk.gov.dvsa.domain.model.Site;
import uk.gov.dvsa.domain.model.User;
import uk.gov.dvsa.domain.model.mot.Defect;
import uk.gov.dvsa.domain.model.mot.TestOutcome;
import uk.gov.dvsa.domain.api.response.Vehicle;
import uk.gov.dvsa.helper.DefectsTestsDataProvider;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.helper.ReasonForRejection;
import uk.gov.dvsa.ui.DslTest;
import uk.gov.dvsa.ui.pages.mot.DefectsPage;
import uk.gov.dvsa.ui.pages.mot.TestResultsEntryGroupAPageInterface;
import uk.gov.dvsa.ui.pages.mot.TestResultsEntryNewPage;

import java.io.IOException;
import java.net.URISyntaxException;
import java.util.ArrayList;
import java.util.List;

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
        tester = motApi.user.createTester(site.getId());
        vehicle = vehicleData.getNewVehicle(tester);
    }

    @Test(testName = "TestResultEntryImprovements", groups = {"BVT"},
            description = "Verify that the brake test journey can be performed through the new MOT Test results page")
    public void saveBrakeTestResultsHappyPath() throws IOException, URISyntaxException {

        // Given I am on the Test Results Entry Page
        TestResultsEntryNewPage testResultsEntryNewPage = pageNavigator.gotoTestResultsEntryNewPage(tester,vehicle);

        // When I complete all Brake test Values with passing data
        testResultsEntryNewPage.completeBrakeTestWithPassValues(false);

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
                motUI.reInspection.startReInspection(motApi.user.createVehicleExaminer("ft-Enf-", false), motTestId, "Targeted Reinspection");

        // When I navigate to Test results entry page with a brake test related defect
        TestResultsEntryNewPage resultsEntryNewPage = ((TestResultsEntryNewPage) resultsEntryPage).clickAddDefectButton()
                                                            .navigateToDefectCategory(defect.getCategoryPath())
                                                            .navigateToAddDefectPage(defect)
                                                            .clickAddDefectButton()
                                                            .clickFinishAndReturnButton();

        // Then the Add brake test button is not displayed
        assertThat(resultsEntryNewPage.isAddBrakeTestButtonDisplayed(), is(false));
    }

    @Test(description = "Verify that brake test result values are removed when adding a 'Brake performance not tested' " +
                    "defect during an original MOT test")
    public void removeBrakeTestResultsWhenAddingBrakePerformanceNotTestedDefect() throws IOException, URISyntaxException {

        Defect brakePerformanceNotTested = getBrakePerformanceNotTestedDefect();

        // Given I am on the Test Results Entry Page for an original MOT test
        TestResultsEntryNewPage testResultsEntryNewPage = pageNavigator.gotoTestResultsEntryNewPage(tester,vehicle);

        // When I add Brake Test values, and add 'Brake performance not tested' defect
        testResultsEntryNewPage.completeBrakeTestWithPassValues(false)
                .clickAddDefectButton()
                .navigateToDefectCategory(brakePerformanceNotTested.getCategoryPath())
                .navigateToAddDefectPage(brakePerformanceNotTested)
                .clickAddDefectButton()
                .clickFinishAndReturnButton();

        // Then the Brake Test values will be removed and the Brake Test status will be 'Not tested'
        assertThat(testResultsEntryNewPage.isBrakeTestNotTestedNoticeDisplayed(), is(true));
    }

    @Test(description = "Verify that brake test result values are removed when undoing the 'mark as repaired' action on " +
                    "a 'Brake performance not tested' defect during an MOT re-test")
    public void removeBrakeTestResultsWhenUndoingMarkAsRepairedForBrakePerformanceNotTestedDefectDuringRetest() throws IOException, URISyntaxException {

        List<ReasonForRejection> reasonForRejectionsList = new ArrayList<>();
        reasonForRejectionsList.add(ReasonForRejection.BRAKE_PERFORMANCE_NOT_TESTED);
        String defectName = "Brake performance not tested";

        // Given I have a vehicle with a failed original MOT test
        motApi.createTestWithRfr(tester, site.getId(), vehicle, TestOutcome.FAILED, 1000, DateTime.now(), reasonForRejectionsList);

        // When I conduct a retest, click 'mark as repaired' on 'Brake performance not tested' defect,
        // add failing Brake Test values, and click 'undo'
        TestResultsEntryNewPage testResultsEntryNewPage = ((TestResultsEntryNewPage)motUI.retest.startRetest(vehicle, tester))
                .clickRepaired(defectName, TestResultsEntryNewPage.class)
                .completeBrakeTestWithFailValues(true)
                .clickUndoRepaired(TestResultsEntryNewPage.class);

        // Then Brake Test status will be 'Not tested' and the summary of generated defects is not present
        assertThat(testResultsEntryNewPage.isBrakeTestNotTestedNoticeDisplayed(), is(true));
        assertThat(PageInteractionHelper.isElementPresent(By.id("numberOfGeneratedFailures")), is(false));
    }

    private Defect getBrakePerformanceNotTestedDefect() {
        Defect.DefectBuilder builder = new Defect.DefectBuilder();
        builder.setCategoryPath(new String[] {"Brakes", "Brake performance", "Brake performance not tested"});
        builder.setDefectName("Brake performance not tested");
        builder.setDefectType(Defect.DefectType.Failure);
        builder.setAddOrRemoveName("Brake performance not tested");
        builder.setIsDangerous(false);
        return builder.build();
    }
}