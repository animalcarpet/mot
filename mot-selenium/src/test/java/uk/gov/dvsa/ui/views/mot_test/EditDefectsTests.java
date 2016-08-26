package uk.gov.dvsa.ui.views.mot_test;

import org.testng.annotations.BeforeMethod;
import org.testng.annotations.DataProvider;
import org.testng.annotations.Test;
import uk.gov.dvsa.domain.model.Site;
import uk.gov.dvsa.domain.model.User;
import uk.gov.dvsa.domain.model.mot.Defect;
import uk.gov.dvsa.domain.model.vehicle.Vehicle;
import uk.gov.dvsa.helper.DefectsTestsDataProvider;
import uk.gov.dvsa.ui.DslTest;
import uk.gov.dvsa.ui.pages.mot.DefectsPage;
import uk.gov.dvsa.ui.pages.mot.EditDefectPage;
import uk.gov.dvsa.ui.pages.mot.TestResultsEntryNewPage;

import java.io.IOException;
import java.net.URISyntaxException;

import static org.hamcrest.MatcherAssert.assertThat;
import static org.hamcrest.core.Is.is;

public class EditDefectsTests extends DslTest {

    protected User tester;
    protected Vehicle vehicle;

    @BeforeMethod(alwaysRun = true)
    protected void setUp() throws IOException {
        Site site = siteData.createSite();
        tester = userData.createTester(site.getId());
        vehicle = vehicleData.getNewVehicle(tester);
    }

    @DataProvider(name = "getDefectArray")
    public Object[][] getDefectArray() throws IOException {
        return DefectsTestsDataProvider.getDefectArray();
    }

    @DataProvider(name = "getAdvisoryDefect")
    public Object[][] getAdvisoryDefect() throws IOException {
        return DefectsTestsDataProvider.getAdvisoryDefect();
    }

    @Test(testName = "TestResultEntryImprovements", groups = {"BVT", "BL-2405"}, dataProvider = "getDefectArray",
            description = "Checks that the Edt a defect page has the correct breadcrumb and button text")
    public void testEditDefectPageElements(Defect defect) throws IOException, URISyntaxException {

        // Given I am on the defects screen with a defect as a tester
        DefectsPage defectsPage = pageNavigator.gotoDefectsPageWithDefect(tester, vehicle, defect);

        // When I navigate to Edit a defect page
        EditDefectPage editDefectPage = defectsPage.navigateToEditDefectPage(defect);

        // Then the breadcrumb is correctly displayed and the edit button is correctly displayed
        assertThat(editDefectPage.checkBreadcrumbExists() && editDefectPage.checkRemoveButtonExists(), is(true));
    }

    @Test(testName = "TestResultEntryImprovements", groups = {"BVT", "BL-2406"},
            dataProvider = "getDefectArray", description = "Checks that you can edit a defect from the defects screen")
    public void testCanEditADefectFromDefectScreenAsTester(Defect defect) throws IOException, URISyntaxException {

        // Given I am on the defects screen with a defect as a tester
        DefectsPage defectsPage = pageNavigator.gotoDefectsPageWithDefect(tester, vehicle, defect);

        // When I edit the defect
        defectsPage = defectsPage.navigateToEditDefectPage(defect)
                .clickIsDangerous(defect)
                .clickEditAndReturnToPage(DefectsPage.class);

        // Then I will be presented with the defect successfully edited message and the defect will have been edited to be dangerous
        assertThat(defectsPage.isDefectEditSuccessMessageDisplayed(defect) && defectsPage.isDefectDangerous(defect), is(true));
    }

    @Test(testName = "TestResultEntryImprovements", groups = {"BVT", "BL-2405"}, dataProvider = "getDefectArray",
            description = "Checks that you can return to the defects screen without editing a defect")
    public void testCanReturnToDefectsScreenAsTester(Defect defect) throws IOException, URISyntaxException {

        // Given I am on the defects screen with a defect as a tester
        DefectsPage defectsPage = pageNavigator.gotoDefectsPageWithDefect(tester, vehicle, defect);

        // When I go to edit the defect and click cancel and return
        defectsPage = defectsPage.navigateToEditDefectPage(defect).cancelAndReturnToPage(DefectsPage.class);

        // Then I am returned to the defects page
        assertThat(defectsPage.defectsAreDisplayed(), is(true));
    }

    @Test(testName = "TestResultEntryImprovements", groups = {"BVT", "BL-2406"},
            dataProvider = "getDefectArray", description = "Checks that you can edit a defect from the test results screen")
    public void testCanEditADefectFromTestResultsScreenAsTester(Defect defect) throws IOException, URISyntaxException {

        // Given I am on the test results screen with a defect as a tester
        TestResultsEntryNewPage testResultsEntryNewPage = pageNavigator.
                                                            gotoTestResultsPageWithDefect(tester, vehicle, defect);

        // When I edit the defect
        testResultsEntryNewPage = testResultsEntryNewPage.navigateToEditDefectPage(defect)
                .clickIsDangerous(defect)
                .clickEditAndReturnToPage(TestResultsEntryNewPage.class);

        // Then I will be presented with the defect successfully edited message and the defect has been edited
        assertThat(testResultsEntryNewPage.isDefectEditedSuccessMessageDisplayed(defect) && testResultsEntryNewPage.isDefectDangerous(defect), is(true));
    }

    @Test(testName = "TestResultEntryImprovements", groups = {"BVT", "BL-2406"}, dataProvider = "getDefectArray",
            description = "Checks that you can return to the test results entry screen without editing a defect")
    public void testCanReturnToTestResultsScreenAsTester(Defect defect) throws IOException, URISyntaxException {

        // Given I am on the test results screen with a defect as a tester
        TestResultsEntryNewPage testResultsEntryNewPage = pageNavigator.gotoTestResultsPageWithDefect(
                tester, vehicle, defect);

        // When I go to remove the defect and click cancel and return
        testResultsEntryNewPage = testResultsEntryNewPage.navigateToEditDefectPage(defect).cancelAndReturnToPage(
                TestResultsEntryNewPage.class);

        // Then I am returned to the test results entry page and the defect has not been edited
        assertThat(testResultsEntryNewPage.isDefectDangerous(defect), is(false));
    }
}