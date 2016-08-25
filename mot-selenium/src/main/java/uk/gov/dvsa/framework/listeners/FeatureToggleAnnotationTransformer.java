package uk.gov.dvsa.framework.listeners;

import org.testng.IAnnotationTransformer;
import org.testng.annotations.ITestAnnotation;
import uk.gov.dvsa.helper.ConfigHelper;

import java.lang.reflect.Constructor;
import java.lang.reflect.Method;

public class FeatureToggleAnnotationTransformer implements IAnnotationTransformer {
    @Override
    public void transform(ITestAnnotation iTestAnnotation, Class aClass, Constructor constructor, Method method) {
        if (iTestAnnotation.getTestName().equals("NewProfile")) {
            iTestAnnotation.setEnabled(ConfigHelper.isNewPersonProfileEnabled());
        }

        if (iTestAnnotation.getTestName().equals("Legacy")) {
            iTestAnnotation.setEnabled(!ConfigHelper.isNewPersonProfileEnabled());
        }

        if (iTestAnnotation.getTestName().equals("Survey")) {
            iTestAnnotation.setEnabled(ConfigHelper.isSurveyPageEnabled());
        }

        if (iTestAnnotation.getTestName().equals("TestResultEntryImprovements")) {
            iTestAnnotation.setEnabled(ConfigHelper.isTestResultEntryImprovementsEnabled());
        }

        if (iTestAnnotation.getTestName().equals("OldRFRTest")) {
            iTestAnnotation.setEnabled(!ConfigHelper.isTestResultEntryImprovementsEnabled());
        }

        if(iTestAnnotation.getTestName().equals("2fa")){
            iTestAnnotation.setEnabled(ConfigHelper.is2faEnabled());
        }

        if(iTestAnnotation.getTestName().equals("non-2fa")){
            iTestAnnotation.setEnabled(!ConfigHelper.is2faEnabled());
        }

        if (iTestAnnotation.getTestName().equals("TestQualityInformation")) {
            iTestAnnotation.setEnabled(ConfigHelper.isTestQualityInformationEnabled());
        }
    }
}
