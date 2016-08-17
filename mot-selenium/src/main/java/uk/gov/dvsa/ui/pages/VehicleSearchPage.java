package uk.gov.dvsa.ui.pages;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;

public class VehicleSearchPage extends AbstractVehicleSearchPage {

    public static final String PATH = "/vehicle-search";
    public static final String REPLACEMENT_PATH = "/replacement-certificate-vehicle-search";
    public static final String TRAINING_TEST_PATH = "/training-test-vehicle-search";

    @FindBy(xpath = "//span[contains(@class, 'summary')]") private WebElement unableToProvideRegSection;
    @FindBy(xpath = "//h4[contains(., 'If registration mark is missing')]") private WebElement registrationMissingHeader;
    @FindBy(xpath = "//h4[contains(., 'If VIN is missing or cannot be found')]") private WebElement vinMissingHeader;

    public VehicleSearchPage(MotAppDriver driver) {
        super(driver);
    }

    public boolean isBasePageContentCorrect() {
        return super.isBasePageContentCorrect()
                && this.isUnableToProvideRegOrVINLinkDisplayed()
                && this.isSearchSectionDisplayed();
    }

    public boolean isUnableToProvideRegOrVINLinkDisplayed() {
        return unableToProvideRegSection.getText().contains("Unable to provide a registration mark or full VIN");
    }

    public VehicleSearchPage clickUnableToProvideRegOrVIN() {
        unableToProvideRegSection.click();
        return this;
    }

    public boolean isUnableToProvideRegOrVINTextDisplayed() {
        return registrationMissingHeader.isDisplayed() && vinMissingHeader.isDisplayed();
    }
}
