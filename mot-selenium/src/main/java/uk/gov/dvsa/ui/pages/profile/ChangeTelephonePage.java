package uk.gov.dvsa.ui.pages.profile;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;

public class ChangeTelephonePage extends Page{

    private static final String PAGE_TITLE = "Change telephone number";

    @FindBy (id = "personTelephone") private WebElement phoneNumberInput;
    @FindBy (id = "submit") private WebElement submitTelephoneChangeButton;
    @FindBy (id = "cancel-and-return") private WebElement cancelAndReturnLink;
    @FindBy (id = "validation-summary-id") private WebElement validationMessage;

    public ChangeTelephonePage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE);
    }

    public String getValidationMessage() {
        return validationMessage.getText();
    }
}
