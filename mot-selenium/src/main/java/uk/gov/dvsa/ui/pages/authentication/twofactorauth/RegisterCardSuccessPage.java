package uk.gov.dvsa.ui.pages.authentication.twofactorauth;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.HomePage;
import uk.gov.dvsa.ui.pages.Page;

public class RegisterCardSuccessPage extends Page {

    private static final String PAGE_TITLE = "Your security card has been activated";
    public static final String PATH = "/register-card/success";

    @FindBy(id = "home-link") private WebElement homeLink;
    @FindBy(className = "transaction-header__title") private WebElement confirmationBanner;
    @FindBy(css = "#role-nominations a") private WebElement nominationsList;
    @FindBy(className = "button") private WebElement continueToHomePage;

    public RegisterCardSuccessPage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE);
    }

    public String getConfirmationText(){
        return confirmationBanner.getText();
    }

    public HomePage continueToHomePage() {
        continueToHomePage.click();
        return new HomePage(driver);
    }
}
