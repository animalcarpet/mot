package uk.gov.dvsa.domain.service;

import uk.gov.dvsa.domain.api.client.MotClient;
import uk.gov.dvsa.helper.JsonHandler;

public abstract class Service {

    protected JsonHandler jsonHandler = new JsonHandler();
    protected MotClient motClient;

    protected Service(String clientUrl) {
        motClient = new MotClient(clientUrl);
    }

    protected void changeClientUrl(String url){
        motClient = new MotClient(url);
    }
}
