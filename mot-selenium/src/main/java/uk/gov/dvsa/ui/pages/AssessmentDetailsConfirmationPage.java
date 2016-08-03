package uk.gov.dvsa.ui.pages;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.PageInteractionHelper;

public class AssessmentDetailsConfirmationPage extends Page {

    private static final String PAGE_TITLE = "Assessment details saved";

    @FindBy (id = "validation-message--success") private WebElement validationMsgSuccess;

    public AssessmentDetailsConfirmationPage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE);
    }

    public String getValidationMessageText() {
        return validationMsgSuccess.getText();
    }
}
