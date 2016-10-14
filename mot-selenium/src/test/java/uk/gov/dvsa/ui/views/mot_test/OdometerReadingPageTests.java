package uk.gov.dvsa.ui.views.mot_test;

import org.hamcrest.core.Is;
import org.testng.annotations.BeforeMethod;
import org.testng.annotations.Test;
import uk.gov.dvsa.domain.model.Site;
import uk.gov.dvsa.domain.model.User;
import uk.gov.dvsa.domain.model.mot.OdometerUnit;
import uk.gov.dvsa.domain.api.response.Vehicle;
import uk.gov.dvsa.ui.DslTest;
import uk.gov.dvsa.ui.pages.mot.OdometerReadingPage;
import uk.gov.dvsa.ui.pages.mot.TestResultsEntryNewPage;
import java.io.IOException;
import java.net.URISyntaxException;

import static org.hamcrest.MatcherAssert.assertThat;

public class OdometerReadingPageTests extends DslTest {
    private User tester;
    private Vehicle vehicle;

    @BeforeMethod(alwaysRun = true)
    public void setUp() throws IOException {
        Site site = siteData.createSite();
        tester = userData.createTester(site.getId());
        vehicle = vehicleData.getNewVehicle(tester);
    }

    @Test(testName = "TestResultEntryImprovements", groups = {"BVT", "BL-1947"})
    public void addOdometerReadingWithValidValues() throws IOException, URISyntaxException {
        // Given I start an MOT test and I am on the MOT Test Results page
        TestResultsEntryNewPage testResultsEntryPage = pageNavigator.gotoTestResultsEntryNewPage(tester, vehicle);

        // When I click the "Add reading" button
        OdometerReadingPage odometerReadingPage = testResultsEntryPage.clickAddReadingButton();

        // When I enter valid odometer reading details and click "Update reading"
        testResultsEntryPage = odometerReadingPage.addOdometerReading(1000, OdometerUnit.KILOMETRES, true);

        // Then I should navigate back to the MOT Test Result page and see a success message
        assertThat(testResultsEntryPage.isOdometerReadingUpdateSuccessMessageDisplayed(), Is.is(true));
    }

    @Test(testName = "TestResultEntryImprovements", groups = {"BVT", "BL-1947"})
    public void verifyOdometerReadingValue() throws IOException, URISyntaxException {
        // Given I start an MOT test and I am on the MOT Test Results page
        TestResultsEntryNewPage testResultsEntryPage = pageNavigator.gotoTestResultsEntryNewPage(tester, vehicle);

        // When I click the "Add reading" button
        OdometerReadingPage odometerReadingPage = testResultsEntryPage.clickAddReadingButton();

        // When I enter valid odometer reading details and click "Update reading"
        testResultsEntryPage = odometerReadingPage.addOdometerReading(1000, OdometerUnit.KILOMETRES, true);

        // Then I should navigate back to the MOT Test Result page and see a success message
        assert(testResultsEntryPage.getOdometerReading().contains("1,000 km"));
    }

    @Test(testName = "TestResultEntryImprovements", groups = {"BVT", "BL-1947"})
    public void addOdometerReadingWithInvalidValues() throws IOException, URISyntaxException {
        // Given I start an MOT test and I am on the MOT Test Results page
        TestResultsEntryNewPage testResultsEntryPage = pageNavigator.gotoTestResultsEntryNewPage(tester, vehicle);

        // When I click the "Add reading" button
        OdometerReadingPage odometerReadingPage = testResultsEntryPage.clickAddReadingButton();

        // When I enter invalid odometer reading details and click "Update reading"
        odometerReadingPage.addOdometerReading(9999999, OdometerUnit.MILES, false);

        // Then I should navigate back to the MOT Test Result page and see a success message
        assertThat(odometerReadingPage.isOdometerReadingUpdateErrorMessageDisplayed(), Is.is(true));
    }
}
