package uk.gov.dvsa.ui.feature.journey.mot.enforcement;

import org.testng.annotations.BeforeClass;
import org.testng.annotations.BeforeMethod;
import org.testng.annotations.Test;
import uk.gov.dvsa.domain.api.response.Vehicle;
import uk.gov.dvsa.domain.model.Site;
import uk.gov.dvsa.domain.model.User;
import uk.gov.dvsa.ui.DslTest;
import uk.gov.dvsa.ui.pages.vehicleinformation.VehicleInformationPage;
import uk.gov.dvsa.ui.pages.vehicleinformation.VehicleInformationSearchPage;

import java.io.IOException;
import java.net.URISyntaxException;

import static org.hamcrest.MatcherAssert.assertThat;
import static org.hamcrest.Matchers.is;

public class EnforcementsTests extends DslTest {

    private User tester;
    private Vehicle vehicle;
    private User vehicleExaminer;
    private Site site;

    @BeforeClass(alwaysRun = true)
    public void classSetUp() throws IOException {
        this.site = siteData.createSite();
        this.tester = motApi.user.createTester(site.getId());
    }

    @BeforeMethod(alwaysRun = true)
    public void setUp() throws IOException {
        vehicle = vehicleData.getNewVehicle(tester);
        vehicleExaminer = motApi.user.createVehicleExaminer("ve", false);
    }

    @Test(testName = "MysteryShopper", groups = {"Regression", "BL-3655"},
            description = "Verifies that vehicle examiner can mask a vehicle for enforcement activities")
    public void veCanMaskVehicleSuccessfully() throws IOException, URISyntaxException {

        //Given i am on the Vehicle Information Page as an Vehicle examiner
        VehicleInformationPage vehicleInformationPage = pageNavigator
                                    .navigateToPage(vehicleExaminer, VehicleInformationSearchPage.PATH, VehicleInformationSearchPage.class)
                                    .searchVehicleByRegistration(vehicle.getDvsaRegistration(), VehicleInformationPage.class);

        //When I Mask the vehicle for enforcement activities
        vehicleInformationPage = vehicleInformationPage.clickMaskThisVehicleButton()
                                    .clickMaskThisVehicleButton()
                                    .clickContinueToVehicleRecordLink();

        //Then confirmation message should be displayed on the Vehicle Information page
        assertThat(vehicleInformationPage.isVehicleStatusBannerDisplayed(), is(true));
    }

    @Test(testName = "MysteryShopper", groups = {"Regression", "BL-3658"},
            description = "Verifies that vehicle examiner can unmask a vehicle and exclude that from enforcement activities")
    public void veCanUnmaskVehicleSuccessfully() throws IOException, URISyntaxException {

        //Given i am on the Vehicle Information Page as an Vehicle examiner with masked vehicle
        VehicleInformationPage vehicleInformationPage = pageNavigator
                                    .navigateToPage(vehicleExaminer, VehicleInformationSearchPage.PATH, VehicleInformationSearchPage.class)
                                    .searchVehicleByRegistration(vehicle.getDvsaRegistration(), VehicleInformationPage.class)
                                    .clickMaskThisVehicleButton()
                                    .clickMaskThisVehicleButton()
                                    .clickContinueToVehicleRecordLink();

        //When I Unmask the vehicle for enforcement activities
        vehicleInformationPage = vehicleInformationPage.clickUnmaskThisVehicleButton()
                                    .clickUnmaskThisVehicleButton()
                                    .clickContinueToVehicleRecordLink();

        //Then confirmation message should not be displayed on the Vehicle Information page
        assertThat(vehicleInformationPage.isVehicleStatusBannerDisplayed(), is(false));
    }

    @Test(testName = "MysteryShopper", groups = {"Regression", "BL-3655"},
            description = "Verifies that vehicle examiner can navigate back from mask a vehicle page")
    public void veCanNavigateBackFromMaskVehiclePageSuccessfully() throws IOException, URISyntaxException {

        //Given i am on the Vehicle Information Page as an Vehicle examiner
        VehicleInformationPage vehicleInformationPage = pageNavigator
                                    .navigateToPage(vehicleExaminer, VehicleInformationSearchPage.PATH, VehicleInformationSearchPage.class)
                                    .searchVehicleByRegistration(vehicle.getDvsaRegistration(), VehicleInformationPage.class);

        //When I navigate back from Mask the vehicle page
        vehicleInformationPage = vehicleInformationPage.clickMaskThisVehicleButton().clickCancelAndReturnLink();

        //Then confirmation message should not be displayed on the Vehicle Information page
        assertThat(vehicleInformationPage.isVehicleStatusBannerDisplayed(), is(false));
    }

    @Test(testName = "MysteryShopper", groups = {"Regression", "BL-3658"},
            description = "Verifies that vehicle examiner can navigate back from unmask a vehicle page")
    public void veCanNavigateBackFromUnmaskVehiclePageSuccessfully() throws IOException, URISyntaxException {

        //Given i am on the Vehicle Information Page as an Vehicle examiner with masked vehicle
        VehicleInformationPage vehicleInformationPage = pageNavigator
                                    .navigateToPage(vehicleExaminer, VehicleInformationSearchPage.PATH, VehicleInformationSearchPage.class)
                                    .searchVehicleByRegistration(vehicle.getDvsaRegistration(), VehicleInformationPage.class)
                                    .clickMaskThisVehicleButton()
                                    .clickMaskThisVehicleButton()
                                    .clickContinueToVehicleRecordLink();

        //When I navigate back from Unmask the vehicle page
        vehicleInformationPage = vehicleInformationPage.clickUnmaskThisVehicleButton().clickCancelAndReturnLink();

        //Then confirmation message should be displayed on the Vehicle Information page
        assertThat(vehicleInformationPage.isVehicleStatusBannerDisplayed(), is(true));
    }
}
