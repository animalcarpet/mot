package uk.gov.dvsa.helper;

import uk.gov.dvsa.domain.service.FeaturesService;

public class ConfigHelper {
    private static FeaturesService service = new FeaturesService();

    public static boolean is2faEnabled() {
        return service.getToggleValue("2fa.enabled");
    }

    public static boolean is2faHardStopEnabled() { return service.getToggleValue("2fa.hardstop.enabled"); }
}
