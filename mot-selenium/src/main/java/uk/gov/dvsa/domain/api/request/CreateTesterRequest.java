package uk.gov.dvsa.domain.api.request;

import com.fasterxml.jackson.annotation.JsonAutoDetect;
import com.fasterxml.jackson.annotation.JsonInclude;

import uk.gov.dvsa.domain.model.mot.TestGroup;

import java.util.List;

@JsonInclude(JsonInclude.Include.NON_NULL)
@JsonAutoDetect(fieldVisibility = JsonAutoDetect.Visibility.ANY)
public class CreateTesterRequest extends UserRequest{

    private List<Integer> siteIds;
    private String testGroup;

    public CreateTesterRequest(List<Integer> siteIdList, boolean accountClaimRequired, TestGroup testGroup) {
        super(accountClaimRequired);
        this.siteIds = siteIdList;
        this.testGroup = testGroup == null ? null : String.valueOf(testGroup.group);
    }
}
