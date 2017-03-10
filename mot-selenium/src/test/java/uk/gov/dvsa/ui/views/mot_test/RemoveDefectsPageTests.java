package uk.gov.dvsa.ui.views.mot_test;

import org.openqa.selenium.TimeoutException;
import org.testng.annotations.BeforeMethod;
import org.testng.annotations.DataProvider;
import org.testng.annotations.Test;
import uk.gov.dvsa.domain.model.Site;
import uk.gov.dvsa.domain.model.User;
import uk.gov.dvsa.domain.model.mot.Defect;
import uk.gov.dvsa.domain.api.response.Vehicle;
import uk.gov.dvsa.helper.DefectsTestsDataProvider;
import uk.gov.dvsa.ui.DslTest;
import uk.gov.dvsa.ui.pages.mot.DefectsPage;
import uk.gov.dvsa.ui.pages.mot.RemoveDefectPage;
import uk.gov.dvsa.ui.pages.mot.TestResultsEntryNewPage;

import java.io.IOException;
import java.net.URISyntaxException;

import static org.hamcrest.MatcherAssert.assertThat;
import static org.hamcrest.core.Is.is;

public class RemoveDefectsPageTests extends DslTest {

    protected User tester;
    protected Vehicle vehicle;

    @BeforeMethod(alwaysRun = true)
    protected void setUp() throws IOException {
        Site site = siteData.createSite();
        tester = motApi.user.createTester(site.getId());
        vehicle = vehicleData.getNewVehicle(tester);
    }

    @DataProvider(name = "getDefectArray")
    public Object[][] getDefectArray() throws IOException {
        return DefectsTestsDataProvider.getDefectArray();
    }

    @Test(groups = {"BVT", "BL-2405"}, dataProvider = "getDefectArray",
            description = "Checks that the Remove a defect page has the correct breadcrumb and button text")
    public void testRemoveDefectPageElements(Defect defect) throws IOException, URISyntaxException {

        // Given I am on the defects screen with a defect as a tester
        DefectsPage defectsPage = pageNavigator.gotoDefectsPageWithDefect(tester, vehicle, defect);

        // When I navigate to remove a defect page
        RemoveDefectPage removeDefectPage = defectsPage.navigateToRemoveDefectPage(defect);

        // Then the breadcrumb is correctly displayed
        assertThat(removeDefectPage.checkBreadcrumbExists(), is(true));

        // And the remove button is correctly displayed
        assertThat(removeDefectPage.checkRemoveButtonExists(), is(true));
    }

    @Test(groups = {"BVT", "BL-2405"}, expectedExceptions = TimeoutException.class, dataProvider = "getDefectArray",
            description = "Checks that you can remove a defect from the defects screen")
    public void testCanRemoveADefectFromDefectScreenAsTester(Defect defect) throws IOException, URISyntaxException {

        // Given I am on the defects screen with a defect as a tester
        DefectsPage defectsPage = pageNavigator.gotoDefectsPageWithDefect(tester, vehicle, defect);

        // When I remove the defect
        defectsPage = defectsPage.navigateToRemoveDefectPage(defect).removeDefectAndReturnToPage(DefectsPage.class);

        // Then I will be presented with the defect successfully removed message
        assertThat(defectsPage.isDefectRemovedSuccessMessageDisplayed(defect), is(true));

        // And the defect does not exist in the reasons for rejection
        assertThat(defectsPage.isDefectInReasonsForRejection(defect), is(false));
    }

    @Test(groups = {"BVT", "BL-2405"}, dataProvider = "getDefectArray",
            description = "Checks that you can return to the defects screen without removing a defect")
    public void testCanReturnToDefectsScreenAsTester(Defect defect) throws IOException, URISyntaxException {

        // Given I am on the defects screen with a defect as a tester
        DefectsPage defectsPage = pageNavigator.gotoDefectsPageWithDefect(tester, vehicle, defect);

        // When I go to remove the defect and click cancel and return
        defectsPage = defectsPage.navigateToRemoveDefectPage(defect).cancelAndReturnToPage(DefectsPage.class);

        // Then I am returned to the defects page and the defect still remains
        assertThat(defectsPage.isDefectInReasonsForRejection(defect), is(true));
    }

    @Test(groups = {"BVT", "BL-2405"}, expectedExceptions = TimeoutException.class,
            dataProvider = "getDefectArray", description = "Checks that you can remove a defect from the test results screen")
    public void testCanRemoveADefectFromTestResultsScreenAsTester(Defect defect) throws IOException, URISyntaxException {

        // Given I am on the test results screen with a defect as a tester
        TestResultsEntryNewPage testResultsEntryNewPage = pageNavigator.gotoTestResultsPageWithDefect(tester, vehicle, defect);

        // When I remove the defect
        testResultsEntryNewPage = testResultsEntryNewPage.navigateToRemoveDefectPage(defect).removeDefectAndReturnToPage(
                TestResultsEntryNewPage.class);

        // Then I will be presented with the defect successfully removed message
        assertThat(testResultsEntryNewPage.isDefectRemovedSuccessMessageDisplayed(defect), is(true));

        // And the defect does not exist in the reasons for rejection
        assertThat(testResultsEntryNewPage.isDefectInReasonsForRejection(defect), is(false));
    }

    @Test(groups = {"BVT", "BL-2405"}, dataProvider = "getDefectArray",
            description = "Checks that you can remove a defect from the test results screen")
    public void testCanReturnToTestResultsScreenAsTester(Defect defect) throws IOException, URISyntaxException {

        // Given I am on the test results screen with a defect as a tester
        TestResultsEntryNewPage testResultsEntryNewPage = pageNavigator.gotoTestResultsPageWithDefect(tester, vehicle, defect);

        // When I go to remove the defect and click cancel and return
        testResultsEntryNewPage = testResultsEntryNewPage.navigateToRemoveDefectPage(defect).cancelAndReturnToPage(
                TestResultsEntryNewPage.class);

        // Then I am returned to the test results entry page and the defect still remains
        assertThat(testResultsEntryNewPage.isDefectInReasonsForRejection(defect), is(true));
    }
}
