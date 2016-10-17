package uk.gov.dvsa.data;

import org.apache.commons.lang3.RandomStringUtils;
import org.joda.time.DateTime;
import uk.gov.dvsa.domain.api.response.Vehicle;
import uk.gov.dvsa.domain.model.User;
import uk.gov.dvsa.domain.model.vehicle.*;
import uk.gov.dvsa.domain.service.VehicleService;

import java.io.IOException;

public class VehicleData extends VehicleService {

    public VehicleData() {}

    private static final String DEFAULT_PIN = "123456";
    private static final String DEFAULT_TRANSMISSION_TYPE_ID = "1";
    private static final String DEFAULT_CC = "888";

    protected Vehicle createVehicle(User user) throws IOException {
        return createVehicle(user, VehicleClass.four);
    }

    protected Vehicle createVehicle(User user, VehicleClass vehicleClass) throws IOException {
        return createVehicle(
                user,
                vehicleClass,
                generateCarRegistration()
        );
    }

    protected Vehicle createVehicle(User user, VehicleClass vehicleClass, String registration) throws IOException {
        String makeId, modelId;
        if (vehicleClass == VehicleClass.one || vehicleClass == VehicleClass.two) {
            makeId = Integer.toString(VehicleDetails.KAWASAKI_ZRX1100.getMakeId());
            modelId = Integer.toString(VehicleDetails.KAWASAKI_ZRX1100.getModelId());
        } else {
            makeId = Integer.toString(VehicleDetails.MERCEDES_300_D.getMakeId());
            modelId = Integer.toString(VehicleDetails.MERCEDES_300_D.getModelId());
        }
        return createVehicle(
                user,
                DEFAULT_PIN,
                Colour.Blue.getId().toString(),
                CountryOfRegistration.Great_Britain.getRegistrationId(),
                DEFAULT_CC,
                getDateMinusYears(5),
                Integer.toString(FuelTypes.Petrol.getId()),
                makeId,
                modelId,
                registration,
                Colour.Grey.getId().toString(),
                getRandomVin(),
                vehicleClass.getCode(),
                DEFAULT_TRANSMISSION_TYPE_ID
        );
    }

    public Vehicle getNewVehicle(User user) throws IOException {
        return createVehicle(user);
    }



    public Vehicle getNewVehicle(User user, VehicleClass vehicleClass) throws IOException {
        return createVehicle(user, vehicleClass);
    }

    public Vehicle getNewVehicle(User user, String registration) throws IOException {
        return createVehicle(user, VehicleClass.four, registration);
    }

    public DvlaVehicle getNewDvlaVehicle(User user) throws IOException {
        return createDvlaVehicle(
                user,
                generateCarRegistration(),
                getRandomVin(),
                "1889A",
                "01163"
        );
    }

    private String generateCarRegistration() {
        return RandomStringUtils.randomAlphanumeric(7).toUpperCase();
    }

    private String getRandomVin() {
        return new DefaultVehicleDataRandomizer().nextVin();
    }

    private String getDateMinusYears(int years) {
        return DateTime.now().minusYears(years).toString("YYYY-MM-dd");
    }
}
