package uk.gov.dvsa.journey;

import uk.gov.dvsa.domain.api.response.Vehicle;
import uk.gov.dvsa.domain.model.User;
import uk.gov.dvsa.domain.navigation.PageNavigator;
import uk.gov.dvsa.ui.pages.vehicleinformation.VehicleInformationPage;
import uk.gov.dvsa.ui.pages.vehicleinformation.VehicleInformationSearchPage;

import java.io.IOException;

public class MysteryShopper {

    private PageNavigator pageNavigator;

    public MysteryShopper(PageNavigator pageNavigator) {
        this.pageNavigator = pageNavigator;
    }

    public VehicleInformationPage maskVehicle(User user, Vehicle vehicle) throws IOException {
        return pageNavigator.navigateToPage(user, VehicleInformationSearchPage.PATH, VehicleInformationSearchPage.class)
                .searchVehicleByRegistration(vehicle.getDvsaRegistration(), VehicleInformationPage.class)
                .clickMaskThisVehicleButton()
                .clickMaskThisVehicleButton()
                .clickContinueToVehicleRecordLink();
    }

    public VehicleInformationPage unMaskVehicle(User user, Vehicle vehicle) throws IOException {
        return pageNavigator.navigateToPage(user, VehicleInformationSearchPage.PATH, VehicleInformationSearchPage.class)
                .searchVehicleByRegistration(vehicle.getDvsaRegistration(), VehicleInformationPage.class)
                .clickUnmaskThisVehicleButton()
                .clickUnmaskThisVehicleButton()
                .clickContinueToVehicleRecordLink();
    }
}
