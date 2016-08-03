package uk.gov.dvsa.domain.service;

import com.google.common.base.Optional;
import com.jayway.restassured.response.Response;
import org.joda.time.DateTime;
import uk.gov.dvsa.domain.api.request.CreateSiteRequest;
import uk.gov.dvsa.domain.model.Site;
import uk.gov.dvsa.domain.model.User;
import uk.gov.dvsa.framework.config.webdriver.WebDriverConfigurator;

import java.io.IOException;

public class SiteService extends Service {
    private static final String CREATE_PATH = "/testsupport/vts";
    User areaOfficer = new User("areaoffice1user", "Password1");

    protected SiteService() {
        super(WebDriverConfigurator.testSupportUrl());
    }

    protected Site createSite(Optional<Integer> aeId, String siteName) throws IOException {
        String request = jsonHandler.convertToString(new CreateSiteRequest(aeId, areaOfficer, siteName));

        Response response = motClient.createSite(request, CREATE_PATH);
        return ServiceResponse.createResponse(response, Site.class);
    }

    protected Site createSiteWithStartSiteOrgLinkDate(Optional<Integer> aeId, String siteName, DateTime startDate) throws IOException {
        String request = jsonHandler.convertToString(new CreateSiteRequest(aeId, areaOfficer, siteName, startDate));

        Response response = motClient.createSite(request, CREATE_PATH);
        return ServiceResponse.createResponse(response, Site.class);
    }
}
