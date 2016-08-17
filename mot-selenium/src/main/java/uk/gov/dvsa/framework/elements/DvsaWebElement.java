package uk.gov.dvsa.framework.elements;

import org.openqa.selenium.*;

import java.util.ArrayList;
import java.util.List;

public class DvsaWebElement implements WebElement {

    private Locator locator;
    private WebElement underlyingElement;

    public static WebElement wrap(WebElement element, Locator locator) {
        return new DvsaWebElement(element, locator);
    }

    public static List<WebElement> wrap(List<WebElement> elements, Locator locator) {
        List<WebElement> webElements = new ArrayList<>();

        for (WebElement element : elements) {
            webElements.add(wrap(element, locator));
        }

        return webElements;
    }

    protected DvsaWebElement(WebElement underlyingElement, Locator locator) {
        this.underlyingElement = underlyingElement;
        this.locator = locator;
    }

    @Override
    public void click() {
        try {
            underlyingElement.click();
        } catch (StaleElementReferenceException sere) {
            againLocate();
            click();
        }
    }

    @Override
    public void submit() {
        try {
            underlyingElement.submit();
        } catch (StaleElementReferenceException sere) {
            againLocate();
            submit();
        }
    }

    @Override
    public void sendKeys(CharSequence... charSequences) {
        try {
            underlyingElement.sendKeys(charSequences);
        } catch (StaleElementReferenceException sere) {
            againLocate();
            sendKeys(charSequences);
        }
    }

    @Override
    public void clear() {
        try {
            underlyingElement.clear();
        } catch (StaleElementReferenceException sere) {
            againLocate();
            clear();
        }
    }

    @Override
    public String getTagName() {
        try {
            return underlyingElement.getTagName();
        } catch (StaleElementReferenceException sere) {
            againLocate();
            return getTagName();
        }
    }

    @Override
    public String getAttribute(String s) {
        try {
            return underlyingElement.getAttribute(s);
        } catch (StaleElementReferenceException sere) {
            againLocate();
            return getAttribute(s);
        }
    }

    @Override
    public boolean isSelected() {
        try {
            return underlyingElement.isSelected();
        } catch (StaleElementReferenceException sere) {
            againLocate();
            return isSelected();
        }
    }

    @Override
    public boolean isEnabled() {
        try {
            return underlyingElement.isEnabled();
        } catch (StaleElementReferenceException sere) {
            againLocate();
            return isEnabled();
        }
    }

    @Override
    public String getText() {
        try {
            return underlyingElement.getText();
        } catch (StaleElementReferenceException sere) {
            againLocate();
            return getText();
        }
    }

    @Override
    public List<WebElement> findElements(By by) {
        try {
            List<WebElement> webElements = new ArrayList<>();
            List<WebElement> webElementsFound = underlyingElement.findElements(by);

            for (int i = 0; i < webElementsFound.size(); i++) {
                webElements.add(wrap(webElementsFound.get(i), new FindElementsLocator(this, by, i)));
            }

            return webElements;

        } catch (StaleElementReferenceException sere) {
            againLocate();
            return findElements(by);
        }
    }

    @Override
    public WebElement findElement(By by) {
        try {
            return wrap(underlyingElement.findElement(by), new FindElementLocator(this, by));
        } catch (StaleElementReferenceException sere) {
            againLocate();
            return findElement(by);
        }
    }

    @Override
    public boolean isDisplayed() {
        try {
            return underlyingElement.isDisplayed();
        } catch (StaleElementReferenceException sere) {
            againLocate();
            return isDisplayed();
        }
    }

    @Override
    public Point getLocation() {
        try {
            return underlyingElement.getLocation();
        } catch (StaleElementReferenceException sere) {
            againLocate();
            return getLocation();
        }
    }

    @Override
    public Dimension getSize() {
        try {
            return underlyingElement.getSize();
        } catch (StaleElementReferenceException sere) {
            againLocate();
            return getSize();
        }
    }

    @Override
    public Rectangle getRect() {
        try {
            return underlyingElement.getRect();
        } catch (StaleElementReferenceException sere) {
            againLocate();
            return getRect();
        }
    }

    @Override
    public String getCssValue(String s) {
        try {
            return underlyingElement.getCssValue(s);
        } catch (StaleElementReferenceException sere) {
            againLocate();
            return getCssValue(s);
        }
    }

    protected void againLocate() {
        underlyingElement = locator.locate();
    }

    @Override
    public <X> X getScreenshotAs(OutputType<X> xOutputType) throws WebDriverException {
        try {
            return underlyingElement.getScreenshotAs(xOutputType);
        } catch (StaleElementReferenceException sere) {
            againLocate();
            return getScreenshotAs(xOutputType);
        }
    }
}
