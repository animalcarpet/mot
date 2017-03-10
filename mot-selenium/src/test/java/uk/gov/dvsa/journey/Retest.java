package uk.gov.dvsa.journey;

import org.openqa.selenium.NoSuchElementException;
import uk.gov.dvsa.domain.model.User;
import uk.gov.dvsa.domain.api.response.Vehicle;
import uk.gov.dvsa.domain.navigation.PageNavigator;
import uk.gov.dvsa.helper.AssertionHelper;
import uk.gov.dvsa.helper.ConfigHelper;
import uk.gov.dvsa.ui.pages.Page;
import uk.gov.dvsa.ui.pages.VehicleSearchPage;
import uk.gov.dvsa.ui.pages.VehicleSearchResultsPage;
import uk.gov.dvsa.ui.pages.mot.TestResultsEntryNewPage;
import uk.gov.dvsa.ui.pages.mot.retest.ReTestCompletePage;
import uk.gov.dvsa.ui.pages.mot.retest.ReTestResultsEntryPage;
import uk.gov.dvsa.ui.pages.mot.retest.ReTestSummaryPage;

import java.io.IOException;
import java.net.URISyntaxException;

import static org.hamcrest.MatcherAssert.assertThat;
import static org.hamcrest.Matchers.equalToIgnoringCase;
import static org.hamcrest.core.Is.is;

public class Retest {

    PageNavigator pageNavigator;
    private boolean successful = false;
    private String expectedText;
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

    public <T extends Page> T startRetest(Vehicle vehicle, User tester) throws IOException, URISyntaxException {
        return (T) pageNavigator.gotoReTestResultsEntryPage(tester, vehicle);
    }

    public void searchForVehicle(User user, Vehicle vehicle) throws IOException, URISyntaxException {
        VehicleSearchResultsPage searchPage = pageNavigator.navigateToPage(user,VehicleSearchPage.PATH, VehicleSearchPage.class).searchVehicle(vehicle);
        expectedText = searchPage.getVehicleRetestStatus();
    }

    public boolean isTextPresent(String actual) throws NoSuchElementException {
        return AssertionHelper.compareText(expectedText, actual);
    }

    public void verifyRetestIsSuccessful() {
        assertThat(successful, is(true));
    }

    public void verifyRetestLinkNotPresent(){
        assertThat(successful, is(false));
    }

    public boolean isDeclarationStatementDisplayed() {
        return declarationSuccessful;
    }
}
