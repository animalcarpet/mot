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
import uk.gov.dvsa.ui.pages.mot.*;

import java.io.IOException;
import java.net.URISyntaxException;

import static org.hamcrest.MatcherAssert.assertThat;
import static org.hamcrest.core.Is.is;

public class SearchForADefectPageTests extends DslTest {

    private User tester;
    private Vehicle vehicle;

    @BeforeMethod(alwaysRun = true)
    public void setUp() throws IOException {
        Site site = siteData.createSite();
        tester = motApi.user.createTester(site.getId());
        vehicle = vehicleData.getNewVehicle(tester);
    }

    @Test(testName = "TestResultEntryImprovements", groups = {"BVT", "BL-1932"},
            description = "Checks that the Search for a defect page has the correct page elements")
    public void testSearchForADefectPageElements() throws IOException, URISyntaxException {

        // Given I am on the MOT test results screen
        TestResultsEntryNewPage testResultsEntryNewPage = pageNavigator.gotoTestResultsEntryNewPage(tester, vehicle);

        // When I click Search for a defect
        SearchForADefectPage searchForADefectPage = testResultsEntryNewPage.clickSearchForADefectButton();

        // Then the breadcrumb, defect categories link and manual advisory link are displayed
        assertThat("Search page elements are not displayed", searchForADefectPage.checkPageElementsDisplayed(), is(true));
    }

    @Test(testName = "TestResultEntryImprovements", groups = {"BVT", "BL-1932"},
            description = "Checks that the defect categories link navigates to the Defect categories screen")
    public void testDefectCategoriesNavigation() throws IOException, URISyntaxException {

        // Given I am on the Search for a defect page
        SearchForADefectPage searchForADefectPage = pageNavigator.gotoSearchForADefectPage(tester, vehicle);

        // When I click defect categories link
        searchForADefectPage.clickDefectCategoriesLink();

        // Then I should be navigated to the Defect categories screen
    }

    @Test(testName = "TestResultEntryImprovements", groups = {"BVT", "BL-1932"},
            description = "Checks that the Finish and return to MOT test results navigates to the MOT test results page")
    public void testFinishAndReturnToMOTTestResultsNavigation() throws IOException, URISyntaxException {

        // Given I am on the Search for a defect page
        SearchForADefectPage searchForADefectPage = pageNavigator.gotoSearchForADefectPage(tester, vehicle);

        // When I click Finish and return to MOT Test results
        searchForADefectPage.clickFinishAndReturnToMOTTestButton();

        // Then I should be navigated to the MOT Test results page
    }

    @Test(testName = "TestResultEntryImprovements", groups = {"BVT", "BL-1932"},
            description = "Checks that the Search returns no results for a search string that does not exist")
    public void testSearchForADefectPageNoResults() throws IOException, URISyntaxException {

        // Given I am on the Search for a defect page
        SearchForADefectPage searchForADefectPage = pageNavigator.gotoSearchForADefectPage(tester, vehicle);

        // When I click Search for a defect
        searchForADefectPage = searchForADefectPage.searchForDefect("foobar");

        // Then the search summary should contain the search string foobar and the result count is 0
        assertThat("Search summary should contain search string",
                searchForADefectPage.checkSearchSummaryCorrect("foobar", "0"), is(true));
    }

    @Test(testName = "TestResultEntryImprovements", groups = {"BVT", "BL-1932"},
            description = "Checks that the Searching returns the correct results")
    public void testSearchForADefectPageWithResults() throws IOException, URISyntaxException {

        // Given I am on the Search for a defect page
        SearchForADefectPage searchForADefectPage = pageNavigator.gotoSearchForADefectPage(tester, vehicle);

        // When I click Search for a defect
        searchForADefectPage = searchForADefectPage.searchForDefect("10mm");

        // Then the search summary should contain the search string 10mm, the count 2 and expected result should appear
        assertThat("Search summary should contain search string", searchForADefectPage.checkSearchResultsCorrect(
                "10mm", "2", "Windscreen has damage to an area in excess of a 10mm circle within zone 'A'"), is(true));
    }

    @Test(testName = "TestResultEntryImprovements", groups = {"BVT", "BL-3075"},
            description = "Checks that the Searching returns are not paginated when there are less than 10 results")
    public void testSearchForADefectPageWithNoPagination() throws IOException, URISyntaxException {

        // Given I am on the Search for a defect page
        SearchForADefectPage searchForADefectPage = pageNavigator.gotoSearchForADefectPage(tester, vehicle);

        // When I click Search for a defect
        searchForADefectPage = searchForADefectPage.searchForDefect("mirror");

        // Then the search results should be paginated
        assertThat("Search results should not be paginated", searchForADefectPage.isPaginationDisplayed(), is(false));
    }

    @Test(testName = "TestResultEntryImprovements", groups = {"BVT", "BL-3075"},
            description = "Checks that the Searching returns are paginated when there are more than 10 results")
    public void testSearchForADefectPageWithPagination() throws IOException, URISyntaxException {

        // Given I am on the Search for a defect page
        SearchForADefectPage searchForADefectPage = pageNavigator.gotoSearchForADefectPage(tester, vehicle);

        // When I click Search for a defect
        searchForADefectPage = searchForADefectPage.searchForDefect("100");

        // Then the search results should be paginated
        assertThat("Search results should be paginated", searchForADefectPage.isPaginationDisplayed(), is(true));
    }

    @Test(testName = "TestResultEntryImprovements", groups = {"BVT", "BL-3075"},
            description = "Checks that the first page pagination does not display a previous link")
    public void testSearchResultsPaginationFirstPage() throws IOException, URISyntaxException {

        // Given I am on the Search for a defect page
        SearchForADefectPage searchForADefectPage = pageNavigator.gotoSearchForADefectPage(tester, vehicle);

        // When I click Search for a defect
        searchForADefectPage = searchForADefectPage.searchForDefect("100");

        // Then the search results should be paginated
        assertThat("Pagination should not show previous but should show next on first page",
                !searchForADefectPage.isPaginationPreviousDisplayed() && searchForADefectPage.isPaginationNextDisplayed(), is(true));
    }

    @Test(testName = "TestResultEntryImprovements", groups = {"BVT", "BL-3075"},
            description = "Checks that after navigating to the middle page of pagination the previous and next button should be displayed")
    public void testSearchResultsPaginationPage2() throws IOException, URISyntaxException {

        // Given I am on the Search for a defect page
        SearchForADefectPage searchForADefectPage = pageNavigator.gotoSearchForADefectPage(tester, vehicle);

        // When I click Search for a defect
        searchForADefectPage = searchForADefectPage.searchForDefect("100").navigateToPage(2);

        // Then the search results should be paginated
        assertThat("Pagination should show previous and next links",
                searchForADefectPage.isPaginationPreviousDisplayed() && searchForADefectPage.isPaginationNextDisplayed(), is(true));
    }

    @Test(testName = "TestResultEntryImprovements", groups = {"BVT", "BL-3075"},
            description = "Checks that after navigating to the last page of pagination the next link is not displayed")
    public void testSearchResultsPaginationLastPage() throws IOException, URISyntaxException {

        // Given I am on the Search for a defect page
        SearchForADefectPage searchForADefectPage = pageNavigator.gotoSearchForADefectPage(tester, vehicle);

        // When I click Search for a defect
        searchForADefectPage = searchForADefectPage.searchForDefect("100").navigateToLastPage();

        // Then the search results should be paginated
        assertThat("Pagination should show previous but not should show next on last page",
                searchForADefectPage.isPaginationPreviousDisplayed() && !searchForADefectPage.isPaginationNextDisplayed(), is(true));
    }

    @Test(testName = "TestResultEntryImprovements", groups = {"BVT", "BL-3075"},
            description = "Checks that after navigating to the 5th page of pagination where there are more than 5 displays the correct pagination numbers")
    public void testSearchResultsPaginationMoreThan5Pages() throws IOException, URISyntaxException {

        // Given I am on the Search for a defect page
        SearchForADefectPage searchForADefectPage = pageNavigator.gotoSearchForADefectPage(tester, vehicle);

        // When I click Search for a defect and navigate to page 5 (of more than 7 pages)
        searchForADefectPage = searchForADefectPage.searchForDefect("1").navigateToPage(5);

        // Then the pagination should now have links for pages 3, 4 6 and 7
        assertThat("Pagination links are not as expected", searchForADefectPage.doesPageLinkExist(3)
                && searchForADefectPage.doesPageLinkExist(4)
                && searchForADefectPage.doesPageLinkExist(6)
                && searchForADefectPage.doesPageLinkExist(7), is(true));
    }

    @DataProvider(name = "getManualAdvisoryDefect")
    public Object[][] getManualAdvisoryDefect() throws IOException {
        return DefectsTestsDataProvider.getManualAdvisoryDefect();
    }

    @DataProvider(name = "getManualAdvisoryDefectNoDescription")
    public Object[][] getManualAdvisoryDefectNoDescription() throws IOException {
        return DefectsTestsDataProvider.getManualAdvisoryDefectWithNoDescription();
    }

    @Test(testName = "TestResultEntryImprovements",
            groups = {"BVT", "BL-2421"},
            dataProvider = "getManualAdvisoryDefect")
    public void canAddManualAdvisoryAsTester(Defect defect) throws IOException, URISyntaxException {
        // Given I am on the Search for a defect page
        SearchForADefectPage searchForADefectPage = pageNavigator.gotoSearchForADefectPage(tester, vehicle);

        // When I add a manual advisory
        DefectsPage defectsPage = searchForADefectPage
                .navigateToAddAManualAdvisory()
                .fillDefectDescription(defect)
                .clickAddDefectButton();

        // Then I should see a message confirming the defect was successfully added
        assertThat(defectsPage.isManualAdvisoryDefectSuccessMessageDisplayed(defect), is(true));
    }

    @Test(testName = "TestResultEntryImprovements",
            groups = {"BVT", "BL-2421"},
            dataProvider = "getManualAdvisoryDefectNoDescription")
    public void testAddingManualAdvisoryWithoutDescriptionShowsValidationError(Defect defect) throws IOException, URISyntaxException {
        // Given I am on the Search for a defect page
        SearchForADefectPage searchForADefectPage = pageNavigator.gotoSearchForADefectPage(tester, vehicle);

        // When I add a manual advisory
        AddAManualAdvisoryPage defectsPage = searchForADefectPage
                .navigateToAddAManualAdvisory()
                .fillDefectDescription(defect)
                .clickAddDefectButtonExpectingFailure();

        // Then I should see a message telling me that I need to enter a description
        assertThat(defectsPage.isManualAdvisoryDefectFailureMessageDisplayed(), is(true));
    }
}