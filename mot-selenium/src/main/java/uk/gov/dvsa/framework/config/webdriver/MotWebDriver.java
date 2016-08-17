package uk.gov.dvsa.framework.config.webdriver;

import org.openqa.selenium.HasCapabilities;
import org.openqa.selenium.JavascriptExecutor;
import org.openqa.selenium.TakesScreenshot;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.interactions.HasInputDevices;
import org.openqa.selenium.internal.*;

public interface MotWebDriver extends
        WebDriver, JavascriptExecutor, FindsById, FindsByClassName,
        FindsByLinkText, FindsByName, FindsByCssSelector, FindsByTagName,
        FindsByXPath, HasInputDevices, HasCapabilities, TakesScreenshot {
    String getPageSource();
    void takeScreenShot(String filename, String destinationPath);
}
