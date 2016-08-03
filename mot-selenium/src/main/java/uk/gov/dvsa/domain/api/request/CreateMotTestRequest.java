package uk.gov.dvsa.domain.api.request;

import com.fasterxml.jackson.annotation.JsonAutoDetect;
import com.fasterxml.jackson.annotation.JsonInclude;
import uk.gov.dvsa.domain.model.User;
import uk.gov.dvsa.domain.model.vehicle.Vehicle;

@JsonInclude(JsonInclude.Include.NON_NULL)
@JsonAutoDetect(fieldVisibility = JsonAutoDetect.Visibility.ANY)
public class CreateMotTestRequest {
    protected Requestor requestor;
    protected String vehicleId;
    protected int vtsId;
    protected MotTestData motTest;

    public CreateMotTestRequest(User requestor, Vehicle vehicle, int vtsId, MotTestData testData) {
        this.requestor = new Requestor(requestor);
        this.vehicleId = vehicle.getId();
        this.vtsId = vtsId;
        this.motTest = testData;
    }

    public String requestorName() {
        return requestor.username;
    }

    public String requestorPassword(){
        return requestor.password;
    }
}
