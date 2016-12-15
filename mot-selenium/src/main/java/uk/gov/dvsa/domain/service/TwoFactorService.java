package uk.gov.dvsa.domain.service;

import com.jayway.restassured.response.Response;

import org.apache.http.HttpStatus;

import uk.gov.dvsa.domain.model.TwoFactorDetails;
import uk.gov.dvsa.domain.model.User;
import uk.gov.dvsa.framework.config.Configurator;

import java.io.IOException;
import java.util.HashMap;
import java.util.Map;

public class TwoFactorService extends Service {
    private static final String TWO_FACTOR_PATH = "/testsupport/two-factor-auth/card";
    private static final String ACTIVATE_PATH = "/authorisation/person/security-card";

    public TwoFactorService() throws IOException {
        super(Configurator.testSupportUrl());
    }

    public TwoFactorDetails createTwoFactorDetails() throws IOException {
        Response response = motClient.createTwoFactorDetails(TWO_FACTOR_PATH);
        return ServiceResponse.createResponse(response, TwoFactorDetails.class);
    }

    public void activateCardForUser(User user, TwoFactorDetails twoFactorDetails) throws IOException {
        changeClientUrl(Configurator.authServiceUrl());

        Map<String, String> requestMap = new HashMap<>();
        requestMap.put("serialNumber", twoFactorDetails.serialNumber());
        requestMap.put("pin", twoFactorDetails.pin());

        Response response =  motClient.activate2faCard(jsonHandler.convertToString(requestMap), ACTIVATE_PATH,
            new AuthService().createSessionTokenForUser(user));

        if(response.getStatusCode() != HttpStatus.SC_OK) {
            throw new IllegalStateException(
                    String.format("Code: %s, body: %s", response.getStatusCode(), response.getBody().prettyPrint())
            );
        }
    }
}
