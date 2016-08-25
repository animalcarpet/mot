package uk.gov.dvsa.ui.pages.authentication.twofactorauth;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;

public class RegisterCardInformationPage extends Page {

    private static final String PAGE_TITLE = "Changes to your account security";
    public static final String PATH = "/security-card-information/";

    @FindBy(id = "register-card-link") private WebElement activateCardLink;
    @FindBy(id = "continue-to-home") private WebElement continueToHomeLink;

    public RegisterCardInformationPage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE);
    }

    public void clickRegisterCardLink() {
        activateCardLink.click();
    }

    public void clickContinueToHomeLink() {
        continueToHomeLink.click();
    }
}
