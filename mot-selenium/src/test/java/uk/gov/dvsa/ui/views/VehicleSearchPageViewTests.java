package uk.gov.dvsa.ui.views;

import org.joda.time.DateTime;
import org.openqa.selenium.NoSuchElementException;
import org.openqa.selenium.TimeoutException;
import org.testng.annotations.BeforeMethod;
import org.testng.annotations.Test;
import uk.gov.dvsa.domain.model.AeDetails;
import uk.gov.dvsa.domain.model.Site;
import uk.gov.dvsa.domain.model.User;
import uk.gov.dvsa.domain.model.mot.TestOutcome;
import uk.gov.dvsa.domain.model.vehicle.Vehicle;
import uk.gov.dvsa.ui.DslTest;
import uk.gov.dvsa.ui.pages.VehicleSearchPage;

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
        tester = userData.createTester(site.getId());
        vehicle = vehicleData.getNewVehicle(tester);
    }

    @Test(groups = {"Regression"}, description = "VM-9368")
    public void breadCrumbTrailIsDisplayed() throws IOException, URISyntaxException {

        //Given I'm on the vehicle search page
        VehicleSearchPage vehicleSearchPage = pageNavigator.navigateToPage(tester, VehicleSearchPage.PATH, VehicleSearchPage.class);

        //I Expect the BreadCrumb to contain Mot Testing
        assertThat("Mot Testing Crumb Trail not displayed properly",
                vehicleSearchPage.getVehicleSearchStepNumber().contains("MOT testing"), is(true));

    }

    @Test(groups = {"Regression"}, description = "VM-9368")
    public void validationMessageIsDisplayedForInvalidRegOrVin() throws IOException, URISyntaxException {

        //Given I'm on vehicle search page
        VehicleSearchPage vehicleSearchPage = pageNavigator.navigateToPage(tester, VehicleSearchPage.PATH, VehicleSearchPage.class);

        //When I search for vehicle without registration and VIN
        vehicleSearchPage.searchVehicle("", "");

        //Then I should see the Validation Message
        assertThat(vehicleSearchPage.getMainMessageText(), containsString("0 vehicles found"));
        assertThat(vehicleSearchPage.getMainMessageText(), containsString("without a VIN"));
        assertThat(vehicleSearchPage.getMainMessageText(), containsString("without a registration"));

        //And the create new vehicle link is displayed
        verifyNewVehicleInfoAndLinkDisplayed(vehicleSearchPage, true);
    }

    @Test(groups = {"BVT"}, description = "VM-9368")
    public void validRegAndVinReturnsCorrectVehicle() throws IOException, URISyntaxException {

        //Given I'm on vehicle search page
        VehicleSearchPage vehicleSearchPage = pageNavigator.navigateToPage(tester, VehicleSearchPage.PATH, VehicleSearchPage.class);

        //When I search for a vehicle with correct VIN and registration number
        vehicleSearchPage.searchVehicle(vehicle.getDvsaRegistration(), vehicle.getVin());

        //Then the vehicle record is found
        assertThat(vehicleSearchPage.isResultVehicleDisplayed(), is(true));
        assertThat(vehicleSearchPage.getMainMessageText(), containsString("with registration " +
                vehicle.getDvsaRegistration() + " and a VIN matching " + vehicle.getVin() + "."));
    }

    private void verifyNewVehicleInfoAndLinkDisplayed(VehicleSearchPage page, boolean value) {
        assertThat(page.isCreateNewVehicleInfoDisplayed(), is(value));
        assertThat(page.isCreateNewVehicleRecordLinkDisplayed(), is(value));
    }


    @Test(groups = {"Regression","VM_9444"})
    public void forRetestIsDisplayedForVehicleWithFailedMotTest() throws IOException, URISyntaxException {

        //Given I have a vehicle with a failed MOT test
        motApi.createTest(tester, site.getId(), vehicle, TestOutcome.FAILED, 12345, DateTime.now());

        //When I search for this vehicle
        motUI.retest.searchForVehicle(tester, vehicle);

        //Then text for "Re-Test" is present on the page
        motUI.retest.isTextPresent("For retest");
    }

    @Test(groups = {"Regression", "VM-1854"}, expectedExceptions = TimeoutException.class)
    public void forRetestIsNotDisplayedForVehicleAfter10DayGracePeriod() throws IOException, URISyntaxException {

        //Given a vehicle with more than 10days failed mot test
        motApi.createTest(tester, site.getId(), vehicle, TestOutcome.FAILED, 12000, DateTime.now().minusDays(20));

        //When I search for and locate the vehicle
        motUI.retest.searchForVehicle(tester, vehicle);

        //Then I should not the text for "For retest" on the result
        motUI.retest.isTextPresent("For retest");
    }
}
