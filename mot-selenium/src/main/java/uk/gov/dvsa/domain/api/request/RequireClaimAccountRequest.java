package uk.gov.dvsa.domain.api.request;

import com.fasterxml.jackson.annotation.JsonAutoDetect;
import com.fasterxml.jackson.annotation.JsonInclude;

@JsonInclude(JsonInclude.Include.NON_NULL)
@JsonAutoDetect(fieldVisibility = JsonAutoDetect.Visibility.ANY)
public class RequireClaimAccountRequest {

    private String personId;

    public RequireClaimAccountRequest(String personId) {
        this.personId = personId;
    }
}
