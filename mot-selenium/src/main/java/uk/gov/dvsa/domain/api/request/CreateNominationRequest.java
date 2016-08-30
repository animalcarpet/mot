package uk.gov.dvsa.domain.api.request;

import com.fasterxml.jackson.annotation.JsonAutoDetect;
import com.fasterxml.jackson.annotation.JsonInclude;

import uk.gov.dvsa.domain.shared.role.OrganisationBusinessRoleCodes;

@JsonAutoDetect(fieldVisibility = JsonAutoDetect.Visibility.ANY)
@JsonInclude(JsonInclude.Include.NON_NULL)
public class CreateNominationRequest {

    private int userId;
    private String roleCode;
    private int roleId;
    private int siteId;
    private int orgId;

    public CreateNominationRequest(int userId, String roleName, int siteId) {
        this.userId = userId;
        this.roleCode = roleName;
        this.siteId = siteId;
    }

    public CreateNominationRequest(int userId, int orgId) {
        this.userId = userId;
        this.orgId = orgId;
    }

    public CreateNominationRequest(int userId, int orgId, int roleId) {
        this.userId = userId;
        this.orgId = orgId;
        this.roleId = roleId;
    }

    public CreateNominationRequest(int userId, int orgId, OrganisationBusinessRoleCodes roleCode) {
        this.userId = userId;
        this.orgId = orgId;
        this.roleCode = roleCode.toString();
    }
}
