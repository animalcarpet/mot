package uk.gov.dvsa.ui.views.mot_test;

import org.hamcrest.core.Is;
import org.joda.time.DateTime;
import org.joda.time.format.DateTimeFormat;
import org.joda.time.format.DateTimeFormatter;
import org.testng.annotations.BeforeMethod;
import org.testng.annotations.DataProvider;
import org.testng.annotations.Test;
import uk.gov.dvsa.domain.model.Site;
import uk.gov.dvsa.domain.model.User;
import uk.gov.dvsa.domain.model.mot.CancelTestReason;
import uk.gov.dvsa.domain.api.response.Vehicle;
import uk.gov.dvsa.ui.DslTest;
import uk.gov.dvsa.ui.pages.mot.TestAbandonedPage;
import uk.gov.dvsa.ui.pages.mot.TestAbortedPage;
import uk.gov.dvsa.ui.pages.mot.TestResultsEntryNewPage;

import java.io.IOException;
import java.net.URISyntaxException;

import static org.hamcrest.MatcherAssert.assertThat;

public class TestResultEntryPageTests extends DslTest {
    private User tester;
    private Vehicle vehicle;

    @BeforeMethod(alwaysRun = true)
    public void setUp() throws IOException {
        Site site = siteData.createSite();
        tester = motApi.user.createTester(site.getId());
        vehicle = vehicleData.getNewVehicle(tester);
    }

    @Test(testName = "TestResultEntryImprovements", groups = {"BVT", "BL-1930"})
    public void testVehicleInformationIsShownInHeader() throws IOException, URISyntaxException {
        // Given I start a test and I am on the Test Results Page
        TestResultsEntryNewPage testResultEntryPage = pageNavigator.gotoTestResultsEntryNewPage(tester, vehicle);

        // Then I should see the correct vehicle information in the page header
        DateTimeFormatter formatter = DateTimeFormat.forPattern("yyyy-MM-dd");
        DateTime vehicleFirstUse = formatter.parseDateTime(vehicle.getFirstUsedDate());
        formatter = DateTimeFormat.forPattern("d MMM yyyy");
        String vehicleFirstUseString = formatter.print(vehicleFirstUse);

        assertThat("Vehicle make and model is correct", testResultEntryPage.getVehicleMakeModel()
                .equals(vehicle.getMake().getName() + ", " + vehicle.getModel().getName()));

        assertThat("Vehicle registration is correct", testResultEntryPage.getVehicleRegistration()
                .equals(vehicle.getDvsaRegistration()));

        assertThat("Vehicle first used date is correct", testResultEntryPage.getVehicleFirstUsedDate()
                .equals("First used " + vehicleFirstUseString));

    }

    @Test(testName = "TestResultEntryImprovements", groups = {"BVT", "BL-1930"})
    public void testElementsAreDisplayed() throws URISyntaxException, IOException {
        // Given I start a test and I am on the Test Results Page
        TestResultsEntryNewPage testResultsEntryPage = pageNavigator.gotoTestResultsEntryNewPage(tester, vehicle);

        // Then all the buttons and links required to use the page are displayed
        assertThat("Add odometer reading button is displayed", testResultsEntryPage.addOdomoterReadingButtonIsDisplayed());
        assertThat("Add defect button is displayed", testResultsEntryPage.addDefectButtonIsDisplayed());
        assertThat("Search for defect link is displayed", testResultsEntryPage.searchForDefectIsDisplayed());
        assertThat("Add brake test button is displayed", testResultsEntryPage.addBrakeTestButtonIsDisplayed());
        assertThat("Review test button is displayed", testResultsEntryPage.reviewTestButtonIsDisplayed());
    }

    @Test(testName = "TestResultEntryImprovements", groups = {"BVT", "BL-1930"})
    public void startAndAbandonTest() throws URISyntaxException, IOException {

        //Given I start a test and I am on the Test Results Page
        TestResultsEntryNewPage testResultsEntryPage = pageNavigator.gotoTestResultsEntryNewPage(tester, vehicle);

        //When I Abandon the test with a reason
        TestAbandonedPage testAbandonedPage =
                testResultsEntryPage.abandonMotTest(CancelTestReason.DANGEROUS_OR_CAUSE_DAMAGE);

        //Then I the test process should be cancelled and a VT30 Certificate generated message is displayed
        assertThat(testAbandonedPage.isVT30messageDisplayed(), Is.is(true));
    }

    @Test(testName = "TestResultEntryImprovements", groups = {"BVT", "BL-1930"})
    public void startAndAbortTestAsTester() throws URISyntaxException, IOException {

        //Given I start a test and I am on the Test Results Page
        TestResultsEntryNewPage testResultsEntryPage = pageNavigator.gotoTestResultsEntryNewPage(tester, vehicle);

        //When I Abort the test with a reason
        TestAbortedPage testAbortedPage = testResultsEntryPage.abortMotTest(CancelTestReason.TEST_EQUIPMENT_ISSUE);

        //Then the test process should be cancelled and a VT30 Certificate generated message is displayed
        assertThat(testAbortedPage.isVT30messageDisplayed(), Is.is(true));
    }

    @Test(testName = "TestResultEntryImprovements",
            groups = {"Regression", "BL-3395"},
            dataProvider = "getOdometerReadingsAndNotice")
    public void testOdometerReadingNotices(int initialReading,
                                           int secondReading,
                                           String notice) throws URISyntaxException, IOException {
        // Given I start a test and I am on the Test Results Page and I enter an odometer reading
        TestResultsEntryNewPage testResultsEntryPage = pageNavigator
                .gotoTestResultsEntryNewPage(tester, vehicle)
                .addOdometerReading(initialReading);

        // When I complete the test
        testResultsEntryPage
                .completeBrakeTestWithPassValues()
                .clickReviewTestButton()
                .finishTest();

        // And I test the same vehicle again and enter an odometer reading that will trigger a notice
        testResultsEntryPage = pageNavigator
                .gotoTestResultsEntryNewPage(tester, vehicle)
                .addOdometerReading(secondReading);

        // Then I will see the relevant notice on the test results entry page
        assertThat(testResultsEntryPage.getOdometerNoticeText(), Is.is(notice));
    }

    @DataProvider(name = "getOdometerReadingsAndNotice")
    public Object[][] getOdometerReadingsAndNotice() throws IOException {
        return new Object[][] {
                {1000, 1000, "This is the same as the last test"},
                {1000, 100, "This is lower than the last test"},
                {1000, 999999, "This is significantly higher than the last test"}
        };
    }
}
