package uk.gov.dvsa.ui.views.mot_test;

import org.testng.annotations.BeforeMethod;
import org.testng.annotations.Test;
import uk.gov.dvsa.domain.model.Site;
import uk.gov.dvsa.domain.model.User;
import uk.gov.dvsa.domain.api.response.Vehicle;
import uk.gov.dvsa.domain.model.vehicle.VehicleClass;
import uk.gov.dvsa.ui.DslTest;
import uk.gov.dvsa.ui.pages.mot.DefectCategoriesPage;
import uk.gov.dvsa.ui.pages.mot.DefectsPage;
import uk.gov.dvsa.ui.pages.mot.TestResultsEntryNewPage;

import java.io.IOException;
import java.net.URISyntaxException;

import static org.hamcrest.CoreMatchers.is;
import static org.hamcrest.MatcherAssert.assertThat;

public class DefectCategoriesPageTests extends DslTest {
    private User tester;
    private Vehicle vehicle;
    private Vehicle motorcycle;
    private User veUser;

    @BeforeMethod(alwaysRun = true)
    public void setUp() throws IOException {
        Site site = siteData.createSite();
        tester = userData.createTester(site.getId());
        vehicle = vehicleData.getNewVehicle(tester);
        motorcycle = vehicleData.getNewVehicle(tester, VehicleClass.one);
        veUser = userData.createVehicleExaminer("VehicleExaminer", false);
    }

    @Test(testName = "TestResultEntryImprovements", groups = {"BVT", "BL-1948"})
    public void navigateToDefectCategoriesPageTest() throws IOException, URISyntaxException {
        // Given I start an MOT test and I am on the MOT Test Results page
        TestResultsEntryNewPage testResultsEntryPage = pageNavigator.gotoTestResultsEntryNewPage(tester, vehicle);

        // When I click the "Add a defect" button
        testResultsEntryPage.clickAddDefectButton();

        // Then I should navigate to the Defect Categories page
    }

    @Test(testName = "TestResultEntryImprovements", groups = {"BVT", "BL-1931"})
    public void testCanNavigateThroughCategoryTreeAsTester() throws IOException, URISyntaxException {
        // Given I am on the test results entry screen as a tester
        TestResultsEntryNewPage testResultsEntryPage = pageNavigator.gotoTestResultsEntryNewPage(tester, vehicle);

        // When I click the "Add a defect" button
        DefectCategoriesPage defectCategoriesPage = testResultsEntryPage.clickAddDefectButton();

        // Then I should able to browse through the category tree
        DefectsPage defectsPage = defectCategoriesPage.navigateToDefectCategory("Brakes", "Brake performance", "Decelerometer", "Brake operation");

        // Then I should see a list of defects of that category
        defectsPage.defectsAreDisplayed();
    }

    @Test(testName = "TestResultEntryImprovements", groups = {"BVT", "BL-1931"})
    public void testCanViewNotTestedItemsAsVE() throws IOException, URISyntaxException {
        // Given I perform a training test as a VE
        TestResultsEntryNewPage testResultsEntryPage = pageNavigator.gotoTrainingTestResultsEntryNewPage(veUser, vehicle);

        // When I click the "Add a defect" button
        DefectCategoriesPage defectCategoriesPage = testResultsEntryPage.clickAddDefectButton();

        // Then I should able to browse through the category tree and see "Item not tested" entries
        defectCategoriesPage.navigateToDefectCategory("Items not tested", "Brake performance");
    }

    @Test(testName = "TestResultEntryImprovements", groups = {"Regression", "BL-3565"})
    public void testCategoryWithNoRfrNotDisplayedForVehicle() throws IOException, URISyntaxException {
        // Given I am on the test results entry screen as a tester
        TestResultsEntryNewPage testResultsEntryPage = pageNavigator.gotoTestResultsEntryNewPage(tester, vehicle);

        // When I click the "Add a defect" button
        DefectCategoriesPage defectCategoriesPage = testResultsEntryPage.clickAddDefectButton();

        // Then I should not be able to see an empty category
        assertThat(defectCategoriesPage.isCategoryDisplayed("Windows (old)", "Drivers view of the road"), is(false));
    }

    @Test(testName = "TestResultEntryImprovements", groups = {"Regression", "BL-3565"})
    public void testCategoryWithNoRfrNotDisplayedForMotorcycle() throws IOException, URISyntaxException {
        // Given I am on the test results entry screen as a tester
        TestResultsEntryNewPage testResultsEntryPage = pageNavigator.gotoTestResultsEntryNewPage(tester, motorcycle);

        // When I click the "Add a defect" button
        DefectCategoriesPage defectCategoriesPage = testResultsEntryPage.clickAddDefectButton();

        // Then I should not be able to see an empty category
        assertThat(defectCategoriesPage.isCategoryDisplayed("Headlamp aim (old)", "Motorcycle lighting and signalling"), is(false));
    }
}