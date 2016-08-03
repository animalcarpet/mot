package uk.gov.dvsa.ui.pages.mot;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.FormDataHelper;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;

public class EnforcementAbortPage extends Page {

    private static String PAGE_TITLE = "Abort MOT test";
    @FindBy (id = "reasonForAbort") private WebElement reasonForAbortText;
    @FindBy (id = "mot_test_abort_confirm") private WebElement confirmButton;

    public EnforcementAbortPage(MotAppDriver driver) {
        super(driver);
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE);
    }

    public EnforcementAbortPage enterReasonForAbort(String reason){
        FormDataHelper.enterText(reasonForAbortText, reason);
        return this;
    }

    public EnforcementAbortPage clickConfirm(){
        confirmButton.click();
        return this;
    }
}
