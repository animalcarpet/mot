package uk.gov.dvsa.domain.shared.role;

import uk.gov.dvsa.domain.model.User;
import uk.gov.dvsa.domain.service.RoleService;

import java.io.IOException;

public class RoleManager {
    private static RoleService roleService = new RoleService();

    public static void addSiteRole(User userId, int siteId, TradeRoles role) throws IOException {
        if(!roleService.addSiteRole(Integer.valueOf(userId.getId()), siteId, role.getRoleName()))
            throw new IllegalStateException("Role update failed");
    }

    public static void addSystemRole(User userId, Role role) throws IOException {
        if(!roleService.addSystemRole(Integer.valueOf(userId.getId()), role.getRoleName()))
            throw new IllegalStateException("Role update failed");
    }
}
