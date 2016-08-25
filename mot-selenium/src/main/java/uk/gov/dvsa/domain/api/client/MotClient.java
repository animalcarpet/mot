package uk.gov.dvsa.domain.api.client;

import com.jayway.restassured.RestAssured;
import com.jayway.restassured.response.Response;

import static com.jayway.restassured.RestAssured.with;

public class MotClient {

    private String endpointUrl;

    public MotClient(String endpointUrl) {
        RestAssured.useRelaxedHTTPSValidation();
        this.endpointUrl = endpointUrl;
    }

    public Response createSession(String request, String path) {
        return postRequestWithoutToken(request, path);
    }

    public Response createAe(String request, String path, String token) {
        return postRequest(request, path, token);
    }

    public Response createQualificationCertificate(String request, String path, String token) {
        return postRequest(request, path, token);
    }

    public Response post(String request, String path, String token) {
        return postRequest(request, path, token);
    }

    public Response postWithoutToken(String request, String path) {
        return postRequestWithoutToken(request, path);
    }

    public Response createUser(String request, String path, String token) {
        return postRequest(request, path, token);
    }

    public Response createUser(String request, String path) {
        return postRequestWithoutToken(request, path);
    }

    public Response createVehicle(String request, String path, String token) {
        return postRequest(request, path, token);
    }

    public Response createSite(String request, String path) {
        return postRequestWithoutToken(request, path);
    }

    public Response broadcastSpecialNotice(String request, String path) {
        return postRequestWithoutToken(request, path);
    }

    public Response addRoleToUser(String resourceUrl) {
        return with()
                .header("Content-Type", "application/json")
                .put(endpointUrl + resourceUrl);
    }

    public Response createTwoFactorDetails(String path) {
        return postRequestWithoutToken("", path);
    }

    public Response getFeature(String path)
    {
        return getWithoutToken(path);
    }

    private Response postRequestWithoutToken(String request, String path) {
        return with()
                .header("Content-Type", "application/json")
                .body(request)
                .post(endpointUrl + path);
    }

    private Response postRequest(String request, String path, String token) {
        return with()
                .header("Authorization", "Bearer " + token)
                .header("Content-Type", "application/json")
                .body(request)
                .post(endpointUrl + path);
    }

    private Response getWithoutToken(String path) {
        return with()
                .header("Content-Type", "application/json")
                .header("Accept", "application/json")
                .get(endpointUrl + path);
    }
}
