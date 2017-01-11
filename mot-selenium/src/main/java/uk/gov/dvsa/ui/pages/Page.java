package uk.gov.dvsa.ui.pages;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import org.openqa.selenium.support.PageFactory;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.exception.PhpInlineErrorVerifier;

public abstract class Page {
    @FindBy(tagName = "h1") protected WebElement title;
    protected MotAppDriver driver;

    public Page(MotAppDriver driver) {
        this.driver = driver;
        PageFactory.initElements(driver, this);
        PageInteractionHelper.getInstance(driver);
        PhpInlineErrorVerifier.verifyErrorAtPage(driver, getTitle());
    }

    public String getTitle() {
        return title.getText();
    }

    protected abstract boolean selfVerify();
}

