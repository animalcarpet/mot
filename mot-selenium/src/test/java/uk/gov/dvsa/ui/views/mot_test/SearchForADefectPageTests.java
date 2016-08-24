package uk.gov.dvsa.ui.views.mot_test;

import org.testng.annotations.BeforeMethod;
import org.testng.annotations.Test;
import uk.gov.dvsa.domain.model.Site;
import uk.gov.dvsa.domain.model.User;
import uk.gov.dvsa.domain.model.vehicle.Vehicle;
import uk.gov.dvsa.ui.DslTest;
import uk.gov.dvsa.ui.pages.mot.SearchForADefectPage;
import uk.gov.dvsa.ui.pages.mot.TestResultsEntryNewPage;

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
        tester = userData.createTester(site.getId());
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
                "10mm", "2", "has damage to an area in excess of a 10mm circle within zone 'A'"), is(true));
    }
}