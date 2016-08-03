package uk.gov.dvsa.ui.pages.cpms;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.domain.navigation.MotPageFactory;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;

public class SaveCardConfirmationPage extends CpmsMainPage {

    @FindBy(id = "scp_storeCardConfirmationPage_buttons_cancel_button") private WebElement cancelButton;

    public SaveCardConfirmationPage(MotAppDriver driver) {
        super(driver);
    }

    public CardPaymentConfirmationPage clickCancelButton() {
        cancelButton.click();
        return new CardPaymentConfirmationPage(driver);
    }
}
