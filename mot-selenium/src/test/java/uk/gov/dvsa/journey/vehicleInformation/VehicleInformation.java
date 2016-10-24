package uk.gov.dvsa.journey.vehicleInformation;

import uk.gov.dvsa.domain.api.response.Colour;
import uk.gov.dvsa.domain.model.vehicle.*;
import uk.gov.dvsa.ui.pages.vehicleinformation.VehicleInformationPage;

public class VehicleInformation {
    private VehicleInformationPage vehicleInformationPage;

    public void setVehicleInformationPage(VehicleInformationPage vehicleInformationPage) {
        this.vehicleInformationPage = vehicleInformationPage;
    }

    public VehicleInformationPage changeEngine(FuelTypes fuelType, String cylinderCapacity)
    {
        return vehicleInformationPage
                .clickChangeEngineLink()
                .selectFuelType(fuelType)
                .fillCylinderCapacity(cylinderCapacity)
                .submit();
    }

    public String getEngine()
    {
        return vehicleInformationPage.getEngine();
    }

    public VehicleInformationPage changeMotTestClass(VehicleClass vehicleClass) {
        return vehicleInformationPage
                .clickChangeMotTestClassLink()
                .chooseClass(vehicleClass)
                .submit();
    }

    public String getMotTestClass()
    {
        return vehicleInformationPage.getMotTestClass();
    }

    public String getCountryOfRegistration() {
        return vehicleInformationPage.getCountryOfRegistration();
    }

    public VehicleInformationPage changeCountryOfRegistration(CountryOfRegistration countryOfRegistration) {
        return vehicleInformationPage
                .clickChangeCountryOfRegistrationLink()
                .selectCountryOfRegistration(countryOfRegistration)
                .submit();
    }

    public VehicleInformationPage changeMakeAndModel(Make make, Model model) {
        return vehicleInformationPage
                .clickChangeMakeAndModelLink()
                .selectMake(make)
                .submit()
                .selectModel(model)
                .submit()
                .submit();
    }

    public String getMakeAndModel() {
        return vehicleInformationPage.getMakeModel();
    }

    public VehicleInformationPage changeColour(Colours colour, Colours secondaryColour) {
        return vehicleInformationPage
                .clickChangeColourLink()
                .selectColour(colour)
                .selectSecondaryColour(secondaryColour)
                .submit();
    }

    public String getColour() { return vehicleInformationPage.getColour(); }
}
