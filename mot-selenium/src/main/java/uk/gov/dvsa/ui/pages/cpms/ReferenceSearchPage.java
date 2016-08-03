package uk.gov.dvsa.ui.pages.cpms;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.domain.navigation.MotPageFactory;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.FormDataHelper;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;

public class ReferenceSearchPage extends Page {
    private static final String PAGE_TITLE = "Reference search";
    public static final String PATH = "/payment/search";

    @FindBy(xpath = "//*[contains(text(), 'Payment reference')]/input") private WebElement paymentReferenceButton;
    @FindBy(xpath = "//*[contains(text(), 'Invoice reference')]/input") private WebElement invoiceReferenceButton;
    @FindBy(id = "inputReference") private WebElement referenceInputElement;
    @FindBy(id = "submitAeSearch") private WebElement searchButton;

    public ReferenceSearchPage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE);
    }

    public ReferenceSearchPage choosePaymentReference() {
        paymentReferenceButton.click();
        return this;
    }

    public ReferenceSearchPage chooseInvoiceReference() {
        invoiceReferenceButton.click();
        return this;
    }

    public ReferenceSearchResultsPage searchForReference(String value) {
        FormDataHelper.enterText(referenceInputElement, value);
        searchButton.click();
        return new ReferenceSearchResultsPage(driver);
    }
}
