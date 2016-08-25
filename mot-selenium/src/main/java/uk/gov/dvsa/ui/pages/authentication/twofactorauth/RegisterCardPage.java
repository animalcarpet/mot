package uk.gov.dvsa.ui.pages.authentication.twofactorauth;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.FormDataHelper;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;
import uk.gov.dvsa.ui.pages.exception.BreadcrumbNotFoundException;

public class RegisterCardPage extends Page {

    private static final String PAGE_TITLE = "Activate your security card";
    public static final String PATH = "/register-card";

    @FindBy(id = "serial_number") private WebElement serialNumberElement;
    @FindBy(id = "pin") private WebElement pinNumber;
    @FindBy(id = "activate_cta") private WebElement submitButton;
    @FindBy(id = "skip_cta") private WebElement skipLink;
    @FindBy(id = "validation-summary-id") private WebElement validationSummaryBox;

    public RegisterCardPage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE) && verifyBreadcrumb();
    }

    protected boolean verifyBreadcrumb() {
        if (getBreadcrumb().asList().isEmpty()) {
            throw new BreadcrumbNotFoundException(this.toString());
        }

        return true;
    }

    public void enterSerialNumber(String serialNumber){
        FormDataHelper.enterText(serialNumberElement, serialNumber);
    }

    public void enterPin(String pin){
        FormDataHelper.enterText(pinNumber, pin);
    }

    public void continueButton(){
        submitButton.click();
    }

    public void clickSkipActivationLink(){
        skipLink.click();
    }

    public boolean isValidationSummaryBoxDisplayed(){
        return PageInteractionHelper.isElementDisplayed(validationSummaryBox);
    }

}
