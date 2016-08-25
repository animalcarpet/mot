package uk.gov.dvsa.ui.pages.authentication.securitycard;

import org.openqa.selenium.By;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;

public class OrderNewCardPage extends Page {
    private static final String PAGE_TITLE = "Order a security card";
    public static final String PATH= "/security-card-order/new";
    public static final String CSCO_PATH= "/security-card-order/new/%s";

    private By continueButton = By.id("orderContinue");
    @FindBy(id = "existing-card-message") private WebElement existingCardMessage;

    public OrderNewCardPage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(getTitle(), PAGE_TITLE);
    }

    public EnterSecurityCardAddressPage continueToAddressPage(){
        driver.findElement(continueButton).click();
        return new EnterSecurityCardAddressPage(driver);
    }

    public boolean isCardDeactivationMessageDisplayed() {
        return PageInteractionHelper.isElementDisplayed(existingCardMessage);
    }
}
