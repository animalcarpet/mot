package uk.gov.dvsa.domain.service;

import com.jayway.restassured.response.Response;
import org.joda.time.DateTime;
import org.json.JSONObject;
import uk.gov.dvsa.domain.api.request.CreateMotTestRequest;
import uk.gov.dvsa.domain.api.request.MotTestData;
import uk.gov.dvsa.domain.model.User;
import uk.gov.dvsa.domain.model.mot.MotTest;
import uk.gov.dvsa.domain.model.mot.TestOutcome;
import uk.gov.dvsa.domain.model.vehicle.Vehicle;
import uk.gov.dvsa.framework.config.webdriver.WebDriverConfigurator;
import uk.gov.dvsa.helper.ReasonForRejection;

import java.io.IOException;
import java.util.List;


public class MotTestService extends Service {
    private static final String CREATE_MOT_TEST_PATH = "/testsupport/mottest";
    private static final String CREATE_100_MOT_TESTS_PATH = "/testsupport/onehundredmottests";
    private AuthService authService = new AuthService();

    protected MotTestService() {
        super(WebDriverConfigurator.testSupportUrl());
    }

    protected MotTest createMotTest(User requestor, int siteId,
                                 Vehicle vehicle, TestOutcome outcome,
                                 int mileage, DateTime issuedDate) throws IOException {

        MotTestData testData = new MotTestData(outcome, mileage, issuedDate);
        return createMotTest(requestor, vehicle, siteId, testData);
    }


    protected MotTest createMotTestWithRfr(User requestor, int siteId,
                                 Vehicle vehicle, TestOutcome outcome,
                                 int mileage, DateTime issuedDate, List<ReasonForRejection> rfrs) throws IOException {

        MotTestData testData = new MotTestData(outcome, mileage, issuedDate, issuedDate, issuedDate, issuedDate, rfrs);
        return createMotTest(requestor, vehicle, siteId, testData);
    }

    protected MotTest createMotTest(User requestor, Vehicle vehicle, int vtsId, MotTestData testData) throws IOException {
        String request =
                jsonHandler.convertToString(new CreateMotTestRequest(requestor, vehicle, vtsId, testData));

        String token = authService.createSessionTokenForUser(requestor);
        Response response = motClient.post(request, CREATE_MOT_TEST_PATH, token);

        return ServiceResponse.createResponse(response, MotTest.class);
    }

    protected void createOneHundredMotTests(User user) throws IOException {
        JSONObject requestJSON = createHTTPRequestJSON(user);
        String token = authService.createSessionTokenForUser(user);
        Response response = motClient.post(requestJSON, CREATE_100_MOT_TESTS_PATH, token);

        ServiceResponse.checkResponseSanity(response);
    }

    private JSONObject createHTTPRequestJSON(User user) {
        JSONObject requestJSON = new JSONObject();
        requestJSON.put("userId", user.getId());
        return requestJSON;
    }
}
