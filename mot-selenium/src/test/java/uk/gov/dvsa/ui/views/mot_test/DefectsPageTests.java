package uk.gov.dvsa.ui.views.mot_test;

import org.testng.annotations.BeforeMethod;
import org.testng.annotations.DataProvider;
import org.testng.annotations.Test;
import uk.gov.dvsa.domain.model.Site;
import uk.gov.dvsa.domain.model.User;
import uk.gov.dvsa.domain.model.mot.Defect;
import uk.gov.dvsa.domain.api.response.Vehicle;
import uk.gov.dvsa.helper.DefectsTestsDataProvider;
import uk.gov.dvsa.ui.DslTest;
import uk.gov.dvsa.ui.pages.mot.AddAManualAdvisoryPage;
import uk.gov.dvsa.ui.pages.mot.DefectCategoriesPage;
import uk.gov.dvsa.ui.pages.mot.DefectsPage;
import uk.gov.dvsa.ui.pages.mot.TestResultsEntryNewPage;

import java.io.IOException;
import java.net.URISyntaxException;

import static org.hamcrest.MatcherAssert.assertThat;
import static org.hamcrest.core.Is.is;

public class DefectsPageTests extends DslTest {

    protected User tester;
    protected Vehicle vehicle;


    @BeforeMethod(alwaysRun = true)
    protected void setUp() throws IOException {
        Site site = siteData.createSite();
        tester = motApi.user.createTester(site.getId());
        vehicle = vehicleData.getNewVehicle(tester);
    }

    @DataProvider(name = "getAdvisoryDefect")
    public Object[][] getAdvisoryDefect() throws IOException {
        return DefectsTestsDataProvider.getAdvisoryDefect();
    }

    @DataProvider(name = "getManualAdvisoryDefect")
    public Object[][] getManualAdvisoryDefect() throws IOException {
        return DefectsTestsDataProvider.getManualAdvisoryDefect();
    }

    @DataProvider(name = "getManualAdvisoryDefectNoDescription")
    public Object[][] getManualAdvisoryDefectNoDescription() throws IOException {
        return DefectsTestsDataProvider.getManualAdvisoryDefectWithNoDescription();
    }

    @Test(testName = "TestResultEntryImprovements", groups = {"BVT", "BL-1952"}, dataProvider = "getAdvisoryDefect")
    public void testCanAddADefectAsTester(Defect defect) throws IOException, URISyntaxException {
        // Given I am on the test results entry screen as a tester
        TestResultsEntryNewPage testResultsEntryPage = pageNavigator.gotoTestResultsEntryNewPage(tester, vehicle);

        // When I click the "Add a defect" button
        DefectCategoriesPage defectCategoriesPage = testResultsEntryPage.clickAddDefectButton();

        // Then I should able to browse through the category tree
        DefectsPage defectsPage = defectCategoriesPage.navigateToDefectCategory(defect.getCategoryPath());

        // Then I should see a list of defects of that category on the Defects page
        defectsPage.defectsAreDisplayed();

        // When I click the add Advisory/PRS/Failure button of a defect
        // Then I should navigate to the Add advisory/PRS/failure page
        defectsPage.navigateToAddDefectPage(defect);

        // When I click Add advisory/PRS/failure
        defectsPage.clickAddDefectButton();

        // Then I should see a message confirming the defect was successfully added
        assertThat(defectsPage.isDefectAddedSuccessMessageDisplayed(defect), is(true));
    }

    @Test(testName = "TestResultEntryImprovements",
            groups = {"BVT", "BL-2421"},
            dataProvider = "getManualAdvisoryDefect")
    public void testCanAddManualAdvisoryAsTester(Defect defect) throws IOException, URISyntaxException {
        // Given I am on the test results entry screen as a tester
        TestResultsEntryNewPage testResultsEntryPage = pageNavigator.gotoTestResultsEntryNewPage(tester, vehicle);

        // When I add a manual advisory
        DefectsPage defectsPage = testResultsEntryPage
                .clickAddDefectButton()
                .navigateToAddManualAdvisory()
                .fillDefectDescription(defect)
                .clickAddDefectButton();

        // Then I should see a message confirming the defect was successfully added
        assertThat(defectsPage.isManualAdvisoryDefectSuccessMessageDisplayed(defect), is(true));
    }

    @Test(testName = "TestResultEntryImprovements",
            groups = {"BVT", "BL-2421"},
            dataProvider = "getManualAdvisoryDefectNoDescription")
    public void testAddingManualAdvisoryWithoutDescriptionShowsValidationError(Defect defect) throws IOException, URISyntaxException {
        // Given I am on the test results entry screen as a tester
        TestResultsEntryNewPage testResultsEntryPage = pageNavigator.gotoTestResultsEntryNewPage(tester, vehicle);

        // When I add a manual advisory
        AddAManualAdvisoryPage defectsPage = testResultsEntryPage
                .clickAddDefectButton()
                .navigateToAddManualAdvisory()
                .fillDefectDescription(defect)
                .clickAddDefectButtonExpectingFailure();

        // Then I should see a message telling me that I need to enter a description
        assertThat(defectsPage.isManualAdvisoryDefectFailureMessageDisplayed(), is(true));
    }
}