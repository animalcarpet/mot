package uk.gov.dvsa.ui.pages.mot.certificates;

import uk.gov.dvsa.domain.model.vehicle.CountryOfRegistration;
import uk.gov.dvsa.domain.navigation.MotPageFactory;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.FormDataHelper;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;

public class ReplacementCertificateUpdatePage extends Page {
    private static String PAGE_TITLE = "Replacement certificate update";

    @FindBy(id = "cancelMotTest") private WebElement cancelEdit;

    @FindBy(id = "dashboard-section-toggler-make") private WebElement editMakeButton;
    @FindBy(id = "dashboard-section-header-value-make") private WebElement makeText;
    @FindBy(id = "input-make") private WebElement makeDropdownList;
    @FindBy(id = "section-make-submit") private WebElement submitMakeButton;

    @FindBy(id = "dashboard-section-toggler-model") private WebElement editModelButton;
    @FindBy(id = "dashboard-section-header-value-model") private WebElement modelText;
    @FindBy(id = "input-model") private WebElement modelDropdownList;
    @FindBy(id = "section-model-submit") private WebElement submitModelButton;

    @FindBy(id = "dashboard-section-toggler-odometer") private WebElement updateOdometerReading;
    @FindBy(id = "odometer") private WebElement enterOdometerReading;
    @FindBy(id = "notReadable") private WebElement odometerNotReadableOption;
    @FindBy(id = "noOdometer") private WebElement noOdometerOption;
    @FindBy(id = "section-odometer-submit") private WebElement submitOdometerReading;

    @FindBy(id = "dashboard-section-toggler-vehicle-colour") private WebElement editColour;
    @FindBy(id = "select-primary-colour") private WebElement selectPrimaryColour;
    @FindBy(id = "select-secondary-colour") private WebElement selectSecondaryColour;
    @FindBy(id = "section-vehicle-colour-submit") private WebElement submitColour;

    @FindBy(id = "dashboard-section-toggler-vts") private WebElement editTestingLocation;
    @FindBy(id = "select2-drop") private WebElement vtsSearchContent;
    @FindBy(id = "input-vts") private WebElement vtsSearchBox;
    @FindBy(xpath = "//*[@id='dashboard-section-body-vts']//*[contains(@class, 'select2-chosen')]") private WebElement vtsSearchBoxMask;
    @FindBy(id = "section-vts-submit") private WebElement submitVTSLocation;

    @FindBy(id = "dashboard-section-toggler-vin") private WebElement editVIN;
    @FindBy(id = "input-vin") private WebElement enterVIN;
    @FindBy(id = "section-vin-submit") private WebElement submitVIN;

    @FindBy(id = "dashboard-section-toggler-vrm") private WebElement editRegistration;
    @FindBy(id = "input-vrm") private WebElement enterVRM;
    @FindBy(id = "section-vrm-submit") private WebElement submitVRM;

    @FindBy(id = "dashboard-section-toggler-cor") private WebElement editCountryOfRegistration;
    @FindBy(id = "input-cor") private WebElement selectCountryOfRegistration;
    @FindBy(id = "section-cor-submit") private WebElement submitCountryOfRegistration;

    @FindBy(id = "dashboard-section-toggler-expiryDate") private WebElement editExpiryDate;
    @FindBy(id = "expiryDateDay") private WebElement dayOfExpiry;
    @FindBy(id = "expiryDateMonth") private WebElement monthOfExpiry;
    @FindBy(id = "expiryDateYear") private WebElement yearOfExpiry;
    @FindBy(id = "section-expiryDate-submit") private WebElement submitExpiryDate;

    @FindBy(id = "dashboard-section-toggler-cor") private WebElement editCountryOfRegistrationButton;
    @FindBy(id = "dashboard-section-header-value-cor") private WebElement countryOfRegistrationText;
    @FindBy(id = "input-cor") private WebElement countryOfRegistrationDropdownList;
    @FindBy(id = "section-cor-submit") private WebElement submitCountryOfRegistrationButton;

    @FindBy(id = "input-reason-for-replacement") private WebElement enterReasonForReplacement;
    @FindBy(id = "updateCertificate") private WebElement reviewChanges;

    public ReplacementCertificateUpdatePage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE);
    }

    public void selectNoOdometerOption() {
        noOdometerOption.click();
    }

    public void submitNoOdometerOption() {
        updateOdometerReading.click();
        selectNoOdometerOption();
        submitOdometerReading.click();
    }

    public void changeCountryOfRegistration(CountryOfRegistration country) {
        editCountryOfRegistration.click();
        FormDataHelper.selectFromDropDownByValue(selectCountryOfRegistration, country.getRegistrationId());
        submitCountryOfRegistration.click();
    }

    public <T extends Page> T reviewChangesButton(Class<T> clazz) {
        if(PageInteractionHelper.isElementDisplayed(enterReasonForReplacement)){
            FormDataHelper.enterText(enterReasonForReplacement, "Test Reason");
        }
        reviewChanges.click();
        return MotPageFactory.newPage(driver, clazz);
    }

    public boolean isEditOdometerButtonDisplayed(){
        return PageInteractionHelper.isElementDisplayed(updateOdometerReading);
    }

    public String getCountryOfRegistration() {
        return countryOfRegistrationText.getText();
    }
}
