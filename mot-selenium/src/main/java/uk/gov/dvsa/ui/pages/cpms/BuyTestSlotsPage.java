package uk.gov.dvsa.ui.pages.cpms;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;

import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;

public class BuyTestSlotsPage extends Page {
    private static final String PAGE_TITLE = "Buy test slots";
    
    @FindBy(id = "input_slots_number") private WebElement slotsRequired;
    @FindBy(id = "calculateCost") private WebElement calculateCostButton;
    @FindBy(id = "validationError") private WebElement exceedsMaximumBalanceErrorMessage;

    public BuyTestSlotsPage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE);
    }
    
    public BuyTestSlotsPage enterSlotsRequired(int slots) {
        slotsRequired.sendKeys(Integer.toString(slots));
        return new BuyTestSlotsPage(driver);
    }
    
    public OrderSummaryPage clickCalculateCostButton() {
        calculateCostButton.click();
        return new OrderSummaryPage(driver);
    }

    public boolean isSlotsRequiredVisible() {
        return slotsRequired.isDisplayed();
    }

    public boolean isCalculateCostButtonVisible() {
        return calculateCostButton.isDisplayed();
    }
}
