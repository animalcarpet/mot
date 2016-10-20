package uk.gov.dvsa.ui.views;

import org.joda.time.format.DateTimeFormat;
import org.testng.annotations.BeforeMethod;
import org.testng.annotations.Test;
import uk.gov.dvsa.domain.api.response.Vehicle;
import uk.gov.dvsa.domain.model.Site;
import uk.gov.dvsa.domain.model.User;
import uk.gov.dvsa.domain.model.vehicle.*;
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
    private User areaOffice1User;
    private User vehicleExaminer;

    @BeforeMethod(alwaysRun = true)
    public void setUp() throws IOException {
        Site site = siteData.createSite();
        tester = motApi.user.createTester(site.getId());
        vehicle = vehicleData.getNewVehicle(tester);
        areaOffice1User = motApi.user.createAreaOfficeOne("ao1");
        vehicleExaminer = motApi.user.createVehicleExaminer("ve", false);
    }

    @Test (groups = {"Regression"})
    public void viewVehicleInformationSuccessfully() throws IOException, URISyntaxException {
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
        assertThat(vehicleInformationPage.getPageHeaderTitle(), is(vehicle.getMakeModelWithSeparator(", ")));
        assertThat(vehicleInformationPage.getManufactureDate(), is(getManufactureDateForVehicle(vehicle.getManufactureDate())));
        assertThat(vehicleInformationPage.getColour(), is(vehicle.getColorsWithSeparator(" and ")));
        assertThat(vehicleInformationPage.getMakeModel(), is(vehicle.getMakeModelWithSeparator(", ")));
    }

    @Test (groups = {"Regression"})
    public void redirectToVehicleInformationIfFoundOnlyOneResult() throws IOException, URISyntaxException {
        //Given i am on the Vehicle Information Page as an AreaOffice1User
        VehicleInformationSearchPage vehicleInformationSearchPage =
                pageNavigator.navigateToPage(areaOffice1User, VehicleInformationSearchPage.PATH, VehicleInformationSearchPage.class);

        //When I search for a vehicle
        VehicleInformationPage vehicleInformationPage = vehicleInformationSearchPage
            .findVehicleAndRedirectToVehicleInformationPage(vehicle.getDvsaRegistration());

        //Then I should be able to view that vehicles information

        assertThat("The registration is as expected", vehicleInformationPage.getRegistrationNumber(), is(vehicle.getDvsaRegistration()));
        assertThat("The Vin is as expected",vehicleInformationPage.getVinNumber(), is(vehicle.getVin()));
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
        User tester = motApi.user.createTester(siteData.createSite().getId());
        DvlaVehicle dvlaVehicle = vehicleData.getNewDvlaVehicle(tester);

        //When I search for that Vehicle
        motUI.normalTest.startTestConfirmationPage(tester, dvlaVehicle);

        //Then I should find the vehicle
        assertThat(motUI.normalTest.getVin(), is(dvlaVehicle.getVin()));
        assertThat(motUI.normalTest.getRegistration(), is(dvlaVehicle.getRegistration()));
    }

    @Test(groups = {"Regression"})
    public void vehicleEditEngineCorrectByAreaOffice() throws  IOException, URISyntaxException {
        //And i am on the Vehicle Information Page as an AreaOffice1User
        motUI.showVehicleInformationFor(areaOffice1User, vehicle);

        //When I change Engine
        motUI.vehicleInformation.changeEngine(FuelTypes.Gas, "234");

        //Then Engine will be changed
        assertThat(motUI.vehicleInformation.getEngine(), is("Gas, 234 cc"));
    }

    @Test(groups = {"Regression"})
    public void vehicleEditMotTestClassCorrectByAreaOffice() throws  IOException, URISyntaxException {
        //And i am on the Vehicle Information Page as an AreaOffice1User
        motUI.showVehicleInformationFor(areaOffice1User, vehicleData.getNewVehicle(tester));

        //When I change Mot Test Class
        motUI.vehicleInformation.changeMotTestClass(VehicleClass.two);

        //Then Mot Test Class will be changed
        assertThat(motUI.vehicleInformation.getMotTestClass(), is("2"));
    }

    @Test(groups = {"Regression"})
    public void vehicleEditCountryOfRegistrationCorrectByAreaOffice() throws  IOException, URISyntaxException {
        //And i am on the Vehicle Information Page as an AreaOffice1User
        motUI.showVehicleInformationFor(areaOffice1User, vehicle);

        //When I change Country of Registration
        motUI.vehicleInformation.changeCountryOfRegistration(CountryOfRegistration.Czech_Republic);

        //Then Country of Registration will be changed
        assertThat(motUI.vehicleInformation.getCountryOfRegistration(), is(CountryOfRegistration.Czech_Republic.getCountry()));
    }

    @Test(groups = {"Regression"})
    public void vehicleEditMakeModelCorrectByAreaOffice() throws  IOException, URISyntaxException {
        //And i am on the Vehicle Information Page as an VehicleExaminer
        motUI.showVehicleInformationFor(vehicleExaminer, vehicle);

        //When I change Make and Model
        motUI.vehicleInformation.changeMakeAndModel(Make.SUBARU, Model.SUBARU_IMPREZA);

        //Then Make and Model will be changed
        assertThat(motUI.vehicleInformation.getMakeAndModel(), is(Make.SUBARU.getName()+", "+ Model.SUBARU_IMPREZA.getName()));
    }

    private String getManufactureDateForVehicle(String manufactureDate) {
        return DateTimeFormat.forPattern("yyyy-MM-dd")
                .parseDateTime(manufactureDate)
                .toString("d MMMM yyyy");
    }
}
