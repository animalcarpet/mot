package uk.gov.dvsa.domain.service;

import com.jayway.restassured.response.Response;
import uk.gov.dvsa.domain.model.TwoFactorDetails;
import uk.gov.dvsa.framework.config.Configurator;

import java.io.IOException;

public class TwoFactorService extends Service {
    private static final String TWO_FACTOR_PATH = "/testsupport/two-factor-auth/card";

    public TwoFactorService() throws IOException {
        super(Configurator.testSupportUrl());
    }

    public TwoFactorDetails createTwoFactorDetails() throws IOException {
        Response response = motClient.createTwoFactorDetails(TWO_FACTOR_PATH);
        return ServiceResponse.createResponse(response, TwoFactorDetails.class);
    }
}
