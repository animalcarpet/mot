package uk.gov.dvsa.domain.model.vehicle;


import org.apache.commons.lang3.RandomStringUtils;
import org.joda.time.DateTime;
import uk.gov.dvsa.domain.api.response.Vehicle;

public class VehicleFactory {
    public static Vehicle generateValidDetails() {

        String randomRegistrationNumber = RandomStringUtils.randomAlphabetic(7);
        return Vehicle.createVehicle(
                Colour.Blue.getName(),
                CountryOfRegistration.Great_Britain.getRegistrationId(),
                "1700",
                randomRegistrationNumber,
                randomRegistrationNumber,
                new DateTime().minusYears(1).toString(),
                FuelTypes.Diesel.getName(),
                Make.BMW.getName(),
                Model.BMW_ALPINA.getName(),
                Colour.Black.getName(),
                TransmissionType.Manual.getName(),
                RandomStringUtils.randomAlphabetic(17),
                VehicleClass.four.getId(),
                "888"
        );
    }

    public static Vehicle generateEmptyAndInvalidDetails() {
        return Vehicle.createVehicle(
                " ", " ", " ",
                " ", " ", " ",
                "Fake name", "Fake make", "Fake model",
                Colour.Black.getName(),
                "FakeFuel",
                " ",
                VehicleClass.four.getId(),
                "888"
        );
    }
}
