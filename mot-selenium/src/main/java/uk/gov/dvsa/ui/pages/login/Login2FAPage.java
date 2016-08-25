package uk.gov.dvsa.ui.pages.login;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.domain.navigation.MotPageFactory;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;

public class Login2FAPage extends Page{

    private static final String PAGE_TITLE = "MOT testing service";
    public static final String PATH = "/";

    @FindBy(id = "forgotten-security-card") private WebElement forgottenSecurityCard;
    @FindBy(id = "my-security-card-is-lost-or-damaged") private WebElement securityCardLostOrDamaged;
    @FindBy(xpath = "//*[contains(@id,'_tid1')]") private WebElement pinNumberInput;
    @FindBy(name = "Login.Submit") private WebElement submitButton;

    public Login2FAPage(MotAppDriver driver){
        super(driver);
        selfVerify();
    }

    @Override
    public boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE) && forgottenSecurityCard.isDisplayed();
    }

    public <T extends Page>T login(String pinNumber, Class<T> clazz) {
        pinNumberInput.sendKeys(pinNumber);
        submitButton.click();
        return MotPageFactory.newPage(driver, clazz);
    }

}
