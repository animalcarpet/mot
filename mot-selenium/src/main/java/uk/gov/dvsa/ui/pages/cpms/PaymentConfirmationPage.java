package uk.gov.dvsa.ui.pages.cpms;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.domain.navigation.MotPageFactory;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;

public class PaymentConfirmationPage extends CpmsMainPage {

    @FindBy(id = "scp_confirmationPage_buttons_payment_button") private WebElement makePaymentButton;
    @FindBy(id = "scp_confirmationPage_buttons_back_button") private WebElement backButton;

    public PaymentConfirmationPage(MotAppDriver driver) {
        super(driver);
    }

    public SaveCardConfirmationPage clickMakePaymentButton() {
        makePaymentButton.click();
        return new SaveCardConfirmationPage(driver);
    }

    public CardPaymentConfirmationPage clickMakePaymentButtonAsFinance() {
        makePaymentButton.click();
        return new CardPaymentConfirmationPage(driver);
    }
}
