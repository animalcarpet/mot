package uk.gov.dvsa.ui.pages.profile.qualificationdetails;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.FormDataHelper;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;

public class QualificationDetailsGroupAPage extends Page {

    private static final String PAGE_TITLE = "Change a certificate";

    @FindBy (name = "cert-number") private WebElement certificateNumber;
    @FindBy (name = "date-day") private WebElement dateDay;
    @FindBy (name = "date-month") private WebElement dateMonth;
    @FindBy (name = "date-year") private WebElement dateYear;
    @FindBy (name = "vts-id") private WebElement vtsId;
    @FindBy (id = "submit-button") private WebElement submit;

    public QualificationDetailsGroupAPage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE);
    }

    public QualificationDetailsGroupAPage fillDate(String day, String month, String year) {
        FormDataHelper.enterText(dateDay, day);
        FormDataHelper.enterText(dateMonth, month);
        FormDataHelper.enterText(dateYear, year);
        return this;
    }

    public QualificationDetailsGroupAPage fillCertificateNumber(String number) {
        FormDataHelper.enterText(certificateNumber, number);
        return this;
    }
    public QualificationDetailsGroupAPage fillVtsId(String id) {
        FormDataHelper.enterText(vtsId, id);
        return this;
    }

    public QualificationDetailsConfirmationPage submitAndGoToConfirmationPage(){
        submit.click();
        return new QualificationDetailsConfirmationPage(driver);
    }
}
