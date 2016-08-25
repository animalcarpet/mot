package uk.gov.dvsa.ui.pages.authentication.securitycard.lost_or_forgotten;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.ui.pages.HomePage;
import uk.gov.dvsa.ui.pages.authentication.securitycard.OrderNewCardPage;

public class LostForgottenCardConfirmationPage extends AbstractLostForgottenPage {
    private static final String PAGE_TITLE = "You have signed in without your security card";
    public static final String PATH = "/lost-or-forgotten-card/confirmation";

    @FindBy(id = "continueToHome") private WebElement continueToHome;
    @FindBy(id = "orderCard") private WebElement orderCardLink;

    public LostForgottenCardConfirmationPage(final MotAppDriver driver) {
        super(driver, PAGE_TITLE);
    }

    public HomePage continueToHome(){
        continueToHome.click();
        return new HomePage(driver);
    }


    public OrderNewCardPage orderSecurityCard() {
        orderCardLink.click();
        return new OrderNewCardPage(driver);
    }
}
