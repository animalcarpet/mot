package uk.gov.dvsa.ui.pages.authentication.securitycard;

import org.openqa.selenium.By;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.HomePage;
import uk.gov.dvsa.ui.pages.Page;

public class ConfirmSecurityCardOrderPage extends Page {
    private static final String PAGE_TITLE = "Your security card has been ordered";
    public static final String PATH= "/security-card/confirmation";

    private By continueToHome = By.id("continueToHome");
    private By orderStatusMessage = By.className("transaction-header__title");

    public ConfirmSecurityCardOrderPage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(getTitle(), PAGE_TITLE);
    }

    public HomePage continueToHome(){
        driver.findElement(continueToHome).click();
        return new HomePage(driver);
    }

    public String orderStatusMessage(){
        return driver.findElement(orderStatusMessage).getText();
    }
}
