package uk.gov.dvsa.domain.model;

import com.fasterxml.jackson.annotation.JsonAutoDetect;
import com.fasterxml.jackson.annotation.JsonIgnoreProperties;
import uk.gov.dvsa.helper.TimeBasedOneTimePasswordHelper;

@JsonIgnoreProperties(ignoreUnknown = true)
@JsonAutoDetect(fieldVisibility = JsonAutoDetect.Visibility.ANY)
public class TwoFactorDetails {
    private String serialNumber;
    private String secret;

    public TwoFactorDetails() {}

    public String pin() {
        return TimeBasedOneTimePasswordHelper.generatePin(secret, System.currentTimeMillis());
    }

    public String serialNumber() {
        return serialNumber;
    }
}
