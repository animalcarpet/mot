package uk.gov.dvsa.ui.pages.cpms;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.domain.navigation.MotPageFactory;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.FormDataHelper;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;

public class CardAdditionalInformationPage extends CpmsMainPage {

    @FindBy(id = "scp_additionalInformationPage_buttons_continue_button") private WebElement continueButton;
    @FindBy(id = "scp_additionalInformationPage_buttons_back_button") private WebElement backButton;
    @FindBy(id = "scp_additionalInformationPage_buttons_reset_button") private WebElement resetButton;
    @FindBy(id = "scp_additionalInformationPage_cardholderName_input") private WebElement cardHolderNameInput;

    public CardAdditionalInformationPage(MotAppDriver driver) {
        super(driver);
    }

    public CardAdditionalInformationPage enterCardHolderName() {
        FormDataHelper.enterText(cardHolderNameInput, "AEDM");
        return this;
    }

    public PaymentConfirmationPage clickContinueButton() {
        continueButton.click();
        return new PaymentConfirmationPage(driver);
    }
}
