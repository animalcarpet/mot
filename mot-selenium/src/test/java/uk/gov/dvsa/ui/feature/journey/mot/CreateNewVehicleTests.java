package uk.gov.dvsa.ui.feature.journey.mot;

import org.testng.annotations.Test;
import uk.gov.dvsa.domain.model.User;
import uk.gov.dvsa.domain.model.vehicle.Make;
import uk.gov.dvsa.domain.model.vehicle.Model;
import uk.gov.dvsa.ui.DslTest;
import uk.gov.dvsa.ui.pages.vehicleinformation.VehicleConfirmationPage;
import uk.gov.dvsa.ui.pages.vehicleinformation.VehicleMakePage;
import uk.gov.dvsa.ui.pages.vehicleinformation.VehicleReviewPage;
import java.io.IOException;
import java.net.URISyntaxException;
import static org.hamcrest.MatcherAssert.assertThat;
import static org.hamcrest.Matchers.containsString;
import static org.hamcrest.core.Is.is;

public class CreateNewVehicleTests extends DslTest {

    @Test(groups = {"BVT"},
            description = "Tester creates a new vehicle")
     public void createNewVehicleForMotTest() throws IOException, URISyntaxException {
        step("Given I am a MOT tester");
        User tester = motApi.user.createTester(siteData.createSite().getId());

        step("When I create a new vehicle");
        String message = motUI.normalTest.createNewVehicle(tester).verifyMotTestStarted();

        step("Then the MOT test is started with the newly created vehicle");
        assertThat("The MOT test is started",
                message,
                containsString("MOT test started"));
    }

    @Test(groups = {"Regression"},
            description = "Tester creates a new electric vehicle and starts an MOT test")
    public void createNewElectricVehicleForMotTest() throws IOException, URISyntaxException {
        step("Given I am a MOT tester");
        User tester = motApi.user.createTester(siteData.createSite().getId());

        step("When I create a new vehicle");
        VehicleConfirmationPage vehicleConfirmationPage = motUI.normalTest.createNewElectricVehicle(tester);
        String message = vehicleConfirmationPage.verifyMotTestStarted();

        step("Then the MOT test is started with the newly created electric vehicle");
        assertThat("The vehicle is created successfully and MOT test is started",
                message,
                containsString("MOT test started"));
        assertThat("Vehicle inspection sheet is displayed", vehicleConfirmationPage.verifyVehicleInspectionSheet(), is(true));
    }

    @Test(groups = {"BVT"},
            description = "Tester can update vehicle details when creating a vehicle")
    public void testerCanChangeVehicleDetailsWhenCreatingANewVehicle() throws IOException, URISyntaxException {
        step("Given I am a MOT tester creating a new vehicle");
        User tester = motApi.user.createTester(siteData.createSite().getId());
        step("And I review the vehicle details");
        VehicleReviewPage vehicleReviewPage = motUI.normalTest.reviewVehcielDetails(tester);

        step("When I update the make and model");
        String message = vehicleReviewPage.changeVehicleMake()
                .updateVehicleMake(Make.BMW)
                .updateVehicleModel(Model.BMW_ALPINA)
                .continueToVehicleConfirmationPage().verifyMotTestStarted();

        step("Then the vehicle is created successfully with the updated details");
        assertThat("The vehicle is created successfully",
                message,
                containsString("MOT test started"));
    }
}
