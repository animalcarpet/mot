package uk.gov.dvsa.framework.config.webdriver;

import org.openqa.selenium.chrome.ChromeDriver;
import org.openqa.selenium.firefox.FirefoxDriver;
import org.openqa.selenium.remote.RemoteWebDriver;

public class MotBrowserFactory {

     public static MotAppDriver createMotDriver(RemoteWebDriver remoteWebDriver){
        return new MotRemoteWebDriver(remoteWebDriver);

    }
}
