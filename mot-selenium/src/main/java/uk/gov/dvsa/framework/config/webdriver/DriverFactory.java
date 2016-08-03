package uk.gov.dvsa.framework.config.webdriver;

import org.openqa.selenium.Platform;
import org.openqa.selenium.chrome.ChromeDriver;
import org.openqa.selenium.firefox.FirefoxDriver;
import org.openqa.selenium.firefox.FirefoxProfile;
import org.openqa.selenium.ie.InternetExplorerDriver;
import org.openqa.selenium.remote.DesiredCapabilities;
import org.openqa.selenium.remote.RemoteWebDriver;
import org.openqa.selenium.safari.SafariDriver;
import org.testng.Assert;
import uk.gov.dvsa.framework.config.Configurator;
import uk.gov.dvsa.helper.Utilities;

import java.net.MalformedURLException;
import java.net.URL;

public class DriverFactory {

    /**
     * Fetches an instance of the web driver as configured in the Configurator
     *
     * @param configurator Configurator to be used
     * @return a Selenium web driver as configured in the Configurator
     */
    public MotAppDriver getDriver(Configurator configurator) {
        switch (configurator.getGridStatus()) {
            case SELENIUM:
                return getSeleniumGridWebDriver(configurator);
            case BROWSERSTACK:
                return getBrowserStackWebDriver(configurator);
            default:
                return getLocalBrowser(configurator);
        }
    }

    private DesiredCapabilities setCapabilityIfNonNull(DesiredCapabilities capabilities, String key,
            Object value) {
        if (value != null) {
            capabilities.setCapability(key, value);
        }

        return capabilities;
    }

    private MotAppDriver getBrowserStackWebDriver(Configurator configurator) {
        //Set Desired Capabilities
        DesiredCapabilities capability = new DesiredCapabilities();

        setCapabilityIfNonNull(capability, "browser", configurator.getBrowser());
        setCapabilityIfNonNull(capability, "browser_version", configurator.getBrowserVersion());
        setCapabilityIfNonNull(capability, "os", configurator.getOs());
        setCapabilityIfNonNull(capability, "os_version", configurator.getOsVersion());
        setCapabilityIfNonNull(capability, "resolution", configurator.getResolution());
        setCapabilityIfNonNull(capability, "device", configurator.getDevice());
        setCapabilityIfNonNull(capability, "deviceOrientation",
                configurator.getDeviceOrientation());

        capability.setCapability("browserstack.local", "true");
        capability.setCapability("build", configurator.getBuildNumber());

        capability.setJavascriptEnabled(configurator.getJavascriptStatus());

        if (configurator.getBrowser() != null) {
            // Special configuration for some browsers
            switch (configurator.getBrowser()) {
                case FIREFOX: {
                    FirefoxProfile profile = new FirefoxProfile();
                    // TODO: Remove the disabling of the redirect prompt when the mock payment gateway is fixed
                    profile.setPreference("network.http.prompt-temp-redirect", false);
                    profile.setPreference("javascript.enabled", configurator.getJavascriptStatus());
                    capability.setCapability(FirefoxDriver.PROFILE, profile);
                    break;
                }
                case CHROME: {
                    System.setProperty("webdriver.chrome.driver",
                            configurator.getChromeDriverPath());
                    break;
                }
                case IE: {
                    capability.setCapability(
                            InternetExplorerDriver.INTRODUCE_FLAKINESS_BY_IGNORING_SECURITY_DOMAINS,
                            true);
                    break;
                }
            }
        }

        //Create the RemoteWebDriver object
        try {
            return new MotRemoteWebDriver(new RemoteWebDriver(new URL(configurator.getGridUrl()), capability));
        } catch (MalformedURLException e) {
            Assert.fail("Error in config. Check hub URL is correct." + e.toString());
            return null;
        }
    }

    /**
     * Returns an instance of a remote browser to run in Grid configuration
     *
     * @return RemoteWebDriver as configured in the Configurator
     * @throws java.net.MalformedURLException
     */
    private MotAppDriver getSeleniumGridWebDriver(Configurator configurator) {
        //Set Desired Capabilities
        DesiredCapabilities capability;

        //Set Browser version
        String browserVersion = configurator.getBrowserVersion();

        //Set Platform to run tests on (Windows, Linux etc)
        Platform platform = configurator.getPlatform();

        //Javascript status
        Boolean javascriptEnabled = configurator.getJavascriptStatus();

        switch (configurator.getBrowser()) {
            case FIREFOX: {
                capability = DesiredCapabilities.firefox();

                FirefoxProfile profile = new FirefoxProfile();
                // TODO: Remove the disabling of the redirect prompt when the mock payment gateway is fixed
                profile.setPreference("network.http.prompt-temp-redirect", false);
                profile.setPreference("javascript.enabled", javascriptEnabled);
                capability.setCapability(FirefoxDriver.PROFILE, profile);
                capability.setVersion(browserVersion);
                capability.setPlatform(platform);

                Utilities.Logger
                        .LogInfo("Javacript is enabled: " + String.valueOf(javascriptEnabled));
                break;
            }
            case CHROME: {
                System.setProperty("webdriver.chrome.driver", configurator.getChromeDriverPath());

                capability = DesiredCapabilities.chrome();
                capability.setVersion(browserVersion);
                capability.setPlatform(platform);
                capability.setJavascriptEnabled(javascriptEnabled);
                Utilities.Logger
                        .LogInfo("Javacript is enabled: " + String.valueOf(javascriptEnabled));
                break;
            }
            case SAFARI: {
                capability = DesiredCapabilities.safari();
                capability.setVersion(browserVersion);
                capability.setPlatform(platform);
                capability.setJavascriptEnabled(javascriptEnabled);
                Utilities.Logger
                        .LogInfo("Javacript is enabled: " + String.valueOf(javascriptEnabled));
                break;
            }
            case IE: {
                capability = DesiredCapabilities.internetExplorer();
                capability.setVersion(browserVersion);
                capability.setCapability(
                        InternetExplorerDriver.INTRODUCE_FLAKINESS_BY_IGNORING_SECURITY_DOMAINS,
                        true);
                capability.setPlatform(platform);
                capability.setJavascriptEnabled(javascriptEnabled);
                Utilities.Logger
                        .LogInfo("Javacript is enabled: " + String.valueOf(javascriptEnabled));
                break;
            }
            default: {
                capability = DesiredCapabilities.firefox();
                capability.setPlatform(Platform.ANY);
                capability.setJavascriptEnabled(true);
                Utilities.Logger
                        .LogInfo("Javacript is enabled: " + String.valueOf(javascriptEnabled));
                break;
            }
        }

        //Create the RemoteWebDriver object
        try {
            return new MotRemoteWebDriver(new RemoteWebDriver(new URL(configurator.getGridUrl()), capability));
        } catch (MalformedURLException e) {
            Assert.fail("Unable to create Remote WebDriver. Check Grid hub URL is correct." + e
                    .toString());
        }

        //Return null on exception
        return null;
    }

    /**
     * Returns an instance of a locally running browser
     *
     * @return a Selenium web driver as configured in the Configurator
     */
    private static MotAppDriver getLocalBrowser(Configurator configurator) {
        MotAppDriver driver;

        //Set Desired Capabilities
        DesiredCapabilities capability;

        //Set Browser version
        String browserVersion = configurator.getBrowserVersion();

        //Javascript status
        Boolean javascriptEnabled = configurator.getJavascriptStatus();

        switch (configurator.getBrowser()) {
            case FIREFOX: {
                capability = DesiredCapabilities.firefox();
                FirefoxProfile profile = new FirefoxProfile();
                // TODO: Remove the disabling of the redirect prompt when the mock payment gateway is fixed
                profile.setPreference("network.http.prompt-temp-redirect", false);
                profile.setPreference("javascript.enabled", javascriptEnabled);

                capability.setVersion(browserVersion);
                capability.setJavascriptEnabled(javascriptEnabled);
                Utilities.Logger.LogInfo("Javacript is enabled: " + String
                        .valueOf(capability.isJavascriptEnabled()));
                capability.setCapability(FirefoxDriver.PROFILE, profile);
                driver = MotBrowserFactory.createMotDriver(new FirefoxDriver(profile));
                break;
            }
            case CHROME: {
                System.setProperty("webdriver.chrome.driver", configurator.getChromeDriverPath());

                capability = DesiredCapabilities.chrome();
                capability.setJavascriptEnabled(javascriptEnabled);
                driver = MotBrowserFactory.createMotDriver(new ChromeDriver(capability));
                break;
            }
            case SAFARI: {
                capability = DesiredCapabilities.safari();
                capability.setJavascriptEnabled(javascriptEnabled);
                driver = MotBrowserFactory.createMotDriver(new SafariDriver(capability));
                break;
            }
            default: {
                driver = MotBrowserFactory.createMotDriver(new FirefoxDriver());
                break;
            }
        }

        return driver;
    }
}
