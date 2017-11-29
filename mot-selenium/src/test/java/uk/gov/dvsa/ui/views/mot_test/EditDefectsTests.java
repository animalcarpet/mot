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
import uk.gov.dvsa.ui.pages.mot.DefectCategoriesPage;
import uk.gov.dvsa.ui.pages.mot.DefectsPage;
import uk.gov.dvsa.ui.pages.mot.EditDefectPage;
import uk.gov.dvsa.ui.pages.mot.TestResultsEntryNewPage;

import java.io.IOException;
import java.net.URISyntaxException;

import static org.hamcrest.MatcherAssert.assertThat;
import static org.hamcrest.core.Is.is;

public class EditDefectsTests extends DslTest {

    public static final String ORIGINAL_TEST_COMMENT = "Original test comment";
    public static final String NEW_COMMENT_ON_EDIT = "New comment on edit";
    public static final String LOC_LATERAL_ORIGINAL_NEARSIDE = "Nearside";
    public static final String LOCATION_LATERAL_EDITED_CENTRAL = "Central";
    public static final String LOC_LONG_ORIGINAL_FRONT = "Front";
    public static final String LOC_LONG_EDITED_REAR = "Rear";
    public static final String LOC_VERTICAL_ORIGINAL_UPPER = "Upper";
    public static final String LOC_VERTICAL_EDITED_INNER = "Inner";
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
        return DefectsTestsDataProvider.getDefectArray(false, false, false);
    }

    @DataProvider(name = "getAdvisoryDefect")
    public Object[][] getAdvisoryDefect() throws IOException {
        return DefectsTestsDataProvider.getAdvisoryDefect(false);
    }

    @Test(groups = {"BVT", "BL-2405"}, dataProvider = "getDefectArray",
            description = "Checks that the Edt a defect page has the correct breadcrumb and button text")
    public void testEditDefectPageElements(Defect defect) throws IOException, URISyntaxException {
        // Given I am on the defects screen with a defect as a tester
        DefectsPage defectsPage = pageNavigator.gotoDefectsPageWithDefect(tester, vehicle, defect);

        // When I navigate to Edit a defect page
        EditDefectPage editDefectPage = defectsPage.navigateToEditDefectPage(defect);

        // Then the breadcrumb is correctly displayed and the edit button is correctly displayed
        assertThat(editDefectPage.checkBreadcrumbExists() && editDefectPage.checkRemoveButtonExists(), is(true));
    }

    @Test(groups = {"BVT", "BL-2406"}, dataProvider = "getDefectArray",
            description = "Checks that you can edit a defect from the defects screen")
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

    @Test(groups = {"Regression", "BL-2405"}, dataProvider = "getDefectArray",
            description = "Checks that you can return to the defects screen without editing a defect")
    public void testCanReturnToDefectsScreenAsTester(Defect defect) throws IOException, URISyntaxException {
        // Given I am on the defects screen with a defect as a tester
        DefectsPage defectsPage = pageNavigator.gotoDefectsPageWithDefect(tester, vehicle, defect);

        // When I go to edit the defect and click cancel and return
        defectsPage = defectsPage.navigateToEditDefectPage(defect).cancelAndReturnToPage(DefectsPage.class);

        // Then I am returned to the defects page
        assertThat(defectsPage.defectsAreDisplayed(), is(true));
    }

    @Test(groups = {"Regression", "BL-2406"}, dataProvider = "getDefectArray",
            description = "Checks that you can edit a defect from the test results screen")
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

    @Test(groups = {"BVT", "BL-2406"}, dataProvider = "getDefectArray",
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

    @Test(groups = {"BVT"}, dataProvider = "getDefectArray",
            description = "Verifies that the new info in an edited defect is saved")
    public void editDefectSuccessfully(Defect defect) throws IOException, URISyntaxException, InterruptedException {

        //Given I have added a new defect as a tester
        TestResultsEntryNewPage testResultsEntryNewPage = pageNavigator.gotoTestResultsEntryNewPage(tester,vehicle);

        testResultsEntryNewPage
                .clickAddDefectButton()
                .navigateToDefectCategory(defect.getCategoryPath())
                .navigateToAddDefectPage(defect)
                .addComment(ORIGINAL_TEST_COMMENT)
                .setLocationLateral(LOC_LATERAL_ORIGINAL_NEARSIDE)
                .setLocationLongitudinal(LOC_LONG_ORIGINAL_FRONT)
                .setLocationVertical(LOC_VERTICAL_ORIGINAL_UPPER)
                .setIsDangerous()
                .clickAddDefectButton()
                .clickFinishAndReturnButton();
        
        //When I edit all editable fields for the defect
        EditDefectPage editDefectPage = testResultsEntryNewPage.navigateToEditDefectPage(defect);
        assertThat(editDefectPage.getComment(), is(ORIGINAL_TEST_COMMENT));
        assertThat(editDefectPage.isDangerousChecked(), is(true));
        assertThat(editDefectPage.getLocationLateral(), is(LOC_LATERAL_ORIGINAL_NEARSIDE));
        assertThat(editDefectPage.getLocationLongitudinal(), is(LOC_LONG_ORIGINAL_FRONT));
        assertThat(editDefectPage.getLocationVertical(), is(LOC_VERTICAL_ORIGINAL_UPPER));

        editDefectPage
                .addComment(NEW_COMMENT_ON_EDIT)
                .unsetIsDangerous(defect)
                .setLocationLateral(LOCATION_LATERAL_EDITED_CENTRAL)
                .setLocationLongitudinal(LOC_LONG_EDITED_REAR)
                .setLocationVertical(LOC_VERTICAL_EDITED_INNER);
        editDefectPage.clickEditAndReturnToPage(TestResultsEntryNewPage.class);

        //Then the new details entered on edit are persisted
        editDefectPage = testResultsEntryNewPage.navigateToEditDefectPage(defect);
        assertThat(editDefectPage.getComment(), is(NEW_COMMENT_ON_EDIT));
        assertThat(editDefectPage.isDangerousChecked(), is(false));
        assertThat(editDefectPage.getLocationLateral(), is(LOCATION_LATERAL_EDITED_CENTRAL));
        assertThat(editDefectPage.getLocationLongitudinal(), is(LOC_LONG_EDITED_REAR));
        assertThat(editDefectPage.getLocationVertical(), is(LOC_VERTICAL_EDITED_INNER));
    }
}
