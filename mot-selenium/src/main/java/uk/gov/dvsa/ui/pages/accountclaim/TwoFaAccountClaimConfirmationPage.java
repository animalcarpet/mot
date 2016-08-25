package uk.gov.dvsa.ui.pages.accountclaim;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;

import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;
import uk.gov.dvsa.ui.pages.login.LoginPage;

public class TwoFaAccountClaimConfirmationPage extends Page {

    private static final String PAGE_TITLE = "Your account has been claimed";

    @FindBy(id = "go-to-sign-in") private WebElement goToSignIn;

    @FindBy(className = "banner__heading") private WebElement pinHeading;

    @FindBy(className = "lead") private WebElement leadHeading;

    @FindBy(className = "text") private WebElement pageContentText;

    public TwoFaAccountClaimConfirmationPage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE);
    }

    public LoginPage goToSignIn() {
        goToSignIn.click();
        return new LoginPage(driver);
    }

    public String getPinHeadingText() {
        return pinHeading.getText();
    }

    public String getLeadHeadingText() {
        return leadHeading.getText();
    }

    public String getPageContentText() {
        return pageContentText.getText();
    }
}
