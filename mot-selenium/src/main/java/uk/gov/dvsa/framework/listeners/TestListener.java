package uk.gov.dvsa.framework.listeners;

import org.openqa.selenium.OutputType;
import org.testng.ITestListener;
import org.testng.ITestResult;
import org.testng.TestListenerAdapter;
import ru.yandex.qatools.allure.annotations.Attachment;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.helper.Utilities;

import java.io.File;
import java.io.IOException;
import java.nio.file.Files;
import java.nio.file.Paths;

public class TestListener extends TestListenerAdapter implements ITestListener {

    // Keep these the same length for easier-to-read output
    private static final String RUNNING = "Running:             ";
    private static final String SKIPPED = "SKIPPED (%8.1f)s: ";
    private static final String FAILURE = "FAILURE (%8.1f)s: ";
    private static final String SUCCESS = "Success (%8.1f)s: ";

    @Override public void onTestStart(ITestResult result) {
        super.onTestStart(result);
        Utilities.Logger.LogInfo(RUNNING + printable(result));
    }

    @Override public void onTestSuccess(ITestResult result) {
        super.onTestSuccess(result);
        Utilities.Logger.LogInfo(String.format(SUCCESS, duration(result)) + printable(result));
    }

    @Override public void onTestFailure(ITestResult result) {
        super.onTestFailure(result);
        saveImageAttachment(getScreenShotName(result));
        Utilities.Logger.LogInfo(String.format(FAILURE, duration(result)) + printable(result));
    }

    @Override public void onTestSkipped(ITestResult result) {
        super.onTestSkipped(result);
        Utilities.Logger.LogInfo(String.format(SKIPPED, duration(result)) + printable(result));
    }

    @Override public void onTestFailedButWithinSuccessPercentage(ITestResult result) {
        super.onTestFailedButWithinSuccessPercentage(result);
        Utilities.Logger.LogInfo("Failure within success %: " + printable(result));
    }

    private String printable(ITestResult result) {
        return result.getTestClass().getName() + "." + result.getName();
    }

    private float duration(ITestResult result) {
        return (result.getEndMillis() - result.getStartMillis()) / 1000f;
    }

    @Attachment(value = "{0}")
    private byte[] saveImageAttachment(String imageName){
        try {
            File scrFile = (PageInteractionHelper.getDriver()).getScreenshotAs(OutputType.FILE);
            return toByteArray(scrFile);
        } catch (Exception e) {
            e.getMessage();
        }
        return new byte[0];
    }

    private static byte[] toByteArray(File file) throws IOException {
        return Files.readAllBytes(Paths.get(file.getPath()));
    }

    private String getScreenShotName(ITestResult result) {
        return result.getTestClass().getName().replace("uk.gov.dvsa.", "") + "." + result.getName() + ".png";
    }
}
