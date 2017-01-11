package uk.gov.dvsa.ui.pages.authorisedexaminer;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.domain.model.AeContactDetails;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.FormCompletionHelper;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;

public class AuthorisedExaminerChangeDetailsPage extends Page {

    private static final String PAGE_TITLE = "Change contact details";

    @FindBy(id = "CORRemail") private WebElement correspondenceEmail;

    @FindBy(id = "CORRemailConfirmation")
    private WebElement correspondenceEmailConfirmation;

    @FindBy(id = "CORRphoneNumber") private WebElement correspondencePhoneNumber;

    @FindBy(id = "isCorrContactDetailsSame1")
    private WebElement correspondenceSameAsBusiness;

    @FindBy(id = "submitAeEdit")
    private WebElement saveChanges;

    public AuthorisedExaminerChangeDetailsPage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    public boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE);
    }

    public AuthorisedExaminerChangeDetailsPage fillOutMinimumContactDetails(
        AeContactDetails aeContactDetails) {
        correspondenceEmail.clear();
        correspondenceEmailConfirmation.clear();
        correspondencePhoneNumber.clear();
        correspondenceEmail.sendKeys(aeContactDetails.getEmail());
        correspondenceEmailConfirmation.sendKeys(aeContactDetails.getConfirmationEmail());
        correspondencePhoneNumber.sendKeys(aeContactDetails.getTelephoneNumber());
        FormCompletionHelper.selectInputBox(correspondenceSameAsBusiness);
        return this;
    }

    public AuthorisedExaminerPage saveContactDetailChanges() {
        saveChanges.click();
        return new AuthorisedExaminerPage(driver);
    }
}
