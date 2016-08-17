package uk.gov.dvsa.ui.pages;

import org.openqa.selenium.By;
import org.openqa.selenium.StaleElementReferenceException;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import org.openqa.selenium.support.PageFactory;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.framework.elements.DvsaElementLocatorFactory;
import uk.gov.dvsa.framework.elements.DvsaWebElement;
import uk.gov.dvsa.framework.elements.FindElementLocator;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.elements.Breadcrumb;
import uk.gov.dvsa.ui.pages.exception.PhpInlineErrorVerifier;

public abstract class Page {

    @FindBy(tagName = "h1") private WebElement title;
    protected MotAppDriver driver;

    public Page(final MotAppDriver driver) {
        this.driver = driver;
        DvsaElementLocatorFactory factory = new DvsaElementLocatorFactory(driver);
        PageFactory.initElements(factory, this);
        PageInteractionHelper.setDriver(driver);
        PhpInlineErrorVerifier.verifyErrorAtPage(driver, getTitle());
    }

    public final String getTitle() {
        return title.getText();
    }

    public final Breadcrumb getBreadcrumb() {
        return new Breadcrumb(driver);
    }

    protected abstract boolean selfVerify();

    @Override
    public final String toString() {
        return "Page: " + getTitle();
    }

    protected String getElementText(By selector) {
        try {
            return driver.findElement(selector).getText();
        } catch (StaleElementReferenceException ex) {
            return getElementText(selector);
        }
    }

    protected Boolean isElementVisible(By selector) {
        try {
            return driver.findElement(selector).isDisplayed();
        } catch (StaleElementReferenceException ex) {
            return isElementVisible(selector);
        }
    }

    protected void clickElement(By selector) {
        try {
            driver.findElement(selector).click();
        } catch (StaleElementReferenceException ex) {
            clickElement(selector);
        }
    }

    protected WebElement getElement(By selector) {
        try {
            return DvsaWebElement.wrap(driver.findElement(selector), new FindElementLocator(driver, selector));
        } catch (StaleElementReferenceException ex) {
            return getElement(selector);
        }
    }
}
