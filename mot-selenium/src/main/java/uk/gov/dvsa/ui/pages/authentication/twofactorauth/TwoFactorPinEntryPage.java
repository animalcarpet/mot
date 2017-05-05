package uk.gov.dvsa.ui.pages.authentication.twofactorauth;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.domain.navigation.MotPageFactory;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.FormDataHelper;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;
import uk.gov.dvsa.ui.pages.authentication.securitycard.lost_or_forgotten.LostForgottenCardSignInPage;

public class TwoFactorPinEntryPage extends Page {

    public static final String PATH = "/login-2fa";

    @FindBy (id = "pin") private WebElement pinBox;
    @FindBy (css = "a[href*='lost-or-forgotten-card']") private WebElement forgottenLink;
    @FindBy (name = "Login.Submit") private WebElement signInButton;
    @FindBy (id = "cancelAndReturnToSignIn") private WebElement returnToSignInLink;

    public TwoFactorPinEntryPage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.isElementDisplayed(pinBox);
    }

    public TwoFactorPinEntryPage enterTwoFactorPin(String pin){
        FormDataHelper.enterText(pinBox, pin);
        return this;
    }

    public LostForgottenCardSignInPage clickLostForgottenLink() {
        forgottenLink.click();
        return new LostForgottenCardSignInPage(driver);
    }

    public void clickSignIn(){
        signInButton.click();
    }

    public <T extends Page> T enterTwoFactorPinAndSubmit(String pin, Class<T> clazz) {
        enterTwoFactorPin(pin);
        clickSignIn();
        return MotPageFactory.newPage(driver, clazz);
    }
}