package uk.gov.dvsa.domain.api.request;

import com.fasterxml.jackson.annotation.JsonAutoDetect;
import com.fasterxml.jackson.annotation.JsonInclude;

import uk.gov.dvsa.domain.model.mot.TestGroup;
import uk.gov.dvsa.domain.service.QualificationDetailsService;
import uk.gov.dvsa.domain.shared.qualifications.TesterQualifications;

import java.util.HashMap;
import java.util.List;
import java.util.Map;

@JsonInclude(JsonInclude.Include.NON_NULL)
@JsonAutoDetect(fieldVisibility = JsonAutoDetect.Visibility.ANY)
public class CreateTesterRequest extends UserRequest{

    private List<Integer> siteIds;
    private String testGroup;
    private Map<String, String> qualifications;

    public CreateTesterRequest(List<Integer> siteIdList, boolean accountClaimRequired, TestGroup testGroup, Map<String, String> qualifications) {
        super(accountClaimRequired);
        this.siteIds = siteIdList;
        this.testGroup = testGroup == null ? null : String.valueOf(testGroup.group);
        this.qualifications = qualifications;
    }
}
