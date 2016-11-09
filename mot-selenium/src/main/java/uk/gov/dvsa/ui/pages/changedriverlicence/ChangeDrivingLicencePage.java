package uk.gov.dvsa.ui.pages.changedriverlicence;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.domain.navigation.MotPageFactory;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.FormDataHelper;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;

public class ChangeDrivingLicencePage extends Page {

    private static final String PAGE_TITLE = "User profile\nChange driving licence";
    public static final String PATH = "/user-admin/user-profile/%s/driving-licence";

    @FindBy(linkText = "Review driving licence") private WebElement reviewDrivingLicenceButton;
    @FindBy(linkText = "Cancel and return to user profile") private WebElement backToUserProfileLink;
    @FindBy(id = "submitDrivingLicence") private WebElement submitDrivingLicenceButton;
    @FindBy(id = "drivingLicenceNumber") private WebElement drivingLicenceNumberInput;
    @FindBy(id = "drivingLicenceRegionGB") private WebElement gbDrivingLicenceRadioButton;
    @FindBy(id = "drivingLicenceRegionNI") private WebElement nIDrivingLicenceRadioButton;
    @FindBy(id = "drivingLicenceRegionNU") private WebElement nonUkDrivingLicenceRadioButton;
    @FindBy(id = "validation-summary-id") private WebElement validationSummary;
    @FindBy(id = "delete-driving-licence") private WebElement deleteDrivingLicence;

    public ChangeDrivingLicencePage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(getTitle(), PAGE_TITLE);
    }

    public ReviewDrivingLicencePage clickReviewDrivingLicenceButton() {
        reviewDrivingLicenceButton.click();
        return new ReviewDrivingLicencePage(driver);
    }

    public <T extends Page>T clickSubmitDrivingLicenceButton(Class<T> clazz) {
        submitDrivingLicenceButton.click();
        return MotPageFactory.newPage(driver, clazz);
    }

    public RemoveDriverLicencePage clickRemoveDrivingLicenceLink()
    {
        deleteDrivingLicence.click();
        return new RemoveDriverLicencePage(driver);
    }

    public ChangeDrivingLicencePage enterDriverLicenceNumber(String number) {
        FormDataHelper.enterText(drivingLicenceNumberInput, number);
        return this;
    }

    public ChangeDrivingLicencePage selectDlIssuingCountry(String key) {
        switch (key) {
            case "GB":
                gbDrivingLicenceRadioButton.click();
                break;
            case "NI":
                nIDrivingLicenceRadioButton.click();
                break;
            case "NU":
                nonUkDrivingLicenceRadioButton.click();
                break;
            default:
                break;
        }
        return this;
    }

    public ChangeDrivingLicencePage setInvalidDlIssuingCountry() {
        // Select GB as the issuing country
        gbDrivingLicenceRadioButton.click();

        // Set the value attribute to 0, which is invalid
        PageInteractionHelper.executeJavascript(
                "arguments[0].setAttribute(arguments[1], arguments[2]);",
                gbDrivingLicenceRadioButton,
                "value",
                "0"
        );

        return this;
    }

    public String getValidationSummary() {
        return validationSummary.getText();
    }
}
