package uk.gov.dvsa.tools.test;

import org.apache.commons.lang3.StringUtils;

class TestMethodDescriptionHelper {

    static String convertCamelCaseToSentence(String name) {
        if(!name.equals("")) {
            return StringUtils.join(StringUtils.splitByCharacterTypeCamelCase(StringUtils.capitalize(name)), ' ');
        }
        return name;
    }
    static String useNameAsDescriptionWhereEmpty(String name, String description) {
        if(description.equals("")) {
            return convertCamelCaseToSentence(name);
        } else {
            return description;
        }
    }
}
