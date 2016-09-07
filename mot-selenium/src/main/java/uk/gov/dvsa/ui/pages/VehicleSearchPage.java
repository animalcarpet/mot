package uk.gov.dvsa.ui.pages;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;

public class VehicleSearchPage extends AbstractVehicleSearchPage {

    public static final String PATH = "/vehicle-search";
    public static final String REPLACEMENT_PATH = "/replacement-certificate-vehicle-search";
    public static final String TRAINING_TEST_PATH = "/training-test-vehicle-search";

    @FindBy(xpath = "//span[contains(@class, 'summary')]") private WebElement unableToProvideRegSection;
    @FindBy(xpath = "//h4[contains(., 'Registration mark is missing')]") private WebElement registrationMissingHeader;
    @FindBy(xpath = "//h4[contains(., 'VIN is missing')]") private WebElement vinMissingHeader;

    public VehicleSearchPage(MotAppDriver driver) {
        super(driver);
    }

    public boolean isBasePageContentCorrect() {
        Boolean a = super.isBasePageContentCorrect();
        Boolean b = this.isUnableToProvideRegOrVINLinkDisplayed();
        Boolean c = this.isSearchSectionDisplayed();

        return super.isBasePageContentCorrect()
                && this.isUnableToProvideRegOrVINLinkDisplayed()
                && this.isSearchSectionDisplayed();
    }

    public boolean isUnableToProvideRegOrVINLinkDisplayed() {
        return unableToProvideRegSection.getText().contains("Can't provide the registration mark or VIN?");
    }

    public VehicleSearchPage clickUnableToProvideRegOrVIN() {
        unableToProvideRegSection.click();
        return this;
    }

    public boolean isUnableToProvideRegOrVINTextDisplayed() {
        return registrationMissingHeader.isDisplayed() && vinMissingHeader.isDisplayed();
    }
}
