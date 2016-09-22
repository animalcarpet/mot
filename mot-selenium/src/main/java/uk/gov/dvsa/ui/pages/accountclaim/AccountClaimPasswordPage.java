package uk.gov.dvsa.ui.pages.accountclaim;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;

import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.FormDataHelper;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;

public class AccountClaimPasswordPage extends Page {

    public static final String PATH = "/account/claim";
    private static final String PAGE_TITLE = "Reset your account security";

    @FindBy(id = "password") private WebElement passwordField;
    @FindBy(id = "confirm_password") private WebElement confirmPasswordField;
    @FindBy(id = "btSubmitForm") private WebElement submitFormButton;

    public AccountClaimPasswordPage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE);
    }

    public AccountClaimPasswordPage enterPassword(String password) {

        FormDataHelper.enterText(passwordField, password);
        FormDataHelper.enterText(confirmPasswordField, password);

        return this;
    }

    public AccountClaimSecurityQuestionsPage clickContinueButton() {
        submitFormButton.click();

        return new AccountClaimSecurityQuestionsPage(driver);
    }
}
