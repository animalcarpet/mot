package uk.gov.dvsa.ui.views;

import org.joda.time.DateTime;
import org.openqa.selenium.TimeoutException;
import org.testng.annotations.BeforeMethod;
import org.testng.annotations.Test;
import uk.gov.dvsa.domain.model.AeDetails;
import uk.gov.dvsa.domain.model.Site;
import uk.gov.dvsa.domain.model.User;
import uk.gov.dvsa.domain.model.mot.TestOutcome;
import uk.gov.dvsa.domain.api.response.Vehicle;
import uk.gov.dvsa.ui.DslTest;
import uk.gov.dvsa.ui.pages.VehicleSearchPage;
import uk.gov.dvsa.ui.pages.VehicleSearchResultsPage;
import uk.gov.dvsa.ui.pages.AbstractVehicleSearchResultsPage;

import java.io.IOException;
import java.net.URISyntaxException;

import static org.hamcrest.MatcherAssert.assertThat;
import static org.hamcrest.Matchers.containsString;
import static org.hamcrest.core.Is.is;

public class VehicleSearchPageViewTests extends DslTest {

    private User tester;
    private Vehicle vehicle;
    private Site site;

    @BeforeMethod(alwaysRun = true)
    private void setup() throws IOException {
        AeDetails aeDetails = aeData.createAeWithDefaultValues();
        site = siteData.createNewSite(aeDetails.getId(), "My_Site");
        tester = motApi.user.createTester(site.getId());
        vehicle = vehicleData.getNewVehicle(tester);
    }

    @Test(groups = {"BVT", "VM-9368"}, description = "Test vehicle search page for breadcrumbs, cancel and return link, Unable to provide Reg or VIN link and Search block")
    public void checkExpectedPageElements() throws IOException, URISyntaxException {

        //Given I'm on the vehicle search page as a tester
        VehicleSearchPage vehicleSearchPage = pageNavigator.navigateToPage(tester, VehicleSearchPage.PATH, VehicleSearchPage.class);

        //Then the page should be displayed correctly
        assertThat("Vehicle search page is not as expected", vehicleSearchPage.isBasePageContentCorrect(), is(true));

        //And the Unable to provide a registration mark or full VIN information is not displayed
        assertThat("Unable to provide a registration mark or full VIN information should not be displayed",
                vehicleSearchPage.isUnableToProvideRegOrVINTextDisplayed(), is(false));
    }

    @Test(groups = {"Regression", "VM-9368"}, description = "Test the vehicle search page Unable to provide Reg or VIN link works")
    public void testUnableToProvideRegOrVINLink() throws IOException, URISyntaxException {

        //Given I'm on the vehicle search page as a tester
        VehicleSearchPage vehicleSearchPage = pageNavigator.navigateToPage(tester, VehicleSearchPage.PATH, VehicleSearchPage.class);

        //When I click on the Unable to provide a registration mark or full VIN information
        vehicleSearchPage.clickUnableToProvideRegOrVIN();

        //Then the Unable to provide a registration mark or full VIN information is displayed
        assertThat("Unable to provide a registration mark or full VIN information should be displayed",
                vehicleSearchPage.isUnableToProvideRegOrVINTextDisplayed(), is(true));
    }

    @Test(groups = {"Regression", "VM-9368"}, description = "Tests that no vehicles are returned when searching for a blank Reg and VIN")
    public void testNoResultsReturnedForBlankRegOrVin() throws IOException, URISyntaxException {

        //Given I'm on vehicle search page as a tester
        VehicleSearchPage vehicleSearchPage = pageNavigator.navigateToPage(tester, VehicleSearchPage.PATH, VehicleSearchPage.class);

        //When I search for vehicle without registration and VIN
        AbstractVehicleSearchResultsPage vehicleSearchNoResultsPage = vehicleSearchPage.searchVehicle("", "", false);

        //Then the page should be displayed correctly (includes breadcrumbs, cancel and return link, Search summary, Create new vehicle, Unable to provide Reg or VIN link and Search block)
        assertThat("Vehicle search page is not as expected", vehicleSearchNoResultsPage.isBasePageContentCorrect(), is(true));

        //Then I should see the search summary no results found
        assertThat(vehicleSearchNoResultsPage.getSearchSummaryText(),
                containsString("0 vehicles found without a registration and without a VIN"));
    }

    @Test(groups = {"BVT", "VM-9368"}, description = "Tests that a vehicle that exists can be searched and found")
    public void validRegAndVinReturnsCorrectVehicle() throws IOException, URISyntaxException {

        //Given I'm on vehicle search page as a tester
        VehicleSearchPage vehicleSearchPage = pageNavigator.navigateToPage(tester, VehicleSearchPage.PATH, VehicleSearchPage.class);

        //When I search for a vehicle with correct VIN and registration number
        VehicleSearchResultsPage vehicleSearchResultsPage = vehicleSearchPage.searchVehicle(vehicle);

        //Then the page should be displayed correctly (includes breadcrumbs, cancel and return link, Search summary, Create new vehicle, search results)
        assertThat("Vehicle search page is not as expected", vehicleSearchResultsPage.isBasePageContentCorrect(), is(true));

        //Then the vehicle record is found
        assertThat(vehicleSearchResultsPage.getSearchSummaryText(), containsString(String.format(
                "with registration %s and a VIN matching %s.", vehicle.getDvsaRegistration(), vehicle.getVin())));

        //And the search fields not visible
        assertThat("Search fields should not be visible after a successful search",
                vehicleSearchResultsPage.isSearchSectionDisplayed(), is(false));
    }

    @Test(groups = {"Regression", "VM-9368"}, description = "Tests that once a vehicle has been found the search again link works")
    public void checkSearchAgainPresentAfterSuccessfulSearch() throws IOException, URISyntaxException {

        //Given I'm on vehicle search page as a tester
        VehicleSearchPage vehicleSearchPage = pageNavigator.navigateToPage(tester, VehicleSearchPage.PATH, VehicleSearchPage.class);

        //When I click on Search again after I search for a vehicle with correct VIN and registration number
        VehicleSearchResultsPage vehicleSearchResultsPage = vehicleSearchPage.searchVehicle(vehicle).clickSearchAgain();

        //Then the search fields should be visible
        assertThat("Search fields should be visible after clicking search again",
                vehicleSearchResultsPage.isSearchSectionDisplayed(), is(true));
    }

    @Test(groups = {"Regression","VM_9444"}, description = "Tests that a vehicle that has recently failed is marked as for Retest")
    public void forRetestIsDisplayedForVehicleWithFailedMotTest() throws IOException, URISyntaxException {

        //Given I have a vehicle with a failed MOT test as a tester
        motApi.createTest(tester, site.getId(), vehicle, TestOutcome.FAILED, 12345, DateTime.now());

        //When I search for this vehicle
        motUI.retest.searchForVehicle(tester, vehicle);

        //Then text for "Re-Test" is present on the page
        motUI.retest.isTextPresent("For retest");
    }

    @Test(groups = {"Regression", "VM-1854"}, description = "Tests that a vehicle that failed outside the retest grace period does not appear as for Retest", expectedExceptions = TimeoutException.class)
    public void forRetestIsNotDisplayedForVehicleAfter10DayGracePeriod() throws IOException, URISyntaxException {

        //Given a vehicle with more than 10days failed mot test as a tester
        motApi.createTest(tester, site.getId(), vehicle, TestOutcome.FAILED, 12000, DateTime.now().minusDays(20));

        //When I search for and locate the vehicle
        motUI.retest.searchForVehicle(tester, vehicle);

        //Then I should not the text for "For retest" on the result - This is a little confusing and only passes because the field can't be found and times out!
        motUI.retest.isTextPresent("For retest");
    }
}
