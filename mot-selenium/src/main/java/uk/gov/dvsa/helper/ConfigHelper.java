package uk.gov.dvsa.helper;

import uk.gov.dvsa.domain.service.FeaturesService;

public class ConfigHelper {
    private static FeaturesService service = new FeaturesService();

    public static boolean isNewPersonProfileEnabled() {
        return service.getToggleValue("new_person_profile");
    }

    public static boolean isSurveyPageEnabled() {
        return service.getToggleValue("survey_page");
    }

    public static boolean isTestResultEntryImprovementsEnabled() {
        return service.getToggleValue("test_result_entry_improvements");
    }
    public static boolean isTestQualityInformationEnabled() {
        return service.getToggleValue("test_quality_information");
    }
}
