package uk.gov.dvsa.journey;

import org.joda.time.DateTime;
import org.joda.time.DateTimeZone;
import uk.gov.dvsa.domain.api.response.Vehicle;
import uk.gov.dvsa.domain.model.User;
import uk.gov.dvsa.domain.model.mot.OdometerUnit;
import uk.gov.dvsa.domain.navigation.PageNavigator;
import uk.gov.dvsa.helper.ConfigHelper;
import uk.gov.dvsa.ui.pages.VehicleSearchPage;
import uk.gov.dvsa.ui.pages.mot.*;
import uk.gov.dvsa.ui.pages.mot.retest.ConfirmVehicleRetestPage;
import uk.gov.dvsa.ui.pages.mot.retest.ReTestCompletePage;
import uk.gov.dvsa.ui.pages.mot.retest.ReTestResultsEntryPage;
import uk.gov.dvsa.ui.pages.mot.retest.ReTestSummaryPage;

import java.io.IOException;
import java.net.URISyntaxException;

import static org.hamcrest.MatcherAssert.assertThat;
import static org.hamcrest.Matchers.equalToIgnoringCase;

public class Contingency {

    PageNavigator pageNavigator = null;
    private ContingencyTestEntryPage contingencyPage;
    private boolean successful = false;
    private boolean declarationStatement = false;
    private TestSummaryPage testSummaryPage;

    private static final String DECLARATION_STATEMENT = "I confirm that this MOT transaction has been conducted in accordance with " +
            "the conditions of authorisation which includes compliance with the MOT testing guide, the requirements for " +
            "authorisation, the appropriate MOT Inspection Manual and any other instructions issued by DVSA.";

    private static final String TWOFA_DECLARATION_STATEMENT = "By saving this test result you confirm that you have carried out this " +
            "MOT test in line with DVSA conditions for MOT testing.";

    public Contingency(PageNavigator pageNavigator)
    {
        this.pageNavigator = pageNavigator;
    }

    public ContingencyTestEntryPage testPage(User user) throws IOException, URISyntaxException {
        return contingencyPage = pageNavigator.gotoContingencyTestEntryPage(user);
    }

    public void recordTest(String code, DateTime datetime, Vehicle vehicle) {
        VehicleSearchPage vehicleSearchPage =
                contingencyPage.fillContingencyTestFormAndConfirm(code, datetime.toDateTime(DateTimeZone.UTC));

        StartTestConfirmationPage startTestConfirmationPage =
                vehicleSearchPage.searchVehicle(vehicle).selectVehicle(StartTestConfirmationPage.class);

        if (ConfigHelper.isTestResultEntryImprovementsEnabled()) {
            TestResultsEntryNewPage testResultsEntryPage =
                    startTestConfirmationPage.clickStartMotTestWhenConductingContingencyTest(TestResultsEntryNewPage.class);

            testResultsEntryPage = testResultsEntryPage.clickAddReadingButton().addOdometerReading(99999, OdometerUnit.MILES, true);
            testSummaryPage = testResultsEntryPage.completeBrakeTestWithPassValues(false).clickReviewTestButton();
        } else {
            TestResultsEntryPage testResultsEntryPage =
                    startTestConfirmationPage.clickStartMotTestWhenConductingContingencyTest(TestResultsEntryPage.class);

            testResultsEntryPage.completeTestDetailsWithPassValues(false);

            testSummaryPage = testResultsEntryPage.clickReviewTestButton();
        }

        if (testSummaryPage.isDeclarationTextDisplayed()) {
            assertThat(testSummaryPage.getDeclarationText(), equalToIgnoringCase(TWOFA_DECLARATION_STATEMENT));
            declarationStatement = true;
        }

        TestCompletePage testCompletePage = testSummaryPage.clickFinishButton(TestCompletePage.class);
        successful = testCompletePage.isReturnToHomepageLinkDisplayed();
    }

    public void recordReTest(String code, DateTime datetime, Vehicle vehicle) throws IOException, URISyntaxException {
        VehicleSearchPage vehicleSearchPage = contingencyPage.fillContingencyTestFormAndConfirm(code, datetime);

        ConfirmVehicleRetestPage retestPage =
                vehicleSearchPage.searchVehicle(vehicle).selectVehicle(ConfirmVehicleRetestPage.class);

        ReTestSummaryPage summaryPage;
        if (ConfigHelper.isTestResultEntryImprovementsEnabled()) {
            TestResultsEntryNewPage resultsEntryPage = retestPage.startContigencyRetest(TestResultsEntryNewPage.class);
            resultsEntryPage.completeTestDetailsWithPassValues(true);
            summaryPage = resultsEntryPage.clickReviewTestButton(true);
        } else {
            ReTestResultsEntryPage resultsEntryPage = retestPage.startContigencyRetest(ReTestResultsEntryPage.class);
            summaryPage = resultsEntryPage.completeTestDetailsWithPassValues().clickReviewTestButton();
        }

        if (summaryPage.isDeclarationTextDisplayed()) {
            assertThat(summaryPage.getDeclarationText(), equalToIgnoringCase(TWOFA_DECLARATION_STATEMENT));
            declarationStatement = true;
        }

        ReTestCompletePage testCompletePage = summaryPage.finishTest();
        successful = testCompletePage.verifyBackToHomeDisplayed();
    }

    public boolean isDeclarationStatementDisplayed() {
        return declarationStatement;
    }

    public boolean isTestSaveSuccessful() {
        return successful;
    }

    public void startTest() {
        contingencyPage.clickConfirmContingencyTestDetailsButton();
    }

    public String getValidationMessage(String elementPrefix){
        return contingencyPage.getValidationText(elementPrefix);
    }

    public int getValidationListSize() {
        return contingencyPage.getValidationList().size();
    }
}