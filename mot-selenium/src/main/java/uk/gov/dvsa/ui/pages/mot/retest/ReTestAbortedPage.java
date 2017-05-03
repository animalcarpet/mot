package uk.gov.dvsa.ui.pages.mot.retest;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;

import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;

public class ReTestAbortedPage extends Page {

    @FindBy(className = "col-lg-6") private WebElement vtsMessage;
    @FindBy(id = "cancel_test_result") private WebElement finishButton;
    @FindBy (id = "reprint-certificate") private WebElement printDocumentsButton;

    private static final String PAGE_TITLE = "MOT re-test aborted";

    public ReTestAbortedPage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE);
    }

    public void clickFinishButton() {
        finishButton.click();
    }

    public void clickPrintDocumentsButton() {
        printDocumentsButton.click();
    }

    public boolean isVt30MessageDisplayed() {
        return vtsMessage.getText().contains("The VT30 has been generated");
    }
}
