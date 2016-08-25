package uk.gov.dvsa.domain.service;

import com.jayway.restassured.response.Response;
import uk.gov.dvsa.framework.config.Configurator;

import java.io.IOException;

public class RoleService extends Service {

    private static final String PERSON_ROLE_RESOURCE_URL = "/testsupport/person/%d/role/%s";
    private static final String SITE_ROLE_RESOURCE_URL = "/testsupport/person/%d/site/%d/site-role/%s";

    public RoleService() {
        super(Configurator.testSupportUrl());
    }

    public boolean addSiteRole(int userId, int siteId, String roleName) throws IOException {
        return addRole(SITE_ROLE_RESOURCE_URL, userId, siteId, roleName);
    }

    public boolean addSystemRole(int userId, String roleName) throws IOException{
        return addRole(PERSON_ROLE_RESOURCE_URL, userId, 0, roleName);
    }

    private boolean addRole(String endpoint, int userId, int siteId, String roleName) throws IOException {
        String resourceUrl;

        if (siteId > 0) {
            resourceUrl = String.format(endpoint, userId, siteId, roleName);
        } else {
            resourceUrl = String.format(endpoint, userId, roleName);
        }

        Response response = motClient.addRoleToUser(resourceUrl);
        return ServiceResponse.createResponse(response, "success", boolean.class);
    }
}
