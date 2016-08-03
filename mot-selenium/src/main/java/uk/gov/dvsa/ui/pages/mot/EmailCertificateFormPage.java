package uk.gov.dvsa.ui.pages.mot;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.helper.FormDataHelper;
import uk.gov.dvsa.ui.pages.Page;

public class EmailCertificateFormPage extends Page {

    private static final String PAGE_TITLE = "Email certificate";

    @FindBy (name = "firstName") private WebElement firstNameTextField;
    @FindBy (name = "familyName") private WebElement lastNameTextField;
    @FindBy (name = "email") private WebElement emailAddressTextField;
    @FindBy (name = "retypeEmail") private WebElement retypeEmailAddressTextField;
    @FindBy (id = "email-certificate") private WebElement emailCertificateButton;

    public EmailCertificateFormPage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    public boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(getTitle(), PAGE_TITLE);
    }

    public EmailCertificateFormPage completeEmailDetailsWithPassValues(String firstName, String lastName, String emailAddress) {
        FormDataHelper.enterText(firstNameTextField, firstName);
        FormDataHelper.enterText(lastNameTextField, lastName);
        FormDataHelper.enterText(emailAddressTextField, emailAddress);
        return this;
    }

    public EmailCertificateConfirmationPage goToEmailCertificateConfirmationPage() {
        emailCertificateButton.click();
        return new EmailCertificateConfirmationPage(driver);
    }
}