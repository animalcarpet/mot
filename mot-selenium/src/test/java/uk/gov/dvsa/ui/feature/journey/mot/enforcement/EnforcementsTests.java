package uk.gov.dvsa.ui.feature.journey.mot.enforcement;

import org.testng.annotations.BeforeClass;
import org.testng.annotations.BeforeMethod;
import org.testng.annotations.Test;
import uk.gov.dvsa.domain.api.response.Vehicle;
import uk.gov.dvsa.domain.model.Site;
import uk.gov.dvsa.domain.model.User;
import uk.gov.dvsa.ui.DslTest;
import uk.gov.dvsa.ui.pages.mot.TestCompletePage;
import uk.gov.dvsa.ui.pages.mot.TestResultsEntryNewPage;
import uk.gov.dvsa.ui.pages.mot.TestSummaryPage;
import uk.gov.dvsa.ui.pages.mot.TesterTestLogPage;
import uk.gov.dvsa.ui.pages.profile.PersonProfilePage;
import uk.gov.dvsa.ui.pages.vehicleinformation.VehicleInformationPage;
import uk.gov.dvsa.ui.pages.vehicleinformation.VehicleInformationSearchPage;
import uk.gov.dvsa.ui.pages.vehicleinformation.VehicleMotTestHistoryPage;

import java.io.IOException;
import java.net.URISyntaxException;

import static org.hamcrest.MatcherAssert.assertThat;
import static org.hamcrest.Matchers.is;

public class EnforcementsTests extends DslTest {

    private User tester;
    private Vehicle vehicle;
    private User vehicleExaminer;
    private Site site;

    @BeforeClass(alwaysRun = true)
    public void classSetUp() throws IOException {
        this.site = siteData.createSite();
        this.tester = motApi.user.createTester(site.getId());
    }

    @BeforeMethod(alwaysRun = true)
    public void setUp() throws IOException {
        vehicle = vehicleData.getNewVehicle(tester);
        vehicleExaminer = motApi.user.createVehicleExaminer("ve", false);
    }

    @Test(testName = "MysteryShopper", groups = {"Regression", "BL-3655"},
            description = "Verifies that vehicle examiner can mask a vehicle for enforcement activities")
    public void veCanMaskVehicleSuccessfully() throws IOException, URISyntaxException {

        //Given i am on the Vehicle Information Page as an Vehicle examiner
        VehicleInformationPage vehicleInformationPage = pageNavigator
                                    .navigateToPage(vehicleExaminer, VehicleInformationSearchPage.PATH, VehicleInformationSearchPage.class)
                                    .searchVehicleByRegistration(vehicle.getDvsaRegistration(), VehicleInformationPage.class);

        //When I Mask the vehicle for enforcement activities
        vehicleInformationPage = vehicleInformationPage.clickMaskThisVehicleButton()
                                    .clickMaskThisVehicleButton()
                                    .clickContinueToVehicleRecordLink();

        //Then confirmation message should be displayed on the Vehicle Information page
        assertThat(vehicleInformationPage.isVehicleStatusBannerDisplayed(), is(true));
    }

    @Test(testName = "MysteryShopper", groups = {"Regression", "BL-3658"},
            description = "Verifies that vehicle examiner can unmask a vehicle and exclude that from enforcement activities")
    public void veCanUnmaskVehicleSuccessfully() throws IOException, URISyntaxException {

        //Given i am on the Vehicle Information Page as an Vehicle examiner with masked vehicle
        motUI.mysteryShopper.maskVehicle(vehicleExaminer, vehicle);

        //When I Unmask the vehicle for enforcement activities
        VehicleInformationPage vehicleInformationPage = motUI.mysteryShopper.unMaskVehicle(vehicleExaminer, vehicle);

        //Then confirmation message should not be displayed on the Vehicle Information page
        assertThat(vehicleInformationPage.isVehicleStatusBannerDisplayed(), is(false));
    }

    @Test(testName = "MysteryShopper", groups = {"Regression", "BL-3655"},
            description = "Verifies that vehicle examiner can navigate back from mask a vehicle page")
    public void veCanNavigateBackFromMaskVehiclePageSuccessfully() throws IOException, URISyntaxException {

        //Given i am on the Vehicle Information Page as an Vehicle examiner
        VehicleInformationPage vehicleInformationPage = pageNavigator
                                    .navigateToPage(vehicleExaminer, VehicleInformationSearchPage.PATH, VehicleInformationSearchPage.class)
                                    .searchVehicleByRegistration(vehicle.getDvsaRegistration(), VehicleInformationPage.class);

        //When I navigate back from Mask the vehicle page
        vehicleInformationPage = vehicleInformationPage.clickMaskThisVehicleButton().clickCancelAndReturnLink();

        //Then confirmation message should not be displayed on the Vehicle Information page
        assertThat(vehicleInformationPage.isVehicleStatusBannerDisplayed(), is(false));
    }

    @Test(testName = "MysteryShopper", groups = {"Regression", "BL-3658"},
            description = "Verifies that vehicle examiner can navigate back from unmask a vehicle page")
    public void veCanNavigateBackFromUnmaskVehiclePageSuccessfully() throws IOException, URISyntaxException {

        //Given i am on the Vehicle Information Page as an Vehicle examiner with masked vehicle
        VehicleInformationPage vehicleInformationPage = motUI.mysteryShopper.maskVehicle(vehicleExaminer, vehicle);

        //When I navigate back from Unmask the vehicle page
        vehicleInformationPage = vehicleInformationPage.clickUnmaskThisVehicleButton().clickCancelAndReturnLink();

        //Then confirmation message should be displayed on the Vehicle Information page
        assertThat(vehicleInformationPage.isVehicleStatusBannerDisplayed(), is(true));
    }

    @Test(testName = "MysteryShopper", groups = {"Regression", "BL-2489"},
            description = "Verifies that VE can perform Non_MOT inspection")
    public void veCanPerformNonMotInspectionSuccessfully() throws IOException, URISyntaxException {

        //Given i am on the Entry non mot test results Page as an Vehicle examiner
        TestResultsEntryNewPage testResultsEntryNewPage = motUI.nonMotInspection.startNonMotInspection(vehicleExaminer, vehicle);

        //When I finish non mot inspection with a pass values
        TestCompletePage testCompletePage = motUI.nonMotInspection.completeNonMotInspectionWithPassValues(site.getSiteNumber(), testResultsEntryNewPage);

        //Then I should be able to print certificate on Test complete page
        assertThat(testCompletePage.isPrintDocumentButtonDisplayed(), is(true));
    }

    @Test(testName = "MysteryShopper", groups = {"Regression", "BL-2489"},
            description = "Verifies that vehicle examiner can see non mot test on vehicle test history page")
    public void veCanSeeNonMotTestOnVehicleHistoryPage() throws IOException, URISyntaxException {

        //Given I am started non mot inspection as an Vehicle examiner
        TestResultsEntryNewPage testResultsEntryNewPage = motUI.nonMotInspection.startNonMotInspection(vehicleExaminer, vehicle);

        //When I pass non mot inspection and navigate to Vehicle MOT test history page
        VehicleMotTestHistoryPage vehicleMotTestHistoryPage = motUI.nonMotInspection
                                    .completeNonMotInspectionWithPassValues(site.getSiteNumber(), testResultsEntryNewPage)
                                    .clickReturnToHomepageLink()
                                    .clickVehicleInformationLink()
                                    .searchVehicleByRegistration(vehicle.getDvsaRegistration(), VehicleInformationPage.class)
                                    .clickViewMotHistoryLink();

        //Then non mot test should appear in mot test history table
        assertThat(vehicleMotTestHistoryPage.isNonMotTestDisplayed(), is(true));
    }

    @Test(testName = "MysteryShopper", groups = {"Regression", "BL-2489"},
            description = "Verifies that VE must provide a vts number during Non MOT inspection")
    public void veMustProvideVtsNumberDuringNonMotInspection() throws IOException, URISyntaxException {

        //Given i am on the Entry non mot test results Page as an Vehicle examiner
        TestResultsEntryNewPage testResultsEntryNewPage = motUI.nonMotInspection.startNonMotInspection(vehicleExaminer, vehicle);

        //When I am trying to finish non mot inspection without a valid vts number
        TestSummaryPage testSummaryPage = testResultsEntryNewPage.addOdometerReading(10000)
                                                            .clickReviewTestButton()
                                                            .fillSiteIdInput("")
                                                            .clickFinishButton(TestSummaryPage.class);

        //Then I should see a validation message on test summary page
        assertThat(testSummaryPage.isValidationMessageDisplayed(), is(true));
    }

    //TODO This test needs to be fixed asap
    @Test(testName = "MysteryShopper", groups = {"Skipped", "BL-3659"},
            description = "Verify that tester can conduct normal test on masked vehicle")
    public void testerCanConductMysteryShopperTest() throws IOException, URISyntaxException {

        //Given I've masked a vehicle for Enforcement activity as an Vehicle examiner
        motUI.mysteryShopper.maskVehicle(vehicleExaminer, vehicle);

        //When tester completes all test details with passing data
        TestCompletePage testCompletePage = pageNavigator.gotoTestResultsEntryPage(tester,vehicle)
                                                            .completeTestDetailsWithPassValues(false)
                                                            .clickReviewTestButton()
                                                            .clickFinishButton(TestCompletePage.class);

        //Then he should be able to complete the Test Successfully
        assertThat(testCompletePage.isReturnToHomepageLinkDisplayed(), is(true));
    }

    //TODO This test needs to be fixed asap
    @Test(testName = "MysteryShopper", groups = {"Skipped", "BL-3659"},
            description = "Verify that tester sees in his Test logs mystery shopper test as Normal test")
    public void testerSeeMysteryShopperTestAsNormalInHisTestLogs() throws IOException, URISyntaxException {

        //Given I've masked a vehicle for Enforcement activity as an Vehicle examiner
        motUI.mysteryShopper.maskVehicle(vehicleExaminer, vehicle);

        //When tester completes all test details with passing data
        pageNavigator.gotoTestResultsEntryPage(tester,vehicle).completeTestDetailsWithPassValues(false)
                .clickReviewTestButton()
                .clickFinishButton(TestCompletePage.class);

        //Then he should be able to complete the Test Successfully
        TesterTestLogPage testerTestLogPage = pageNavigator.navigateToPage(tester, PersonProfilePage.PATH, PersonProfilePage.class)
                                                            .clickTestLogsLink()
                                                            .clickTodayLink();

        assertThat(testerTestLogPage.getTestType().equals("Normal Test"), is(true));
    }
}
