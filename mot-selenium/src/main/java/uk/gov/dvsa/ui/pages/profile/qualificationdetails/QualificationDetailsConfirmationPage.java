package uk.gov.dvsa.ui.pages.profile.qualificationdetails;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;

public class QualificationDetailsConfirmationPage extends Page {

    private static final String PAGE_TITLE = "Review certificate and demo test details";

    @FindBy (id = "confirm-button") private WebElement submit;

    public QualificationDetailsConfirmationPage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE);
    }

    public QualificationDetailsPage submitConfirmChanges(){
        submit.click();
        return new QualificationDetailsPage(driver);
    }
}
