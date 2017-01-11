package uk.gov.dvsa.domain.service;

import com.jayway.restassured.response.Response;
import org.apache.commons.lang3.RandomStringUtils;
import org.apache.http.HttpStatus;
import org.joda.time.DateTime;
import uk.gov.dvsa.domain.model.*;
import uk.gov.dvsa.domain.model.vehicle.*;
import uk.gov.dvsa.framework.config.webdriver.WebDriverConfigurator;

import java.io.IOException;
import java.security.SecureRandom;
import java.util.HashMap;
import java.util.Map;
import java.util.Random;

public class VehicleService extends BaseService {
    private static final String CREATE_PATH = "/testsupport/vehicle/create";
    private static final String oneTimePassword = "123456";
    private AuthService authService = new AuthService();

    protected VehicleService() {
        super(WebDriverConfigurator.testSupportUrl());
    }

    public Vehicle createVehicle(User user) throws IOException {
        return createVehicle(VehicleClass.four, user);
    }

    public Vehicle createVehicle(VehicleClass vehicleClass, User user) throws IOException {
        Map<String, String> vehicleDataMap = new HashMap<>();
        VehicleData vehicleData = VehicleData.MercedesBenz_300D;

        vehicleDataMap.put("registrationNumber", generateCarRegistration());
        vehicleDataMap.put("vin", getRandomVin());
        vehicleDataMap.put("make", vehicleData.getId());
        vehicleDataMap.put("makeOther", "");
        vehicleDataMap.put("model", vehicleData.getModelId());
        vehicleDataMap.put("modelOther", "");
        vehicleDataMap.put("colour", Colour.Black.getId());
        vehicleDataMap.put("secondaryColour", Colour.Yellow.getId());
        vehicleDataMap.put("dateOfFirstUse", getDateMinusYears(5));
        vehicleDataMap.put("fuelType", FuelTypes.Petrol.getId());
        vehicleDataMap.put("testClass", vehicleClass.getId());
        vehicleDataMap.put("countryOfRegistration",
                CountryOfRegistration.Great_Britain.getRegistrationCode());
        vehicleDataMap.put("cylinderCapacity", Integer.toString(1700));
        vehicleDataMap.put("transmissionType", TransmissionType.Manual.getCode());
        vehicleDataMap.put("bodyType", BodyType.Hatchback.getCode());
        vehicleDataMap.put("oneTimePassword", oneTimePassword);
        vehicleDataMap.put("returnOriginalId", String.valueOf(true));

        String vehicleRequest = jsonHandler.convertToString(vehicleDataMap);

        Response response = motClient.createVehicle(
                vehicleRequest, CREATE_PATH, authService.createSessionTokenForUser(user));

        if (response.statusCode() == HttpStatus.SC_OK && response.body().path("errors") != null) {
            throw new IllegalStateException("Vehicle creation failed");
        }

        return new Vehicle(vehicleDataMap, response.body().path("data").toString());
    }

    private String generateCarRegistration() {
      return RandomStringUtils.randomAlphanumeric(7).toUpperCase();
    }

    private String getRandomVin(){
        return new DefaultVehicleDataRandomizer().nextVin();
    }

    private String getDateMinusYears(int years){
        return DateTime.now().minusYears(years).toString("YYYY-MM-dd");
    }
}
