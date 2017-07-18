package uk.gov.dvsa.ui.pages.cpms;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;

import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;

public class OrderSummaryPage extends Page {
    private static final String PAGE_TITLE = "Order summary";
    
    @FindBy(id = "payByCard") private WebElement continueButton;

    public OrderSummaryPage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE);
    }
    
    public CardDetailsPage clickContinueToPay() {
        continueButton.click();

        return new CardDetailsPage(driver);
    }
}
