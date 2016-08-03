package uk.gov.dvsa.framework.config.webdriver;

import org.openqa.selenium.*;
import org.openqa.selenium.interactions.Keyboard;
import org.openqa.selenium.interactions.Mouse;
import org.openqa.selenium.remote.RemoteWebDriver;
import org.openqa.selenium.remote.UnreachableBrowserException;
import org.openqa.selenium.support.ui.ExpectedConditions;
import org.openqa.selenium.support.ui.WebDriverWait;

import java.util.List;
import java.util.Set;

public class MotRemoteWebDriver extends MotAppDriver {

    public MotRemoteWebDriver(final RemoteWebDriver remoteWebDriver) {
        super(remoteWebDriver);
    }

    @Override
    public WebElement findElementByClassName(String s) {
        return remoteWebDriver.findElementByClassName(s);
    }

    @Override
    public List<WebElement> findElementsByClassName(String s) {
        return remoteWebDriver.findElementsByClassName(s);
    }

    @Override
    public WebElement findElementByCssSelector(String s) {
        return remoteWebDriver.findElementByCssSelector(s);
    }

    @Override
    public List<WebElement> findElementsByCssSelector(String s) {
        return null;
    }

    @Override
    public WebElement findElementById(String s) {
        return null;
    }

    @Override
    public List<WebElement> findElementsById(String s) {
        return null;
    }

    @Override
    public WebElement findElementByLinkText(String s) {
        return null;
    }

    @Override
    public List<WebElement> findElementsByLinkText(String s) {
        return null;
    }

    @Override
    public WebElement findElementByPartialLinkText(String s) {
        return remoteWebDriver.findElementByPartialLinkText(s);
    }

    @Override
    public List<WebElement> findElementsByPartialLinkText(String s) {
        return remoteWebDriver.findElementsByPartialLinkText(s);
    }

    @Override
    public WebElement findElementByName(String s) {
        return remoteWebDriver.findElementByName(s);
    }

    @Override
    public List<WebElement> findElementsByName(String s) {
        return remoteWebDriver.findElementsByTagName(s);
    }

    @Override
    public WebElement findElementByTagName(String s) {
        return remoteWebDriver.findElementByTagName(s);
    }

    @Override
    public List<WebElement> findElementsByTagName(String s) {
        return remoteWebDriver.findElementsByTagName(s);
    }

    @Override
    public WebElement findElementByXPath(String s) {
        return null;
    }

    @Override
    public List<WebElement> findElementsByXPath(String s) {
        return remoteWebDriver.findElementsByXPath(s);
    }

    @Override
    public Capabilities getCapabilities() {
        return remoteWebDriver.getCapabilities();
    }

    @Override
    public Keyboard getKeyboard() {
        return remoteWebDriver.getKeyboard();
    }

    @Override
    public Mouse getMouse() {
        return remoteWebDriver.getMouse();
    }

    @Override
    public Object executeScript(String s, Object... objects) {
        return remoteWebDriver.executeScript(s, objects);
    }

    @Override
    public Object executeAsyncScript(String s, Object... objects) {
        return remoteWebDriver.executeAsyncScript(s, objects);
    }

    @Override
    public <X> X getScreenshotAs(OutputType<X> outputType) throws WebDriverException {
        return remoteWebDriver.getScreenshotAs(outputType);
    }

    @Override
    public void get(String s) {
        remoteWebDriver.get(s);
    }

    @Override
    public String getCurrentUrl() {
        return remoteWebDriver.getCurrentUrl();
    }

    @Override
    public String getTitle() {
        return remoteWebDriver.getTitle();
    }

    @Override
    public List<WebElement> findElements(By by) {
        return remoteWebDriver.findElements(by);
    }

    @Override
    public WebElement findElement(By by) {
        new WebDriverWait(remoteWebDriver, 10).until(ExpectedConditions.presenceOfElementLocated(by));
        return remoteWebDriver.findElement(by);
    }

    @Override
    public String getPageSource() {
        return remoteWebDriver.getPageSource();
    }

    @Override
    public void close() {
        remoteWebDriver.close();
    }

    @Override
    public void quit() {
        try {
            remoteWebDriver.quit();
        } catch (UnreachableBrowserException e){
            //There is no sense propagating the exception, the browser is already dead if an exception happens
            //It stops Jenkins printing stack trace all over the place
            //We can simply log a warning if necessary
        }
    }

    @Override
    public Set<String> getWindowHandles() {
        return remoteWebDriver.getWindowHandles();
    }

    @Override
    public String getWindowHandle() {
        return remoteWebDriver.getWindowHandle();
    }

    @Override
    public TargetLocator switchTo() {
        return remoteWebDriver.switchTo();
    }

    @Override
    public Navigation navigate() {
        return remoteWebDriver.navigate();
    }

    @Override
    public Options manage() {
        return remoteWebDriver.manage();
    }
}
