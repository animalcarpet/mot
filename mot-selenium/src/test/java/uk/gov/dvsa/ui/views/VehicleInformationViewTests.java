package uk.gov.dvsa.ui.views;

import org.joda.time.DateTime;
import org.joda.time.format.DateTimeFormat;
import org.testng.annotations.BeforeMethod;
import org.testng.annotations.Test;

import uk.gov.dvsa.domain.api.response.Vehicle;
import uk.gov.dvsa.domain.model.Site;
import uk.gov.dvsa.domain.model.User;
import uk.gov.dvsa.domain.model.vehicle.Colours;
import uk.gov.dvsa.domain.model.vehicle.CountryOfRegistration;
import uk.gov.dvsa.domain.model.vehicle.DvlaVehicle;
import uk.gov.dvsa.domain.model.vehicle.FuelTypes;
import uk.gov.dvsa.domain.model.vehicle.Make;
import uk.gov.dvsa.domain.model.vehicle.Model;
import uk.gov.dvsa.domain.model.vehicle.VehicleClass;
import uk.gov.dvsa.ui.DslTest;
import uk.gov.dvsa.ui.pages.vehicleinformation.VehicleInformationPage;
import uk.gov.dvsa.ui.pages.vehicleinformation.VehicleInformationResultsPage;
import uk.gov.dvsa.ui.pages.vehicleinformation.VehicleInformationSearchPage;

import java.io.IOException;
import java.net.URISyntaxException;

import static org.hamcrest.MatcherAssert.assertThat;
import static org.hamcrest.Matchers.containsString;
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
                .searchVehicleByRegistration(vehicle.getDvsaRegistration(), VehicleInformationResultsPage.class)
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
    public void testerCanUpdateVehicleClassWhenStartingMotTest() throws IOException, URISyntaxException {

        //Given I am logged into MOT2 as a Tester
        //And I select a vehicle to start a MOT test
        motUI.normalTest.startTestConfirmationPage(tester, vehicle);

        //When I update the class of a vehicle
        String message = motUI.normalTest.changeClass();

        //Then the vehicle class is updated
        assertThat(message, containsString("Vehicle test class has been successfully changed"));
    }

    @Test(groups = {"Regression"})
    public void testerCanUpdateDvlaVehicleClassWhenStartingMotTest() throws IOException, URISyntaxException {

        //Given I am logged into MOT2 as a Tester
        //And I select a vehicle to start a MOT test
        motUI.normalTest.startTestConfirmationPage(tester, vehicleData.getNewDvlaVehicle(tester));

        //When I update the class of a vehicle
        String message = motUI.normalTest.changeClass();

        //Then the vehicle class is updated
        assertThat(message, containsString("Vehicle test class has been successfully changed"));
    }

    @Test(groups = {"Regression"})
    public void testerCanUpdateVehicleColourWhenStartingMotTest() throws IOException, URISyntaxException {

        //Given I am logged into MOT2 as a Tester
        //And I select a vehicle to start a MOT test
        motUI.normalTest.startTestConfirmationPage(tester, vehicle);

        //When I update the colour of a vehicle
        String message = motUI.normalTest.changeColour();

        //Then the vehicle colour is updated
        assertThat(message, containsString("Vehicle colour has been successfully changed"));
    }

    @Test(groups = {"Regression"})
    public void testerCanUpdateVehicleEngineWhenStartingMotTest() throws IOException, URISyntaxException {

        //Given I am logged into MOT2 as a Tester
        //And I select a vehicle to start a MOT test
        motUI.normalTest.startTestConfirmationPage(tester, vehicle);

        //When I update the engine of a vehicle
        String message = motUI.normalTest.changeEngine();

        //Then the vehicle engine is updated
        assertThat(message, containsString("Vehicle engine specification has been successfully changed"));
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
    public void testerCannotStartMotForVehicleWhenItIsCurrentlyUnderTest() throws IOException, URISyntaxException {

        //Given I am logged into MOT2 as a Tester
        motUI.normalTest.confirmAndStartTest(tester, vehicle);
        motUI.logout(tester);

        //When I start a MOT test for a vehicle already under test
        motUI.normalTest.startTestConfirmationPage(tester, vehicle);

        //Then I am advised the vehicle is currently under test
        assertThat(motUI.normalTest.getVehicleUnderTestBanner(), containsString("This vehicle is currently under test"));
    }
    @Test(groups = {"Regression"})
    public void testerCannotStartMotWithoutVehicleClassPopulated() throws IOException, URISyntaxException {

        //Given I am a Tester starting a MOT test
        //And I select a vehicle without a known vehicle Class
        User tester = motApi.user.createTester(siteData.createSite().getId());
        DvlaVehicle dvlaVehicle = vehicleData.getNewDvlaVehicle(tester);

        //When I confirm and start the test
        motUI.normalTest.startMotTestForDvlaVehicle(tester, dvlaVehicle);

        //Then I advised to enter the vehicle Class
        assertThat(motUI.normalTest.getNoTestClassValidation(), containsString("You must set the test class"));
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
        assertThat(motUI.vehicleInformation.getMakeAndModel(), is(Make.SUBARU.getName() + ", " + Model.SUBARU_IMPREZA.getName()));
    }

    @Test(groups = {"Regression"})
    public void vehicleEditFirstDateUsedByAreaOffice() throws  IOException, URISyntaxException {
        //Given I am on the Vehicle Information Page as an AreaOffice1User
        motUI.showVehicleInformationFor(areaOffice1User, vehicle);

        //When I change date of first use
        DateTime newDate = new DateTime(2010, 4, 6, 0, 0);
        motUI.vehicleInformation.changeFirstDateUsed(newDate);

        //Then the new value is shown
        assertThat(motUI.vehicleInformation.getFirstDateUsed(), is(newDate.toString("d MMMM yyyy")));
    }

    @Test(groups = {"Regression"})
    public void vehicleEditColourCorrectByAreaOffice() throws  IOException, URISyntaxException {
        //Given I am on the Vehicle Information Page as an AreaOffice
        motUI.showVehicleInformationFor(areaOffice1User, vehicle);

        //When I change primary and secondary colour
        motUI.vehicleInformation.changeColour(Colours.Black, Colours.White);

        //Then colours will be changed
        assertThat(motUI.vehicleInformation.getColour(), is(Colours.Black.getName() + " and " + Colours.White.getName()));
    }

    private String getManufactureDateForVehicle(String manufactureDate) {
        return DateTimeFormat.forPattern("yyyy-MM-dd")
                .parseDateTime(manufactureDate)
                .toString("d MMMM yyyy");
    }
}
