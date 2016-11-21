package uk.gov.dvsa.ui.pages.mot.certificates;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;

public class ReplacementCertificateUpdateSuccessfulPage extends Page {

    private static final String PAGE_TITLE = "Test Results Updated Successfully";
    @FindBy (id = "print") private WebElement printButton;

    public ReplacementCertificateUpdateSuccessfulPage(MotAppDriver driver) {
        super(driver);
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE);
    }

    public boolean isPrintButtonDisplayed(){
        return printButton.isDisplayed();
    }
}
