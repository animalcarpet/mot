package uk.gov.dvsa.helper;

import com.dvsa.mot.selenium.framework.Utilities;
import com.google.common.base.Function;
import org.openqa.selenium.*;
import org.openqa.selenium.support.ui.*;
import uk.gov.dvsa.framework.config.Configurator;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.ui.pages.exception.PageInstanceNotFoundException;

import java.util.Date;
import java.util.List;
import java.util.concurrent.TimeUnit;

/*
      To contain helper methods for finding, interacting with and populating page elements
   */
public class PageInteractionHelper {
    protected static final int AJAX_MAXIMUM_TIMEOUT = 5;

    private static PageInteractionHelper pageInstance = null;
    private static MotAppDriver driver;

    private PageInteractionHelper(MotAppDriver driver) {
        PageInteractionHelper.driver = driver;
    }

    public static PageInteractionHelper getInstance(MotAppDriver driver){
        if(pageInstance == null){
            pageInstance = new PageInteractionHelper(driver);
        }

        return pageInstance;
    }

    public static boolean verifyTitle(String actual, String expected) {
        if(actual.contains(expected)){
            return true;
        }

        throw new PageInstanceNotFoundException("Page verification failed: "
                + String.format("\n Expected: %s page, \n Found: %s page", expected, actual)
        );
    }

    public static boolean verifyTitle(String actual, String expected1, String expected2) {
        if(actual.contains(expected1) || actual.contains(expected2)){
            return true;
        }

        throw new PageInstanceNotFoundException(
                "Page verification failed for this page. "
                        + String.format("%s or %s ", expected1, expected2)
                        + String.format("\n but found %s page", actual)
        );
    }

    /**
     * Used for temporarily turning off implicit driver waits, should be followed by turning them back on after the
     * required step is done
     */
    protected static void turnOffImplicitWaits() {
        driver.manage().timeouts().implicitlyWait(500, TimeUnit.MILLISECONDS);
    }

    /**
     * Used for resetting implicit waits to the default
     */
    protected static void turnOnImplicitWaits() {
        driver.manage().timeouts().implicitlyWait(Configurator.defaultWebElementTimeout, TimeUnit.SECONDS);
    }

    /**
     * Returns the element marked invalid by HTML5 validation
     */
    public WebElement findElementMarkedInvalid() {
        return driver.findElement(By.cssSelector("input:invalid"));
    }

    public List<WebElement> findElementWithoutImplicitWaits(By by) {
        turnOffImplicitWaits();
        List<WebElement> elements = driver.findElements(by);
        turnOnImplicitWaits();
        return elements;
    }

    public static WebElement waitForElementToBeVisible(By locator, int timeOut) {
        return new WebDriverWait(driver, timeOut)
                .until(ExpectedConditions.visibilityOfElementLocated(locator));
    }

    /**
     * Waits for the browser to indicate the page has completed loading
     */
    public static void waitForPageToLoad() {
        ExpectedCondition<Boolean> pageLoad = new ExpectedCondition<Boolean>() {
            public Boolean apply(WebDriver driver) {
                return executeJavascript("return document.readyState").equals("complete");
            }
        };

        Wait<WebDriver> wait = new WebDriverWait(driver, 60);

        try {
            wait.until(pageLoad);
        } catch (Throwable pageLoadWaitError) {
            throw new TimeoutException("Timeout during page load");
        }
    }

    /**
     * Disables print popups.
     * This only works if the Javascript custom print method is configured to look for
     * an element with id printingDisabled. It is not a generic method to prevent printing!
     */
    protected void disablePrintingOnCurrentPage() {
        executeJavascript("newNode = document.createElement('span');"
                + "document.getElementsByTagName('html')[0].appendChild(newNode);"
                + "newNode.id='printingDisabled';");
    }

    /**
     * Wait for any jQuery activity to be completed. This is useful functionality when waiting for an AJAX call or
     * other JavaScript update process to complete.
     */
    public static void waitForAjaxToComplete() {
        ExpectedCondition<Boolean> jqueryActive = new ExpectedCondition<Boolean>() {
            @Override
            public Boolean apply(WebDriver driver) {
                return executeJavascript("return (window.jQuery || { active : 0 }).active")
                        .toString().equals("0");
            }
        };

        Wait<WebDriver> wait = new WebDriverWait(driver, 60);

        try {
            wait.until(jqueryActive);
        } catch (Throwable pageLoadWaitError) {
            throw new TimeoutException("Timeout waiting for jQuery to complete");
        }
    }

    /**
     * Wait until the element specified is visible in the page
     *
     * @param element element to watch for
     * @param timeout maximum time to wait in seconds
     */
    public static void waitForElementToBeVisible(WebElement element, int timeout) {
        new WebDriverWait(driver, timeout).until(ExpectedConditions.visibilityOf(element));
    }

    public static WebElement refreshPageUntilElementWontBeVisible(final By locator, int timeout, int refreshEveryTimeout) {
        Wait<WebDriver> wait = new FluentWait<WebDriver>(driver)
                .withTimeout(timeout, TimeUnit.SECONDS)
                .pollingEvery(refreshEveryTimeout, TimeUnit.SECONDS)
                .ignoring(NoSuchElementException.class);

        return wait.until(new Function<WebDriver, WebElement>() {
            public WebElement apply(WebDriver driver) {
                driver.navigate().refresh();
                return driver.findElement(locator);
            }
        });
    }

    protected boolean isElementClickable(WebElement element, int timeout) {
        return new WebDriverWait(driver, timeout)
                .until(ExpectedConditions.elementToBeClickable(element)) != null;
    }

    protected void waitForTextToBePresentInElement(WebElement element, String text, int timeout) {
        new WebDriverWait(driver, timeout)
                .until(ExpectedConditions.textToBePresentInElement(element, text));

    }

    public static WebElement waitForElementToBeClickable(By locator) {
        return new WebDriverWait(driver, AJAX_MAXIMUM_TIMEOUT)
                .until(ExpectedConditions.elementToBeClickable(locator));
    }

    public static WebElement waitForElementToBeClickable(WebElement element) {
        return new WebDriverWait(driver, AJAX_MAXIMUM_TIMEOUT)
                .until(ExpectedConditions.elementToBeClickable(element));
    }

    /**
     * Returns true if the element matched in the By is present in the page even if it is not displayed
     */
    public static boolean isElementPresent(By locator) {
        try {
            turnOffImplicitWaits();
            List<WebElement> elements = new WebDriverWait(driver, Configurator.defaultWebElementTimeout)
                    .until(ExpectedConditions.presenceOfAllElementsLocatedBy(locator));

            return elements.size() > 0;
        } catch (TimeoutException ex) {
            return false;
        } finally {
            turnOnImplicitWaits();
        }
    }

    /**
     * Refresh the current page
     */
    public static void refreshPage(){
        driver.navigate().refresh();
    }

    /**
     * Returns true if the element is displayed in the page, no timeouts are used
     */
    public static boolean isElementDisplayed(WebElement element) {
        try {
            turnOffImplicitWaits();
            return element.isDisplayed();
        } catch (NoSuchElementException | TimeoutException ex) {
            return false;
        } finally {
            turnOnImplicitWaits();
        }
    }

    /**
     * Returns true if the element is displayed in the page, no timeouts are used
     */
    public static boolean isElementDisplayed(By elementLocator) {
        try {
            turnOffImplicitWaits();
            WebElement element = driver.findElement(elementLocator);
            return element.isDisplayed();
        } catch (NoSuchElementException | TimeoutException ex) {
            return false;
        } finally {
            turnOnImplicitWaits();
        }
    }

    /**
     * Method for calling Javascript in the context of the page running in
     * Selenium
     *
     * @param script Javascript to be executed
     * @param args   Arguments for the script
     * @return Throws an exception if the script cannot be executed on current
     * driver, else see JavascriptExecutor
     * @see org.openqa.selenium.JavascriptExecutor
     */
    protected static Object executeJavascript(String script, Object... args) {
        if (driver instanceof JavascriptExecutor) {
            return driver.executeScript(script, args);
        } else {
            throw new IllegalStateException(
                    "Cannot execute Javascript (the driver " + driver.getClass().getSimpleName()
                            + " does not implement the JavascriptExecutor interface)");
        }
    }

    protected void takePageScreenshot(String screenShotPath) {
        String screenPhotoName =
                (this.getClass().getName().replace("com.dvsa.mot.selenium.", "")) + "_" +
                        Configurator.screenshotDateFormat.format(new Date()) + ".png";

        Utilities.takeScreenShot(driver, screenPhotoName, screenShotPath);
    }
}
