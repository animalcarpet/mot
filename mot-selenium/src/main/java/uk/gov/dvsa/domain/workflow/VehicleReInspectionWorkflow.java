package uk.gov.dvsa.domain.workflow;

import org.openqa.selenium.Cookie;
import uk.gov.dvsa.domain.model.User;
import uk.gov.dvsa.domain.model.vehicle.Vehicle;
import uk.gov.dvsa.domain.service.CookieService;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.ui.pages.AssessmentDetailsConfirmationPage;
import uk.gov.dvsa.ui.pages.PageLocator;
import uk.gov.dvsa.ui.pages.VehicleSearchPage;
import uk.gov.dvsa.ui.pages.VehicleSearchResultsPage;
import uk.gov.dvsa.ui.pages.events.EventsHistoryPage;
import uk.gov.dvsa.ui.pages.mot.*;
import uk.gov.dvsa.ui.pages.vts.VehicleTestingStationPage;

import java.io.IOException;

public class VehicleReInspectionWorkflow extends BaseWorkflow {

    private MotAppDriver driver;

    public final void setDriver(final MotAppDriver driver) {
        this.driver = driver;
    }

    public TestSummaryPage searchForMotTest(User user, String searchCategory, String searchValue, String motTestId) throws IOException {
        injectOpenAmCookieAndNavigateToPath(user, MotTestSearchPage.PATH);
        PageLocator.getMotTestSearchPage(driver)
                .selectSearchCategory(searchCategory)
                .fillSearchValue(searchValue)
                .clickSearchButton();
        PageLocator.getMotTestHistoryPage(driver)
                .selectMotTestFromTableById(motTestId);

        return new TestSummaryPage(driver);
    }

    public EventsHistoryPage gotoEventsHistoryPage(User user, String siteId) throws IOException {
        injectOpenAmCookieAndNavigateToPath(user, String.format(VehicleTestingStationPage.PATH, siteId));
        PageLocator.getVehicleTestingStationPage(driver)
                .clickOnViewHistoryLink();
        return new EventsHistoryPage(driver);
    }

    public VehicleTestingStationPage gotoVehicleTestingStationPage(User user, String siteId) throws IOException {
        injectOpenAmCookieAndNavigateToPath(user, String.format(VehicleTestingStationPage.PATH, siteId));
        return new VehicleTestingStationPage(driver);
    }

    public TestCompletePage reinspectVehicle(String testType, int odometerValue, String siteId) {
        PageLocator.getTestSummaryPage(driver)
                .selectTestType(testType)
                .clickStartReinspectionButton();
        PageLocator.getTestResultsEntryReinspectionPage(driver)
                .fillOdometerReadingAndSubmit(odometerValue)
                .clickReviewTest();
        PageLocator.getTestSummaryPage(driver)
                .fillSiteIdInput(siteId)
                .clickFinishButton();

        return new TestCompletePage(driver);
    }

    public AssessmentDetailsConfirmationPage compareResults(String score, String category) {
        PageLocator.getTestCompletePage(driver)
                .clickCompareResultsButton();
        PageLocator.getTestCompareResultsPage(driver)
                .selectServiceScore(score).selectServiceCategory(category).fillServiceJustificationInputWithDefaultValue()
                .selectParkingScore(score).selectParkingCategory(category).fillParkingJustificationInputWithDefaultValue()
                .fillFinalJustificationInputWithDefaultValue()
                .clickRecordAssessmentButton();

        return new AssessmentDetailsConfirmationPage(driver);
    }

    public TestOptionsPage startMotTestAsATester(User user, Vehicle vehicle) throws IOException {
        injectOpenAmCookieAndNavigateToPath(user, VehicleSearchPage.PATH);

        return PageLocator.getVehicleSearchPage(driver).searchVehicle(vehicle).selectVehicle(
                StartTestConfirmationPage.class).clickStartMotTest();
    }

    public TestShortSummaryPage abortActiveTestOnVtsPage(String regNum) {
        PageLocator.getVehicleTestingStationPage(driver)
                .clickOnActiveTest(regNum)
                .clickAbortMotTestButton()
                .selectAbortedByVeReason()
                .clickAbortMotTestButton();

        return new TestShortSummaryPage(driver);
    }

    private void injectOpenAmCookieAndNavigateToPath(User user, String path) throws IOException {
        driver.setUser(user);
        addCookieToBrowser(user);
        driver.navigateToPath(path);
    }

    private Cookie getCookieForUser(User user) throws IOException {
        return CookieService.generateOpenAmLoginCookie(user);
    }

    private void addCookieToBrowser(User user) throws IOException {
        driver.manage().deleteAllCookies();
        driver.loadBaseUrl();
        driver.manage().addCookie(getCookieForUser(user));
    }
}