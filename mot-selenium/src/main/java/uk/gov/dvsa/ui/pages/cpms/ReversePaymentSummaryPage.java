package uk.gov.dvsa.ui.pages.cpms;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.FormDataHelper;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;

public class ReversePaymentSummaryPage extends Page {
    private static final String PAGE_TITLE = "Reverse payment";

    @FindBy(id = "inputReason") private WebElement inputReason;
    @FindBy(id = "startRefund") private WebElement reverseThisPaymentButton;

    public ReversePaymentSummaryPage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE);
    }

    public PaymentReversalConfirmationPage selectReasonAndConfirmPaymentReverse(String reversalReason) {
        FormDataHelper.selectFromDropDownByVisibleText(inputReason, reversalReason);
        reverseThisPaymentButton.click();
        return new PaymentReversalConfirmationPage(driver);
    }
}
