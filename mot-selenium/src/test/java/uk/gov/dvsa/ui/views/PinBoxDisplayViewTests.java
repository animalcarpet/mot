package uk.gov.dvsa.ui.views;

import org.testng.annotations.BeforeMethod;
import org.testng.annotations.Test;
import uk.gov.dvsa.domain.model.AeDetails;
import uk.gov.dvsa.domain.model.Site;
import uk.gov.dvsa.domain.model.User;
import uk.gov.dvsa.domain.api.response.Vehicle;
import uk.gov.dvsa.ui.DslTest;
import uk.gov.dvsa.ui.pages.mot.VehicleDetailsChangedPage;
import uk.gov.dvsa.ui.pages.vehicleinformation.CreateNewVehicleRecordConfirmPage;

import java.io.IOException;
import java.net.URISyntaxException;

import static org.hamcrest.MatcherAssert.assertThat;
import static org.hamcrest.core.Is.is;

public class PinBoxDisplayViewTests extends DslTest {

    private Site site;
    private AeDetails aeDetails;
    private User tester;
    private Vehicle vehicle;

    @BeforeMethod(alwaysRun = true)
    private void setupTestData() throws IOException {
        aeDetails = aeData.createAeWithDefaultValues();
        site = siteData.createNewSite(aeDetails.getId(), "TestSite");
        tester = motApi.user.createTester(site.getId());
        vehicle = vehicleData.getNewVehicle(tester);
    }

    @Test(groups = {"BVT", "Regression"})
    public void pinBoxDisplayedForNonTwoFactorUserWhenCreatingNewVehicle() throws IOException, URISyntaxException {
        //GIVEN I am logged in as a tester
        User non2faTester = motApi.user.createTester(site.getId());

        //WHEN I attempt to create a new vehicle record
        CreateNewVehicleRecordConfirmPage createNewVehicleRecordConfirmPage = motUI.normalTest.createNewVehicleRecord(non2faTester, vehicle);

        //THEN I should see the PIN Box on test summary page
        assertThat(createNewVehicleRecordConfirmPage.isPinBoxDisplayed(), is(true));
    }

    @Test (testName = "2fa", groups = {"BVT", "Regression"})
    public void pinBoxNotDisplayedForTwoFactorUserWhenCreatingNewVehicle() throws IOException, URISyntaxException{
        //GIVEN I am logged in as a tester authenticated by 2fa card
        User twoFactorTester = motApi.user.createTester(site.getId());
        motUI.authentication.registerAndSignInTwoFactorUser(twoFactorTester);

        //WHEN I attempt to create a new vehicle record
        CreateNewVehicleRecordConfirmPage createNewVehicleRecordConfirmPage = motUI.normalTest.createNewVehicleRecord(twoFactorTester, vehicle);

        //THEN I should not see the PIN Box on test summary page
        assertThat(createNewVehicleRecordConfirmPage.isPinBoxDisplayed(), is(false));
    }

    @Test(testName = "2fa", groups = {"BVT", "Regression"})
    public void displayPinBoxAtChangeVehicleDetailsSummary() throws IOException, URISyntaxException {

        User tester = motApi.user.createTester(site.getId());

        vehicle = vehicleData.getNewVehicle(tester);

        //Given I change the vehicle details
        VehicleDetailsChangedPage page = motUI.normalTest.changeVehicleDetails(tester, vehicle);

        //When I submit the change

        //Then I should be presented with the declaration statement
        assertThat(page.isPinBoxDisplayed(), is(true));
    }

    @Test(testName = "2fa", groups = {"BVT", "Regression"})
    public void doNotDisplayPinBoxFor2faTesterAtChangeVehicleDetailsSummary() throws IOException, URISyntaxException {

        User twoFactorTester = motApi.user.createTester(site.getId());
        motUI.authentication.registerAndSignInTwoFactorUser(twoFactorTester);

        vehicle = vehicleData.getNewVehicle(tester);

        //Given I change the vehicle details
        VehicleDetailsChangedPage page = motUI.normalTest.changeVehicleDetails(twoFactorTester, vehicle);

        //When I submit the change

        //Then I should be presented with the declaration statement
        assertThat(page.isPinBoxDisplayed(), is(false));
    }
}
