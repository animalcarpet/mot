package uk.gov.dvsa.ui.feature.journey.mot.reinspection;

import org.joda.time.DateTime;
import org.testng.annotations.BeforeClass;
import org.testng.annotations.Test;
import uk.gov.dvsa.domain.model.Site;
import uk.gov.dvsa.domain.model.User;
import uk.gov.dvsa.domain.model.mot.TestOutcome;
import uk.gov.dvsa.helper.enums.Comparison;
import uk.gov.dvsa.ui.DslTest;

import java.io.IOException;

import static org.hamcrest.MatcherAssert.assertThat;
import static org.hamcrest.Matchers.equalTo;
import static org.hamcrest.Matchers.is;

public class ReInspectionAppealTests extends DslTest {
    private Site testSite;

    @BeforeClass(alwaysRun = true)
    private void setup() throws IOException {
        testSite = siteData.createSite();
    }

    @Test(testName = "OldRFRTest", groups = {"Regression"})
    public void statutoryAppealDisciplinaryActionReportIssuedFor30andGreaterTotalScore() throws IOException {

        //Given I have a Failed MOT
        User tester = userData.createTester(testSite.getId());
        String motTestId = motApi.createTest(
                tester, testSite.getId(), vehicleData.getNewVehicle(tester), TestOutcome.FAILED, 123456, DateTime.now()
        ).getMotTestNumber();

        //When I conduct a Statutory appeal with total score of >=30
        String outcome = motUI.reInspection.statutoryAppeal(
                userData.createVehicleExaminer("ft-Enf-", false), motTestId,
                testSite.getSiteNumber(),
                Comparison.RISK_OF_INJURY_MISSED);

        //Then a Disciplinary action report should be issued
        assertThat("Disciplinary action is selected", outcome, equalTo("Disciplinary action report"));
    }

    @Test(testName = "OldRFRTest", groups = {"Regression"})
    public void statutoryAppealAdvisoryWarningLetterIssuedForBetween10and25TotalScore() throws IOException {
        //Given I have a Failed MOT
        User tester = userData.createTester(testSite.getId());
        String motTestId = motApi.createTest(
                tester, testSite.getId(), vehicleData.getNewVehicle(tester), TestOutcome.FAILED, 123456, DateTime.now()
        ).getMotTestNumber();

        //When I conduct a Statutory appeal with total score of >=20
        String outcome = motUI.reInspection.statutoryAppeal(userData.createVehicleExaminer("ft-Enf-", false), motTestId,
                testSite.getSiteNumber(),
                Comparison.SIGNIFICANTLY_WRONG);

        //Then a Advisory warning report should be issued
        assertThat("Advisory warning is selected", outcome, equalTo("Advisory warning letter"));
    }

    @Test(testName = "OldRFRTest", groups = {"Regression"})
    public void invertedAppealAdvisoryWarningLetterIssuedForBetween10and25TotalScore() throws IOException {
        //Given I have a Failed MOT
        User tester = userData.createTester(testSite.getId());
        String motTestId = motApi.createTest(
                tester, testSite.getId(), vehicleData.getNewVehicle(tester), TestOutcome.FAILED, 123456, DateTime.now()
        ).getMotTestNumber();

        //When I conduct an Inverted appeal with total score of >=20
        String outcome = motUI.reInspection.invertedAppeal(userData.createVehicleExaminer("ft-Enf-", false), motTestId,
                testSite.getSiteNumber(),
                Comparison.SIGNIFICANTLY_WRONG);

        //Then a Advisory warning report should be issued
        assertThat("Advisory warning is selected", outcome, equalTo("Advisory warning letter"));
    }

    @Test(testName = "OldRFRTest", groups = {"Regression"})
    public void expiryDateIsDisplayedForStatutoryAppealMotTest() throws IOException {
        //Given I have a Failed MOT
        String motTestId = motApi.createFailedTest(userData.createTester(testSite.getId()),
                testSite.getId()).getMotTestNumber();

        //When I conduct a PASSED Re-Inspection

        //Then I should see expiry date on the test summary page.
        assertThat("Expiry date is shown",
                motUI.reInspection.statutoryAppealTestSummaryPage(userData.createVehicleExaminer("ft-Enf-", false), motTestId)
                        .isExpiryDateDisplayed(), is(true));
    }

    @Test(testName = "OldRFRTest", groups = {"Regression"})
    public void expiryDateIsDisplayedForInvertedAppealMotTest() throws IOException {
        //Given I have a Failed MOT
        String motTestId = motApi.createFailedTest(userData.createTester(testSite.getId()),
                testSite.getId()).getMotTestNumber();

        //When I conduct a PASSED Re-Inspection

        //Then I should see expiry date on the test summary page.
        assertThat("Expiry date is shown",
                motUI.reInspection.invertedAppealTestSummaryPage(userData.createVehicleExaminer("ft-Enf-", false), motTestId)
                        .isExpiryDateDisplayed(), is(true));
    }
}
