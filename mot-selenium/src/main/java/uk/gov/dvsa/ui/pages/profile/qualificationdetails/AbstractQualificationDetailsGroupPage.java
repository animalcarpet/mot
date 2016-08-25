package uk.gov.dvsa.ui.pages.profile.qualificationdetails;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;

import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.FormDataHelper;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;

public abstract class AbstractQualificationDetailsGroupPage extends Page {
    private String page_title = " ";

    @FindBy(name = "cert-number") protected WebElement certificateNumber;
    @FindBy (name = "date-day") protected WebElement dateDay;
    @FindBy (name = "date-month") protected WebElement dateMonth;
    @FindBy (name = "date-year") protected WebElement dateYear;
    @FindBy (name = "vts-id") protected WebElement vtsId;
    @FindBy (id = "submit-button") protected WebElement submit;

    public AbstractQualificationDetailsGroupPage(MotAppDriver driver, String page_title ) {
        super(driver);
        this.page_title = page_title;
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), page_title);
    }

    public AbstractQualificationDetailsGroupPage fillDate(String day, String month, String year) {
        FormDataHelper.enterText(dateDay, day);
        FormDataHelper.enterText(dateMonth, month);
        FormDataHelper.enterText(dateYear, year);
        return this;
    }

    public AbstractQualificationDetailsGroupPage fillCertificateNumber(String number) {
        FormDataHelper.enterText(certificateNumber, number);
        return this;
    }

    public AbstractQualificationDetailsGroupPage fillVtsId(String id) {
        FormDataHelper.enterText(vtsId, id);
        return this;
    }

    public QualificationDetailsReviewPage submitAndGoToReviewPage(){
        submit.click();
        return new QualificationDetailsReviewPage(driver);
    }
}
