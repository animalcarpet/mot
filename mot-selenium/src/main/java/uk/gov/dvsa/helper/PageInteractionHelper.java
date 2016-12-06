package uk.gov.dvsa.helper;

import com.google.common.base.Function;
import org.openqa.selenium.*;
import org.openqa.selenium.support.ui.*;
import uk.gov.dvsa.domain.navigation.MotPageFactory;
import uk.gov.dvsa.framework.config.Configurator;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.ui.pages.Page;
import uk.gov.dvsa.ui.pages.exception.ExpectedElementNotFoundOnPageException;
import uk.gov.dvsa.ui.pages.exception.PageInstanceNotFoundException;
import uk.gov.dvsa.ui.pages.exception.UnExpectedElementFoundOnPageException;

import java.util.List;
import java.util.concurrent.TimeUnit;

/*
      To contain helper methods for finding, interacting with and populating page elements
   */
public class PageInteractionHelper {
    protected static final int AJAX_MAXIMUM_TIMEOUT = 5;

    private static ThreadLocal<MotAppDriver> driver = new ThreadLocal<>();

    private PageInteractionHelper(MotAppDriver driver) {
        PageInteractionHelper.driver.set(driver);
    }

    public static PageInteractionHelper setDriver(MotAppDriver driver){
        return new PageInteractionHelper(driver);
    }

    public static boolean expectElementOnPage(WebElement elementLocator, String elementName) {
        if(isElementDisplayed(elementLocator)){
            return true;
        }

        throw new ExpectedElementNotFoundOnPageException(
            String.format("Expected Element: %s not found on Page", elementName));
    }

    public static boolean elementNotExpectedOnPage(WebElement elementLocator, String elementName) {
        if(!isElementDisplayed(elementLocator)){
            return true;
        }

        throw new UnExpectedElementFoundOnPageException(
            String.format("Unexpected Element: %s found on Page", elementName));
    }

    public static boolean verifyTitle(String actual, String expected) {
        if(actual.contains(expected)){
            return true;
        }

        throw new PageInstanceNotFoundException("Page verification failed: "
                + String.format("\n Expected: %s page, \n Found: %s page", expected, actual)
        );
    }

    public static boolean verifyPageUrl(String actual, String expected) {
        if(actual.contains(expected)){
            return true;
        }

        throw new PageInstanceNotFoundException("Page verification failed: "
                + String.format("\n Expected page Url: %s, \n Found: %s", expected, actual)
        );
    }

    public static boolean verifyTitle(String actual, String... expected) {
        StringBuilder builder = new StringBuilder();

        for (String value : expected) {
            if (actual.contains(value)) {
                return true;
            }
            builder.append(value).append(" ");
        }

        throw new PageInstanceNotFoundException(
                String.format("Page verification failed for this page. Expected: %s \nbut found %s page", builder.toString(), actual));
        }

    /**
     * Used for temporarily turning off implicit driver waits, should be followed by turning them back on after the
     * required step is done
     */
    protected static void turnOffImplicitWaits() {
        driver.get().manage().timeouts().implicitlyWait(0, TimeUnit.MILLISECONDS);
    }

    /**
     * Used for resetting implicit waits to the default
     */
    protected static void turnOnImplicitWaits() {
        driver.get().manage().timeouts().implicitlyWait(Configurator.defaultWebElementTimeout, TimeUnit.SECONDS);
    }

    /**
     * Returns the element marked invalid by HTML5 validation
     */
    public WebElement findElementMarkedInvalid() {
        return driver.get().findElement(By.cssSelector("input:invalid"));
    }

    public List<WebElement> findElementWithoutImplicitWaits(By by) {
        turnOffImplicitWaits();
        List<WebElement> elements = driver.get().findElements(by);
        turnOnImplicitWaits();
        return elements;
    }

    public static WebElement waitForElementToBeVisible(By locator, int timeOut) {
        return new WebDriverWait(driver.get(), timeOut)
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

        Wait<WebDriver> wait = new WebDriverWait(driver.get(), 60);

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

        Wait<WebDriver> wait = new WebDriverWait(driver.get(), 60);

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
        new WebDriverWait(driver.get(), timeout).until(ExpectedConditions.visibilityOf(element));
    }

    public static WebElement refreshPageUntilElementWontBeVisible(final By locator, int timeout, int refreshEveryTimeout) {
        Wait<WebDriver> wait = new FluentWait<WebDriver>(driver.get())
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

    public static <T extends Page> T refreshPageWhileElementIsVisible(Class<T> clazz, WebElement element) throws InterruptedException {
        while (element.isDisplayed()) {
            driver.get().navigate().refresh();
        }
        return MotPageFactory.newPage(driver.get(), clazz);
    }

    protected boolean isElementClickable(WebElement element, int timeout) {
        return new WebDriverWait(driver.get(), timeout)
                .until(ExpectedConditions.elementToBeClickable(element)) != null;
    }

    public static void waitForTextToBePresentInElement(WebElement element, String text, int timeout) {
        new WebDriverWait(driver.get(), timeout)
                .until(ExpectedConditions.textToBePresentInElement(element, text));

    }

    public static WebElement waitForElementToBeClickable(By locator) {
        return new WebDriverWait(driver.get(), AJAX_MAXIMUM_TIMEOUT)
                .until(ExpectedConditions.elementToBeClickable(locator));
    }

    public static WebElement waitForElementToBeClickable(WebElement element) {
        return new WebDriverWait(driver.get(), AJAX_MAXIMUM_TIMEOUT)
                .until(ExpectedConditions.elementToBeClickable(element));
    }

    /**
     * Returns true if the element matched in the By is present in the page even if it is not displayed
     */
    public static boolean isElementPresent(By locator) {
        try {
            turnOffImplicitWaits();
            List<WebElement> elements = new WebDriverWait(driver.get(), Configurator.defaultWebElementTimeout)
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
        driver.get().navigate().refresh();
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
            WebElement element = driver.get().findElement(elementLocator);
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
    public static Object executeJavascript(String script, Object... args) {
        if (driver.get() instanceof JavascriptExecutor) {
            return driver.get().executeScript(script, args);
        } else {
            throw new IllegalStateException(
                    "Cannot execute Javascript (the driver " + driver.getClass().getSimpleName()
                            + " does not implement the JavascriptExecutor interface)");
        }
    }

    public static MotAppDriver getDriver(){
        return driver.get();
    }
}
