package uk.gov.dvsa.ui.pages.cpms;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;

import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;
import uk.gov.dvsa.ui.pages.authorisedexaminer.FinanceAuthorisedExaminerViewPage;

public class CardPaymentConfirmationPage extends Page {

    private static final String PAGE_TITLE = "Payment confirmation";
    
    @FindBy(id = "successMessage") private WebElement successfulMessage;
    @FindBy(id = "viewDetails") private WebElement viewPaymentDetailsLink;
    @FindBy(id = "backToExaminer") private WebElement backToAeLink;

    public CardPaymentConfirmationPage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE);
    }
    
    public boolean isPaymentSuccessfulMessageDisplayed() {
        return successfulMessage.isDisplayed();
    }
    
    public String getPaymentStatusMessage() {
        return successfulMessage.getText();
    }

    public TransactionDetailsPage clickViewPaymentDetailslink() {
        viewPaymentDetailsLink.click();
        return new TransactionDetailsPage(driver);
    }

    public FinanceAuthorisedExaminerViewPage clickBackToAuthorisedExaminerLink() {
        backToAeLink.click();
        return new FinanceAuthorisedExaminerViewPage(driver);
    }
}
