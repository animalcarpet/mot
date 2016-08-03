package uk.gov.dvsa.ui.pages;

import org.openqa.selenium.By;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.PageFactory;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.elements.Breadcrumb;
import uk.gov.dvsa.ui.pages.exception.PhpInlineErrorVerifier;

public abstract class Page {
    private By title = By.tagName("h1");
    protected MotAppDriver driver;

    public Page(final MotAppDriver driver) {
        this.driver = driver;
        PageFactory.initElements(driver, this);
        PageInteractionHelper.setDriver(driver);
        PhpInlineErrorVerifier.verifyErrorAtPage(driver, getTitle());
    }

    public final String getTitle() {
        return driver.findElement(title).getText();
    }

    public final Breadcrumb getBreadcrumb() {
        return new Breadcrumb(driver);
    }

    protected abstract boolean selfVerify();

    @Override
    public final String toString() {
        return "Page: " + getTitle();
    }
}
