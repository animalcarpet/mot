package uk.gov.dvsa.domain.service;

import com.jayway.restassured.response.Response;
import uk.gov.dvsa.domain.api.request.CreateAnnualAssessmentCertificateRequest;
import uk.gov.dvsa.domain.model.AnnualAssessmentCertificate;
import uk.gov.dvsa.domain.model.User;
import uk.gov.dvsa.framework.config.webdriver.WebDriverConfigurator;

import java.io.IOException;

public class AnnualAssessmentCertificateService extends Service {
    private static final String CREATE_CERTIFICATE_PATH = "/testsupport/annual-assessment-certificate";
    protected static final String GROUP_A = "A";
    protected static final String GROUP_B = "B";
    private AuthService authService = new AuthService();

    protected AnnualAssessmentCertificateService() {
        super(WebDriverConfigurator.testSupportUrl());
    }

    protected AnnualAssessmentCertificate createAnnualAssessmentCertificate(
        User user,
        String vehicleClassGroupCode,
        String certificateNumber,
        String examDate,
        String score
    ) throws IOException {
        String request =
            jsonHandler.convertToString(new CreateAnnualAssessmentCertificateRequest(
                user.getId(),
                user.getUsername(),
                user.getPassword(),
                vehicleClassGroupCode,
                certificateNumber,
                examDate,
                score)
            );

        String token = authService.createSessionTokenForUser(user);
        Response response = motClient.post(request, CREATE_CERTIFICATE_PATH, token);

        return ServiceResponse.createResponse(response, AnnualAssessmentCertificate.class);
    }
}
