package uk.gov.dvsa.framework.listeners;

import org.testng.IAnnotationTransformer;
import org.testng.annotations.ITestAnnotation;
import uk.gov.dvsa.helper.ConfigHelper;

import java.lang.reflect.Constructor;
import java.lang.reflect.Method;

public class FeatureToggleAnnotationTransformer implements IAnnotationTransformer {
    @Override
    public void transform(ITestAnnotation iTestAnnotation, Class aClass, Constructor constructor, Method method) {

        if(iTestAnnotation.getTestName().equals("2fa")){
            iTestAnnotation.setEnabled(ConfigHelper.is2faEnabled());
        }

        if(iTestAnnotation.getTestName().equals("2faHardStop")) {
            iTestAnnotation.setEnabled(ConfigHelper.is2faHardStopEnabled());
        }

        if(iTestAnnotation.getTestName().equals("2faHardStopDisabled")) {
            iTestAnnotation.setEnabled(!ConfigHelper.is2faHardStopEnabled());
        }
    }
}
