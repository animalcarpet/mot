package uk.gov.dvsa.ui.pages.mot;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;

public class TestShortSummaryPage extends Page {

    private static final String PAGE_TITLE = "Vehicle Testing Station\n" +
            "MOT Test";
    @FindBy(id = "sln-action-abort") private WebElement abortMotTestButton;
    @FindBy(id = "reasonForCancel-25") private WebElement abortedByVe;
    @FindBy(id = "confirmationTitle") private WebElement confirmationMessage;

    public TestShortSummaryPage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE);
    }

    public TestShortSummaryPage clickAbortMotTestButton(){
        abortMotTestButton.click();

        return this;
    }

    public TestShortSummaryPage selectAbortedByVeReason() {
        abortedByVe.click();

        return this;
    }

    public boolean isTestAbortedSuccessfully() {
        return confirmationMessage.getText().contains("MOT test successfully aborted");
    }
}

