package uk.gov.dvsa.ui.pages.userregistration;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;
import uk.gov.dvsa.ui.pages.login.LoginPage;

public class AccountCreatedPage extends Page {

    private static final String PAGE_TITLE = "Now check your email";

    @FindBy (id = "successBanner") private WebElement accountCreatedText;

    @FindBy(partialLinkText = "Sign in") private WebElement signInLink;

    public AccountCreatedPage(MotAppDriver driver){
        super(driver);
        selfVerify();
    }

    @Override
    public boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(getTitle(), PAGE_TITLE);
    }

    public LoginPage clickBackToSignInPage() {
        signInLink.click();
        return new LoginPage(driver);
    }

    public boolean isAccountCreatedTextDisplayed(){
        return accountCreatedText.isDisplayed();
    }

}
