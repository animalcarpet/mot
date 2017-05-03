package uk.gov.dvsa.journey;

import uk.gov.dvsa.domain.api.response.Vehicle;
import uk.gov.dvsa.domain.model.User;
import uk.gov.dvsa.domain.model.mot.CancelTestReason;
import uk.gov.dvsa.domain.navigation.PageNavigator;
import uk.gov.dvsa.ui.pages.Page;
import uk.gov.dvsa.ui.pages.mot.TestResultsEntryNewPage;
import uk.gov.dvsa.ui.pages.mot.retest.ReTestAbortedPage;
import uk.gov.dvsa.ui.pages.mot.retest.ReTestCompletePage;
import uk.gov.dvsa.ui.pages.mot.retest.ReTestSummaryPage;

import java.io.IOException;
import java.net.URISyntaxException;

import static org.hamcrest.MatcherAssert.assertThat;
import static org.hamcrest.Matchers.equalToIgnoringCase;
import static org.hamcrest.core.Is.is;

public class Retest {

    PageNavigator pageNavigator;
    private boolean successful = false;
    private boolean aborted = false;
    private String actualText;
    private boolean declarationSuccessful = false;

    private static final String TWO_FA_DECLARATION_STATEMENT = "By saving this test result you confirm that you have carried out " +
            "this MOT test in line with DVSA conditions for MOT testing.";

    public Retest(PageNavigator pageNavigator)
    {
        this.pageNavigator = pageNavigator;
    }

    public void conductRetestPass(Vehicle vehicle, User tester) throws IOException, URISyntaxException {
        TestResultsEntryNewPage resultsEntryPage = pageNavigator.gotoReTestResultsEntryPage(tester, vehicle);
        resultsEntryPage.completeTestDetailsWithPassValues(true);
        ReTestSummaryPage summaryPage = resultsEntryPage.clickReviewTestButton(true);

        if (summaryPage.isDeclarationTextDisplayed()) {
            assertThat(summaryPage.getDeclarationText(), equalToIgnoringCase(TWO_FA_DECLARATION_STATEMENT));

            declarationSuccessful = true;
        }

        ReTestCompletePage testCompletePage = summaryPage.finishTest();

        successful = testCompletePage.verifyBackToHomeDisplayed();
    }

    public void conductRetestAbort(Vehicle vehicle, User tester) throws IOException, URISyntaxException {
        TestResultsEntryNewPage resultsEntryPage = pageNavigator.gotoReTestResultsEntryPage(tester, vehicle);
        ReTestAbortedPage reTestAbort = resultsEntryPage.abortMotReTest(CancelTestReason.TEST_EQUIPMENT_ISSUE);

        aborted = reTestAbort.isVt30MessageDisplayed();
    }

    public <T extends Page> T startRetest(Vehicle vehicle, User tester) throws IOException, URISyntaxException {
        return (T) pageNavigator.gotoReTestResultsEntryPage(tester, vehicle);
    }

    public void verifyRetestIsSuccessful() {
        assertThat(successful, is(true));
    }

    public boolean verifyRetestAbortIsSuccessful() {
        return (aborted);
    }

    public void verifyRetestLinkNotPresent(){
        assertThat(successful, is(false));
    }

    public boolean isDeclarationStatementDisplayed() {
        return declarationSuccessful;
    }
}
