package uk.gov.dvsa.ui.pages.cpms;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.domain.navigation.MotPageFactory;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.FormDataHelper;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;

public class SlotAdjustmentPage extends Page {

    private static final String PAGE_TITLE = "Slot adjustment";

    @FindBy(id = "inputAdjustmentType") private WebElement addSlots;
    @FindBy(xpath = "//input[@value='negative']") private WebElement removeSlots;
    @FindBy(id = "input_slots_number") private WebElement slotsNumber;
    @FindBy(id = "inputReason") private WebElement reasonForAdjustment;
    @FindBy(id = "inputComment") private WebElement adjustmentComment;
    @FindBy(id = "startAdjust") private WebElement reviewSlotAdjustment;
    @FindBy(id = "validation-message--failure") private WebElement errorMessage;

    public SlotAdjustmentPage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE);
    }

    public SlotAdjustmentPage adjustSlots(String adjustmentAmount, String comment, boolean isPositive) {
        String adjustmentReason;
        if (isPositive) {
            adjustmentReason = "Top-up DVSA Garage";
            addSlots.click();
        } else {
            adjustmentReason = "Reconciliation";
            removeSlots.click();
        }
        FormDataHelper.enterText(slotsNumber, adjustmentAmount);
        FormDataHelper.selectFromDropDownByVisibleText(reasonForAdjustment, adjustmentReason);
        FormDataHelper.enterText(adjustmentComment, comment);
        return this;
    }

    public boolean isErrorMessageDisplayed() {
        return errorMessage.isDisplayed();
    }

    public <T extends Page> T reviewAdjustment(Class<T> clazz) {
        reviewSlotAdjustment.click();
        return MotPageFactory.newPage(driver, clazz);
    }
}
