package uk.gov.dvsa.domain.service;

import com.jayway.restassured.response.Response;
import uk.gov.dvsa.domain.api.client.MotClient;
import uk.gov.dvsa.framework.config.webdriver.WebDriverConfigurator;
import uk.gov.dvsa.domain.api.response.Vehicle;

import java.io.IOException;
import java.util.HashMap;
import java.util.Map;

public class VehicleTestingAdviceService extends Service {
    final public String CREATE_VEHICLE_TESTIMG_ADVICE_PATH = "/testsupport/vehicle-testing-advice";

    protected VehicleTestingAdviceService() {
        super(WebDriverConfigurator.testSupportUrl());
    }

    public void create(Vehicle vehicle) throws IOException{
        Map<String, String> dvlaVehicleDataMap = new HashMap<>();
        dvlaVehicleDataMap.put("vehicle_id", vehicle.getId());
        dvlaVehicleDataMap.put("model_id", vehicle.getModel().getId().toString());

        String vehicleRequest = jsonHandler.convertToString(dvlaVehicleDataMap);

        MotClient motClient = new MotClient(WebDriverConfigurator.testSupportUrl());
        Response response = motClient.createTestingAdvice(
                vehicleRequest, CREATE_VEHICLE_TESTIMG_ADVICE_PATH);
    }
}
