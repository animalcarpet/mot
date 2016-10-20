package uk.gov.dvsa.ui.feature.journey.mot;

import org.joda.time.DateTime;
import org.joda.time.format.DateTimeFormat;
import org.joda.time.format.DateTimeFormatter;
import org.testng.annotations.DataProvider;
import org.testng.annotations.Test;
import uk.gov.dvsa.domain.model.vehicle.VehicleFactory;
import uk.gov.dvsa.ui.DslTest;
import uk.gov.dvsa.ui.pages.vehicleinformation.CreateNewVehicleRecordIdentificationPage;
import uk.gov.dvsa.ui.pages.vehicleinformation.CreateNewVehicleRecordSpecificationPage;

import java.io.IOException;
import java.net.URISyntaxException;

import static org.hamcrest.MatcherAssert.assertThat;
import static org.hamcrest.core.Is.is;

public class CreateNewVehicleTests extends DslTest {

    @Test(groups = {"BVT"},
        testName = "canCreateNewValidVehicle",
        description = "Tester can start new test when creating a vehicle")
    public void canCreateNewValidVehicle() throws IOException, URISyntaxException{

        // Given that a tester creates a new DVSA vehicle
        Boolean result = motUI.normalTest.createNewDvsaVehicle(
                motApi.user.createTester(siteData.createSite().getId(), false), VehicleFactory.generateValidDetails());

        // Then a test is started for the newly created vehicle
        assertThat("Test has started", result, is(true));

    }

    @Test(groups = {"BVT"},
            testName = "canCreateNewValidVehicleWithoutVin",
            description = "Tester can start new test when creating a vehicle")
    public void canCreateNewValidVehicleWithoutVin() throws IOException, URISyntaxException{
        // Given that a tester creates a new DVSA vehicle without a VIN
        Boolean result = motUI.normalTest.createNewDvsaVehicle(
                motApi.user.createTester(siteData.createSite().getId(), false),
                VehicleFactory.generateValidDetails().setVin("").setEmptyVinReason("Missing"));

        // Then a test is started for the newly created vehicle
        assertThat("Test has started", result, is(true));

    }

    @Test(groups = {"BVT"},
            testName = "canCreateNewValidVehicleWithoutVrm",
            description = "Tester can start new test when creating a vehicle")
    public void canCreateNewValidVehicleWithoutVrm() throws IOException, URISyntaxException{

        // Given that a tester creates a new DVSA vehicle without a VRM
        Boolean result = motUI.normalTest.createNewDvsaVehicle(
                motApi.user.createTester(siteData.createSite().getId(), false),
            VehicleFactory.generateValidDetails().setRegistration("").setEmptyVrmReason("Missing"));

        // Then a test is started for the newly created vehicle
        assertThat("Test has started", result, is(true));

    }

    @Test(groups = {"BVT"},
        testName = "cannotCreateVehicleWithoutValidAddStepOneParameters",
        description = "Tester cannot start new test when creating a vehicle with invalid details",
        dataProvider = "missingAddStepOneVehicleData")
    public void cannotCreateVehicleWithoutValidAddStepOneParameters(String property, String errorMsg)
            throws IOException, URISyntaxException {

        // Given that a tester is on the page vehicle-step/add-step-one
        CreateNewVehicleRecordIdentificationPage createNewVehicleRecordIdentificationPage =
                motUI.normalTest.gotoCreateNewVehicleRecordIdentificationPage(
                        motApi.user.createTester(siteData.createSite().getId(), false));

            // And they submit the form while missing a parameter
            boolean result = motUI.normalTest.submitInvalidPageOneDetails(
                    property, errorMsg, createNewVehicleRecordIdentificationPage);

            // Then a suitable error message is displayed and they stay on the page
            assertThat("Error message (" + errorMsg + ") validated", result, is(true));

    }


    @Test(
        groups = {"BVT"},
        testName = "cannotCreateVehicleWithoutValidAddStepTwoParameters",
        description = "Tester cannot start new test when creating a vehicle with invalid details",
        dataProvider = "missingAddStepTwoVehicleData")
    public void cannotCreateVehicleWithoutValidAddStepTwoParameters(String property, String errorMsg)
            throws IOException, URISyntaxException {

        // Given that a tester is on the page vehicle-step/add-step-one
        CreateNewVehicleRecordIdentificationPage createNewVehicleRecordIdentificationPage =
                motUI.normalTest.gotoCreateNewVehicleRecordIdentificationPage(
                        motApi.user.createTester(siteData.createSite().getId(), false));

        // And they submit the form with valid details
        CreateNewVehicleRecordSpecificationPage createNewVehicleRecordSpecificationPage =
            motUI.normalTest.submitValidPageOneDetails(createNewVehicleRecordIdentificationPage);

        // And they submit the form while missing a parameter
        boolean result = motUI.normalTest.submitInvalidPageTwoDetails(
                property, errorMsg, createNewVehicleRecordSpecificationPage);

        // Then a suitable error message is displayed and they stay on the page
        assertThat("Error message (" + errorMsg + ") validated", result, is(true));

    }

    @Test(groups = {"BVT"},
        testName = "InvalidAddStepOneDateOfFirstUse",
        description = "Tester should provide valid date of first use in step one when creating a new vehicle",
        dataProvider = "invalidDates")
    public void InvalidAddStepOneDateOfFirstUse(String date, String errorMsg) throws IOException, URISyntaxException{
        // Given that a tester is on the page vehicle-step/add-step-one
        CreateNewVehicleRecordIdentificationPage createNewVehicleRecordIdentificationPage =
                motUI.normalTest.gotoCreateNewVehicleRecordIdentificationPage(
                        motApi.user.createTester(siteData.createSite().getId(), false));

        // And they submit the form while missing a parameter
        boolean result = motUI.normalTest.submitInvalidPageOneDate(
                date, errorMsg, createNewVehicleRecordIdentificationPage);

        // Then a suitable error message is displayed and they stay on the page
        assertThat("Error message (" + errorMsg + ") validated", result, is(true));
    }

    @Test(groups = {"BVT"},
            testName = "cannotCreateNewVehicleWithVinAndVinReason",
            description = "Tester cannot start new test when supplying VIN and missing VIN reason")
    public void cannotCreateNewVehicleWithVinAndVinReason() throws Exception {

        // Given that a tester is on the page vehicle-step/add-step-one
        CreateNewVehicleRecordIdentificationPage createNewVehicleRecordIdentificationPage =
                motUI.normalTest.gotoCreateNewVehicleRecordIdentificationPage(
                        motApi.user.createTester(siteData.createSite().getId(), false));

        // And they submit the form with a VIN and missing VIN reason
        String errorMsg = "remove the VIN";
        boolean result = motUI.normalTest.submitPageOneDetailsWithInappropriateReason(
                "Missing", "vin", errorMsg, createNewVehicleRecordIdentificationPage);

        // Then a suitable error message is displayed and they stay on the page
        assertThat("Error message (" + errorMsg + ") validated", result, is(true));

    }

    @Test(groups = {"BVT"},
            testName = "cannotCreateNewVehicleWithVrmAndVrmReason",
            description = "Tester cannot start new test when supplying VRM and missing VRM reason")
    public void cannotCreateNewVehicleWithVrmAndVrmReason() throws Exception {

        // Given that a tester is on the page vehicle-step/add-step-one
        CreateNewVehicleRecordIdentificationPage createNewVehicleRecordIdentificationPage =
                motUI.normalTest.gotoCreateNewVehicleRecordIdentificationPage(
                        motApi.user.createTester(siteData.createSite().getId(), false));

        // And they submit the form with a VRM and missing VRM reason
        String errorMsg = "remove the registration mark";
        boolean result = motUI.normalTest.submitPageOneDetailsWithInappropriateReason(
                "Missing", "vrm", errorMsg, createNewVehicleRecordIdentificationPage);

        // Then a suitable error message is displayed and they stay on the page
        assertThat("Error message (" + errorMsg + ") validated", result, is(true));

    }

    @DataProvider
    private Object[][] missingAddStepOneVehicleData() {
        return new Object[][] {
                {"Country", "Country of registration"},
                {"Registration", "Registration mark"},
                {"Vin","Full VIN or chassis number"},
                {"Make","Manufacturer -"},
                {"Date","enter a date"},
                {"Transmission","Transmission - "}
        };
    }

    @DataProvider
    private Object[][] missingAddStepTwoVehicleData() {
        return new Object[][] {
            {"Fuel","Fuel Type - choose a fuel type"},
            {"Model", "Model - choose a model"},
            {"Class", "Vehicle Class - choose a class"},
            {"Cylinder","Cylinder Capacity - enter a cylinder capacity"},
            {"Primary","Colour - choose a primary colour"}
            };
    }
    @DataProvider
    private Object [][] invalidDates(){
        return new Object [][] {
            {getFutureDate(1), "Approximate date of first use - must not be in the future"},
            {"2015-01-32","Approximate date of first use - enter a valid date"},
            {"2015-00-01","Approximate date of first use - enter a valid date"},
            {"00-01-01","Approximate date of first use - enter a valid date"}
            };
        }

    private String getFutureDate(int days){
        DateTime today = new DateTime();
        DateTimeFormatter fmt = DateTimeFormat.forPattern("yyyy-MM-dd");
        return fmt.print(today.plusDays(days));

    }
}
