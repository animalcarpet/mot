package uk.gov.dvsa.ui.pages.cpms;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;

public class TransactionDetailsPage extends Page {
    private static final String PAGE_TITLE = "Transaction details";

    @FindBy(id = "startChargeback") private WebElement reverseThisPaymentButton;
    @FindBy(id = "invoiceNumber") private WebElement invoiceNumber;
    @FindBy(id = "receiptReference") private WebElement paymentReference;
    @FindBy(id = "supplierDetails") private WebElement supplierDetails;
    @FindBy(id = "purchaserDetails") private WebElement purchaserDetails;
    @FindBy(id = "paymentDetails") private WebElement paymentDetails;
    @FindBy(id = "orderDetails") private WebElement orderDetails;


    public TransactionDetailsPage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE);
    }

    public ReversePaymentSummaryPage clickReverseThisPaymentButton() {
        reverseThisPaymentButton.click();
        return new ReversePaymentSummaryPage(driver);
    }

    public String getInvoiceNumber() {
        return invoiceNumber.getText();
    }

    public String getPaymentReference() {
        return paymentReference.getText();
    }

    public String getSupplierDetailsText() {
        return supplierDetails.getText();
    }

    public String getPurchaserDetailsText() {
        return purchaserDetails.getText();
    }

    public String getPaymentDetailsText() {
        return paymentDetails.getText();
    }

    public String getOrderDetailsText() {
        return orderDetails.getText();
    }
}
