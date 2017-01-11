package uk.gov.dvsa.ui.pages.login;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.domain.navigation.MotPageFactory;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;
import uk.gov.dvsa.ui.pages.userregistration.CreateAnAccountPage;

public class LoginPage extends Page {

    private static final String PAGE_TITLE = "MOT testing service";

    @FindBy(partialLinkText = "create an account") private WebElement createAnAccountLink;
    @FindBy(xpath = "//*[contains(@id,'_tid1')]") private WebElement userIdInput;
    @FindBy(xpath = "//*[contains(@id,'_tid2')]") private WebElement userPasswordInput;
    @FindBy(name = "Login.Submit") private WebElement submitButton;
    @FindBy(id = "global-cookie-message") private WebElement cookieMessage;

    public LoginPage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE) && createAnAccountLink.isDisplayed();
    }

    public LoginPage refreshPage() throws InterruptedException {
        PageInteractionHelper.refreshPageWhileElementIsVisible(LoginPage.class, cookieMessage);
        return this;
    }

    public CreateAnAccountPage clickCreateAnAccountLink() {
        createAnAccountLink.click();
        return new CreateAnAccountPage(driver);
    }

    public <T extends Page>T loginWithGivenCredentials(Class<T> clazz, String userName, String password) {
        userIdInput.sendKeys(userName);
        userPasswordInput.sendKeys(password);
        submitButton.click();
        return MotPageFactory.newPage(driver, clazz);
    }
}
