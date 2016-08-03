package uk.gov.dvsa.domain.service;

import com.jayway.restassured.response.Response;
import org.apache.http.HttpStatus;
import uk.gov.dvsa.helper.JsonHandler;

import java.io.IOException;

public class ServiceResponse {
    private static JsonHandler jsonHandler = new JsonHandler();

    protected static  <T> T createResponse(final Response response, final Class<T> clazz) throws IOException {
        handleNon200Response(response);
        return createResponse(response, null, clazz);
    }

    protected static  <T> T createResponse(final Response response, final String path, final Class<T> clazz) throws IOException {
        handleNon200Response(response);

        return jsonHandler.hydrateObject(
                jsonHandler.convertToString(response.body().path(path == null ? "data" : "data." + path)), clazz);
    }

    protected static  <T> T hydrateResponse(final Response response, final String path, final Class<T> clazz) throws IOException {
        checkResponseSanity(response);

        return jsonHandler.hydrateObject(
                jsonHandler.convertToString(response.body().path(path)), clazz);
    }

    private static void handleNon200Response(final Response response) {
        if (response.statusCode() != HttpStatus.SC_OK) {
            throw new IllegalStateException(
                    response.body().path("errors.exception.message").toString());
        }
    }

    private static void checkResponseSanity(final Response response) {

        if (response.statusCode() == HttpStatus.SC_OK) {
            return;
        }

        switch (response.statusCode()) {
            case HttpStatus.SC_FORBIDDEN:
                throw new IllegalStateException(HttpStatus.SC_FORBIDDEN + ": " + response.body().path("message").toString());
            case HttpStatus.SC_BAD_REQUEST:
                throw new IllegalStateException(HttpStatus.SC_BAD_REQUEST + ": " + response.body().path("message").toString());

            default:
                throw new IllegalStateException("Unexpected HTTP status received from the API");
        }
    }
}
