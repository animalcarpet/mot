package uk.gov.dvsa.ui.pages.specialnotices;

import org.joda.time.DateTime;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.FormDataHelper;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;

public class SpecialNoticeCreationPage extends Page{

    public static final String PATH = "/special-notices/create";
    private static final String PAGE_TITLE = "Create Special Notice";

    @FindBy(id = "notice-title-input") private WebElement subjectTitleInput;

    @FindBy(name = "internalPublishDate[day]") private WebElement internalDateDayInput;
    @FindBy(name = "internalPublishDate[month]") private WebElement internalDateMonthInput;
    @FindBy(name = "internalPublishDate[year]") private WebElement internalDateYearInput;

    @FindBy(name = "externalPublishDate[day]") private WebElement externalDateDayInput;
    @FindBy(name = "externalPublishDate[month]") private WebElement externalDateMonthInput;
    @FindBy(name = "externalPublishDate[year]") private WebElement externalDateYearInput;

    @FindBy(css = "input[value='TESTER-CLASS-1']") private WebElement vehicleClass1CheckBox;
    @FindBy(css = "input[value='TESTER-CLASS-4']") private WebElement vehicleClass4CheckBox;
    @FindBy(css = "input[value='DVSA']") private WebElement dvsaRolesCheckBox;
    @FindBy(css = "input[value='VTS']") private WebElement vtsRolesCheckbox;

    @FindBy(id = "acknowledgement-input") private WebElement acknowledgementPeriodInput;

    @FindBy(id = "notice-text-input") private WebElement noticeTextInput;
    @FindBy(id = "preview-special-notice") private WebElement previewSpecialNoticeButton;

    public SpecialNoticeCreationPage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    public boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE);
    }

    private SpecialNoticeCreationPage enterInternalPublishDate(DateTime dateTime) {
        FormDataHelper.enterText(internalDateDayInput, dateTime.toString("dd"));
        FormDataHelper.enterText(internalDateMonthInput, dateTime.toString("MM"));
        FormDataHelper.enterText(internalDateYearInput, dateTime.toString("YYYY"));
        return this;
    }
    
    private SpecialNoticeCreationPage enterExternalPublishDate(DateTime dateTime) {
        FormDataHelper.enterText(externalDateDayInput, dateTime.toString("dd"));
        FormDataHelper.enterText(externalDateMonthInput, dateTime.toString("MM"));
        FormDataHelper.enterText(externalDateYearInput, dateTime.toString("YYYY"));
        return this;
    }

    public SpecialNoticePreviewPage createSpecialNoticeSuccessfully(String specialNoticeTitle) {
        FormDataHelper.enterText(subjectTitleInput, specialNoticeTitle);
        enterExternalPublishDate(DateTime.now());
        enterInternalPublishDate(DateTime.now());
        FormDataHelper.enterText(acknowledgementPeriodInput, "12");
        FormDataHelper.selectInputBox(vehicleClass1CheckBox);
        FormDataHelper.selectInputBox(vehicleClass4CheckBox);
        FormDataHelper.selectInputBox(dvsaRolesCheckBox);
        FormDataHelper.selectInputBox(vtsRolesCheckbox);
        FormDataHelper.enterText(noticeTextInput, "#Testing the Future \n *Mark down*");
        previewSpecialNoticeButton.click();
        return new SpecialNoticePreviewPage(driver);
    }

}
