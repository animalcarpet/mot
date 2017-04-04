package uk.gov.dvsa.ui.feature.journey.mot;

import org.testng.annotations.BeforeMethod;
import org.testng.annotations.Test;
import uk.gov.dvsa.domain.api.response.Vehicle;
import uk.gov.dvsa.domain.model.Site;
import uk.gov.dvsa.domain.model.User;
import uk.gov.dvsa.domain.model.vehicle.DvlaVehicle;
import uk.gov.dvsa.ui.DslTest;

import java.io.IOException;
import java.net.URISyntaxException;

import static org.hamcrest.MatcherAssert.assertThat;
import static org.hamcrest.Matchers.containsString;


public class UpdateVehicleBeforeMotTests extends DslTest {

    private User tester;

    @BeforeMethod(alwaysRun = true)
    public void setUp() throws IOException {
        Site site = siteData.createSite();
        tester = motApi.user.createTester(site.getId());
    }

    @Test(groups = {"Regression"},
            description = "Tester can update the vehicle class when starting a MOT test")
    public void testerCanUpdateVehicleClassWhenStartingMotTest() throws IOException, URISyntaxException {

        step("Given I am logged into MOT2 as a Tester");
        step("And I select a vehicle to start a MOT test");
        motUI.normalTest.startTestConfirmationPage(tester, vehicleData.getNewVehicle(tester));

        step("When I update the class of a vehicle");
        String message = motUI.normalTest.changeClass();

        step("Then the vehicle class is updated");
        assertThat(message, containsString("Vehicle test class has been successfully changed"));
    }

    @Test(groups = {"Regression"},
            description = "Tester can update a DVLA vehicle class when starting a MOT test")
    public void testerCanUpdateDvlaVehicleClassWhenStartingMotTest() throws IOException, URISyntaxException {

        step("Given I am logged into MOT2 as a Tester");
        step("And I select a vehicle to start a MOT test");
        motUI.normalTest.startTestConfirmationPage(tester, vehicleData.getNewDvlaVehicle(tester));

        step("When I update the class of a DVLA vehicle");
        String message = motUI.normalTest.changeClass();

        step("Then the vehicle class is updated");
        assertThat(message, containsString("Vehicle test class has been successfully changed"));
    }

    @Test(groups = {"BVT"},
            description = "Tester can update the vehicle colour when starting a MOT test")
    public void testerCanUpdateVehicleColourWhenStartingMotTest() throws IOException, URISyntaxException {

        step("Given I am logged into MOT2 as a Tester");
        step("And I select a vehicle to start a MOT test");
        motUI.normalTest.startTestConfirmationPage(tester, vehicleData.getNewVehicle(tester));

        step("When I update the colour of a vehicle");
        String message = motUI.normalTest.changeColour();

        step("Then the vehicle colour is updated");
        assertThat(message, containsString("Vehicle colour has been successfully changed"));
    }

    @Test(groups = {"Regression"},
            description = "Tester can update the engine of a vehicle at the start of a mot test")
    public void testerCanUpdateVehicleEngineWhenStartingMotTest() throws IOException, URISyntaxException {

        step("Given I am logged into MOT2 as a Tester");
        step("And I select a vehicle to start a MOT test");
        motUI.normalTest.startTestConfirmationPage(tester, vehicleData.getNewVehicle(tester));

        step("When I update the engine of a vehicle");
        String message = motUI.normalTest.changeEngine();

        step("Then the vehicle engine is updated");
        assertThat(message, containsString("Vehicle engine specification has been successfully changed"));
    }

    @Test(groups = {"Regression"},
            description = "Tester can update the make and model of a vehicle at the start of a mot test")
    public void testerCanUpdateVehicleMakeAndModelWhenStartingMotTest() throws IOException, URISyntaxException {

        step("Given I am logged into MOT2 as a Tester");
        step("And I select a vehicle to start a MOT test");
        motUI.normalTest.startTestConfirmationPage(tester, vehicleData.getNewVehicle(tester));

        step("When I update the make and model of a vehicle");
        String message = motUI.normalTest.changeMakeAndModel();

        step("Then the vehicle make and model is updated");
        assertThat(message, containsString("Vehicle make and model has been successfully changed"));
    }

        @Test(groups = {"Regression"},
            description = "Tester can update the make and model of a vehicle to Other at the start of a mot test")
    public void testerCanUpdateVehicleMakeAndModelWithOtherWhenStartingMotTest() throws IOException, URISyntaxException {

        step("Given I am logged into MOT2 as a Tester");
        step("And I select a vehicle to start a MOT test");
        motUI.normalTest.startTestConfirmationPage(tester, vehicleData.getNewVehicle(tester));

        step("When I update the make and model of a vehicle to Other");
        String message = motUI.normalTest.changeMakeAndModelWithOther();

        step("Then the vehicle make and model is updated");
        assertThat(message, containsString("Vehicle make and model has been successfully changed"));
    }

    @Test(groups = {"Regression"},
            description = "Tester can update the make and model of a DVLA vehicle at the start of a mot test")
    public void testerCanUpdateDvlaVehicleMakeAndModelWhenStartingMotTest() throws IOException, URISyntaxException {

        step("Given I am logged into MOT2 as a Tester");
        step("And I select a DVLA vehicle to start a MOT test");
        motUI.normalTest.startTestConfirmationPage(tester, vehicleData.getNewDvlaVehicle(tester));

        step("When I update the make and model of a DVLA vehicle");
        String message = motUI.normalTest.changeMakeAndModel();

        step("Then the vehicle make and model is updated");
        assertThat(message, containsString("Vehicle make and model has been successfully changed"));
    }

    @Test(groups = {"BVT"},
            description = "Tester cannot start a MOT test for a vehicle that is currently under test")
    public void testerCannotStartMotForVehicleWhenItIsCurrentlyUnderTest() throws IOException, URISyntaxException {

        step("Given I am logged into MOT2 as a Tester");
        Vehicle motTestVehicle = vehicleData.getNewVehicle(tester);
        motUI.normalTest.confirmAndStartTest(tester, motTestVehicle);
        motUI.logout(tester);

        step("When I start a MOT test for a vehicle already under test");
        motUI.normalTest.startTestConfirmationPage(tester, motTestVehicle);

        step("Then I am advised the vehicle is currently under test");
        assertThat(motUI.normalTest.getVehicleUnderTestBanner(), containsString("This vehicle is currently under test"));
    }

    @Test(groups = {"BVT"},
            description = "Tester cannot start a MOT test without the vehicle class populated")
    public void testerCannotStartMotWithoutVehicleClassPopulated() throws IOException, URISyntaxException {

        step("Given I am logged into MOT2 as a Tester");
        step("And I select a vehicle without a known vehicle Class");
        DvlaVehicle dvlaVehicle = vehicleData.getNewDvlaVehicle(tester);

        step("When I confirm and start the test");
        motUI.normalTest.startMotTestForDvlaVehicle(tester, dvlaVehicle);

        step("Then I advised to enter the vehicle Class");
        assertThat(motUI.normalTest.getNoTestClassValidation(), containsString("You must set the test class"));
    }

    @Test(groups = {"Regression"},
            description = "Tester can update the make and model of a vehicle at the start of a mot test")
    public void testerCanUpdateVehicleCountryOfRegistrationWhenStartingMotTest() throws IOException, URISyntaxException {

        step("Given I am logged into MOT2 as a Tester");
        step("And I select a vehicle to start a MOT test");
        motUI.normalTest.startTestConfirmationPage(tester, vehicleData.getNewVehicle(tester));

        step("When I update the country of registration of a vehicle");
        String message = motUI.normalTest.changeCountryOfRegistration();

        step("Then the vehicle country of registration is updated");
        assertThat(message, containsString("Country of registration has been successfully changed"));
    }
}
