package uk.gov.dvsa.ui.pages.cpms;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;

import uk.gov.dvsa.domain.navigation.MotPageFactory;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.FormDataHelper;

public class CardDetailsPage extends CpmsMainPage {

    @FindBy(id = "scp_cardPage_cardNumber_input") private WebElement cardNumber;
    @FindBy(id = "scp_cardPage_expiryDate_input") private WebElement expiryMonth;
    @FindBy(id = "scp_cardPage_expiryDate_input2") private WebElement expiryYear;
    @FindBy(id = "issueNumber") private WebElement issueNumber;
    @FindBy(id = "startMonth") private WebElement startMonth;
    @FindBy(id = "startYear") private WebElement startYear;
    @FindBy(id = "scp_cardPage_csc_input") private WebElement securityCode;
    @FindBy(id = "scp_cardPage_buttonsNoBack_continue_button") private WebElement continueButton;
    @FindBy(id = "scp_customer_framework_cancelLink") private WebElement cancelButton;
    @FindBy(id = "scp_additionalInformationPage_cardholderName_input") private WebElement cardHolderNameInput;

    public CardDetailsPage(MotAppDriver driver) {
        super(driver);
    }

    public CardDetailsPage enterCardDetails() {
        FormDataHelper.enterText(cardNumber, "4006000000000600");
        enterExpiryDate();
        FormDataHelper.enterText(securityCode, "654");
        return this;
    }

    public CardDetailsPage enterExpiryDate() {
        FormDataHelper.enterText(expiryMonth, "12");
        FormDataHelper.enterText(expiryYear, "18");
        return this;
    }

    public CardAdditionalInformationPage clickContinueButton() {
        continueButton.click();
        return new CardAdditionalInformationPage(driver);
    }

    public BuyTestSlotsPage clickCancelButton() {
        cancelButton.click();
        return new BuyTestSlotsPage(driver);
    }
}
