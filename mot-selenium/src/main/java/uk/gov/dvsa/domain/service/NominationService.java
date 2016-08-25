package uk.gov.dvsa.domain.service;

import uk.gov.dvsa.domain.api.request.CreateNominationRequest;
import uk.gov.dvsa.domain.model.User;
import uk.gov.dvsa.domain.shared.role.Role;
import uk.gov.dvsa.framework.config.webdriver.WebDriverConfigurator;

import java.io.IOException;

public class NominationService extends Service {

    private static final String SITE_NOMINATION_ENDPOINT = "/testsupport/site/role/nomination";
    private static final String ORG_NOMINATION_ENDPOINT = "/testsupport/org/role/nomination";

    public NominationService() {
        super(WebDriverConfigurator.testSupportUrl());
    }

    public void nominateSiteRole(User user, int siteId, Role role) throws IOException {
        String request = jsonHandler.convertToString(
            new CreateNominationRequest(Integer.valueOf(user.getId()), role.getRoleName(), siteId));

        motClient.postWithoutToken(request, SITE_NOMINATION_ENDPOINT);
    }

    public void nominateOrganisationRole(User user, int organisationId) throws IOException {
        String request = jsonHandler.convertToString(
                new CreateNominationRequest(Integer.valueOf(user.getId()), organisationId));

        motClient.postWithoutToken(request, ORG_NOMINATION_ENDPOINT);
    }

    public void nominateOrganisationRoleWithRoleId(User user, int organisationId, int roleId) throws IOException{
        String request = jsonHandler.convertToString(
                new CreateNominationRequest(Integer.valueOf(user.getId()), organisationId, roleId));

        motClient.postWithoutToken(request, ORG_NOMINATION_ENDPOINT);
    }


}
