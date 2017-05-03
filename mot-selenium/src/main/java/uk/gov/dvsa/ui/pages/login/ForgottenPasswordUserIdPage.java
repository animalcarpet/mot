package uk.gov.dvsa.ui.pages.login;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;

import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.FormDataHelper;
import uk.gov.dvsa.helper.PageInteractionHelper;

public class ForgottenPasswordUserIdPage extends  AbstractForgottenPasswordPage {
   private static final String PAGE_TITLE = "Forgotten your password";
   public static final String PATH = "/forgotten-password";

    @FindBy(id = "username") private WebElement usernameField;
    @FindBy(id = "submitUserId") private WebElement continueButton;

    public ForgottenPasswordUserIdPage(final MotAppDriver driver) {
        super(driver, PAGE_TITLE);
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE);
    }

    public ForgottenPasswordUserIdPage enterUserId(String userId) {
        FormDataHelper.enterText(usernameField, userId);
        return this;
    }

    public ForgottenPasswordQuestionOnePage continueToSecurityQuestionOnePage() {
        continueButton.click();
        return new ForgottenPasswordQuestionOnePage(driver);
    }
}
