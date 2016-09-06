package uk.gov.dvsa.domain.service;

import com.jayway.restassured.response.Response;
import org.openqa.selenium.Cookie;
import uk.gov.dvsa.framework.config.webdriver.WebDriverConfigurator;

public class FrontendService extends Service {

    protected FrontendService() {
        super(WebDriverConfigurator.baseUrl());
    }

    protected Response downloadFile(String path, Cookie sessionCookie, Cookie tokenCookie) {
        return motClient.downloadFile(path, sessionCookie, tokenCookie);
    }
}
