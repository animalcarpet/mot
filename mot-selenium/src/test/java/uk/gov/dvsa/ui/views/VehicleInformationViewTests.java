package uk.gov.dvsa.ui.views;

import org.joda.time.format.DateTimeFormat;
import org.testng.annotations.BeforeMethod;
import org.testng.annotations.Test;
import uk.gov.dvsa.domain.model.Site;
import uk.gov.dvsa.domain.model.User;
import uk.gov.dvsa.domain.model.vehicle.DvlaVehicle;
import uk.gov.dvsa.domain.model.vehicle.Vehicle;
import uk.gov.dvsa.ui.DslTest;
import uk.gov.dvsa.ui.pages.vehicleinformation.VehicleInformationPage;
import uk.gov.dvsa.ui.pages.vehicleinformation.VehicleInformationSearchPage;

import java.io.IOException;
import java.net.URISyntaxException;

import static org.hamcrest.MatcherAssert.assertThat;
import static org.hamcrest.Matchers.is;

public class VehicleInformationViewTests extends DslTest {
    private User tester;
    private Vehicle vehicle;

    @BeforeMethod(alwaysRun = true)
    public void setUp() throws IOException {
        Site site = siteData.createSite();
        tester = userData.createTester(site.getId());
        vehicle = vehicleData.getNewVehicle(tester);
    }

    @Test (groups = {"Regression"})
    public void viewVehicleInformationSuccessfully() throws IOException, URISyntaxException {
        User areaOffice1User = userData.createAreaOfficeOne("ao1");
        String vehicleManufactureDate = DateTimeFormat.forPattern("yyyy-MM-dd")
                .parseDateTime(vehicle.getManufactureDate())
                .toString("d MMMM yyyy");
        String vehicleColors = vehicle.getColour() + " and " + vehicle.getColourSecondary();
        String vehicleMakeModel = vehicle.getMake() + ", " + vehicle.getModel();

        //Given There are 2 vehicles with the same registration
        vehicleData.getNewVehicle(tester, vehicle.getDvsaRegistration());

        //And i am on the Vehicle Information Page as an AreaOffice1User
        VehicleInformationSearchPage vehicleInformationSearchPage =
                pageNavigator.navigateToPage(areaOffice1User, VehicleInformationSearchPage.PATH, VehicleInformationSearchPage.class);

        //When I search for a vehicle and open vehicle information page
        VehicleInformationPage vehicleInformationPage = vehicleInformationSearchPage
                .searchVehicleByRegistration(vehicle.getDvsaRegistration())
                .clickVehicleDetailsLink();

        //Then vehicle information will be correct
        assertThat(vehicleInformationPage.getPageHeaderTertiaryRegistration(), is(vehicle.getDvsaRegistration()));
        assertThat(vehicleInformationPage.getPageHeaderTertiaryVin(), is(vehicle.getVin()));
        assertThat(vehicleInformationPage.getPageHeaderTitle(), is(vehicleMakeModel));
        assertThat(vehicleInformationPage.getManufactureDate(), is(vehicleManufactureDate));
        assertThat(vehicleInformationPage.getColour(), is(vehicleColors));
        assertThat(vehicleInformationPage.getMakeModel(), is(vehicleMakeModel));
    }

    @Test (groups = {"Regression"})
    public void redirectToVehicleInformationIfFoundOnlyOneResult() throws IOException, URISyntaxException {
        User areaOffice1User = userData.createAreaOfficeOne("ao1");

        //Given i am on the Vehicle Information Page as an AreaOffice1User
        VehicleInformationSearchPage vehicleInformationSearchPage =
                pageNavigator.navigateToPage(areaOffice1User, VehicleInformationSearchPage.PATH, VehicleInformationSearchPage.class);

        //When I search for a vehicle
        VehicleInformationPage vehicleInformationPage = vehicleInformationSearchPage
            .findVehicleAndRedirectToVehicleInformationPage(vehicle.getDvsaRegistration());

        //Then I should be able to view that vehicles information

        assertThat("The registration is as expected", vehicleInformationPage.getRegistrationNumber(), is(vehicle.getDvsaRegistration()));
        assertThat("The Vin is as expected",vehicleInformationPage. getVinNumber(), is(vehicle.getVin()));
    }

    @Test(groups = {"Regression"}, description = "BL-46")
    public void displayUnknownForVehicleWithNoWeightInStartTestConfirmationPage() throws IOException, URISyntaxException {

        //Given I have a vehicle with no registered weight

        //When I search for the vehicle to perform a test on it
        motUI.normalTest.startTestConfirmationPage(tester, vehicle);

        //Then I should see its weight displayed as "Unknown"
        assertThat("Correct weight is Displayed", motUI.normalTest.getVehicleWeight(), is("Unknown"));
    }

    @Test(groups = {"Regression"})
    public void editVehicleClass() throws IOException, URISyntaxException {

        //Given I am on the StartTestConfirmation Page
        motUI.normalTest.startTestConfirmationPage(tester, vehicle);

        //When I edit the vehicle class
        motUI.normalTest.changeClass("5");

        //Then I submit the new class successfully
        assertThat(motUI.normalTest.isDeclarationStatementDisplayed(), is(true));
    }

    @Test(groups = {"Regression"})
    public void vehicleSearchReturnsVehicleOnlyInDvlaTable() throws IOException, URISyntaxException {

        //Given I have a vehicle in the DVLA table only
        User tester = userData.createTester(siteData.createSite().getId());
        DvlaVehicle dvlaVehicle = vehicleData.getNewDvlaVehicle(tester);

        //When I search for that Vehicle
        motUI.normalTest.startTestConfirmationPage(tester, dvlaVehicle);

        //Then I should find the vehicle
        assertThat(motUI.normalTest.getVin(), is(dvlaVehicle.getVin()));
        assertThat(motUI.normalTest.getRegistration(), is(dvlaVehicle.getRegistration()));
    }
}
