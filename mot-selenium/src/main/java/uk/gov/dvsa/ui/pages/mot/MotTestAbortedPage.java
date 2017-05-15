package uk.gov.dvsa.ui.pages.mot;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;

public class MotTestAbortedPage extends Page {

    private static final String PAGE_TITLE = "Vehicle Testing Station\n" +
            "MOT test aborted";
    @FindBy(id = "sln-action-abort") private WebElement abortMotTestButton;
    @FindBy(id = "reasonForCancel-25") private WebElement abortedByVe;
    @FindBy(id = "print-abort-certificate") private WebElement printAbortCertificateButton;

    public MotTestAbortedPage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE);
    }

    public boolean isTestAbortedSuccessfully() {
        return printAbortCertificateButton.isDisplayed();
    }
}
