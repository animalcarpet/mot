package uk.gov.dvsa.ui.pages.mot;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;

public class AbortMotTestPage extends Page {

    private static final String PAGE_TITLE = "Vehicle Testing Station\n" +
            "Abort MOT test";
    @FindBy(id = "sln-action-abort") private WebElement abortMotTestButton;
    @FindBy(id = "reasonForCancel-25") private WebElement abortedByVe;

    public AbortMotTestPage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE);
    }

    public MotTestAbortedPage clickAbortMotTestButton(){
        abortMotTestButton.click();

        return new MotTestAbortedPage(driver);
    }

    public AbortMotTestPage selectAbortedByVeReason() {
        abortedByVe.click();

        return this;
    }
}
