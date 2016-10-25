package uk.gov.dvsa.ui.pages.vehicleinformation;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.domain.api.response.Vehicle;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;

import static org.hamcrest.MatcherAssert.assertThat;
import static org.hamcrest.core.Is.is;

public class VehicleInformationPage extends Page {

    private static final String PAGE_TYPE = "Vehicle";

    @FindBy(className = "content-header__type") private WebElement pageHeaderType;

    @FindBy(className = "content-header__title") private WebElement pageHeaderTitle;
    @FindBy(xpath = "//li[@class='content-header__list-item'][1]") private WebElement pageHeaderTertiaryRegistration;
    @FindBy(xpath = "//li[@class='content-header__list-item'][2]") private WebElement pageHeaderTertiaryVin;
    @FindBy(id = "manufacture-date") private WebElement manufactureDate;
    @FindBy(id = "colour") private WebElement colour;
    @FindBy(id = "make-and-model") private WebElement makeModel;
    @FindBy(id = "make-and-model-change") private WebElement changeMakeAndModelLink;
    @FindBy(id = "first-used") private WebElement firstDateUsed;
    @FindBy(id = "registration-mark") private WebElement registrationNumber;
    @FindBy(id = "vin") private WebElement vinNumber;
    @FindBy(id = "engine") private WebElement engine;
    @FindBy(id = "engine-change") private WebElement changeEngineLink;
    @FindBy(id = "mot-test-class-change") private WebElement changeMotTestClassLink;
    @FindBy(id = "mot-test-class") private WebElement motTestClass;
    @FindBy(id = "country-of-registration") private WebElement countryOfRegistration;
    @FindBy(id = "country-of-registration-change") private WebElement changeCountryOfRegistrationLink;
    @FindBy(id = "colour-change") private WebElement changeColourLink;
    @FindBy(id = "first-used-change") private WebElement changeFirstDateUsedLink;

    public VehicleInformationPage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    public boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(pageHeaderType.getText(), PAGE_TYPE);
    }

    public String getColour() {
        return colour.getText();
    }

    public String getMakeModel() {
        return makeModel.getText();
    }

    public String getManufactureDate() {
        return manufactureDate.getText();
    }

    public String getRegistrationNumber() {
        return registrationNumber.getText();
    }

    public String getFirstDateUsed() {
        return firstDateUsed.getText();
    }

    public String getPageHeaderTitle() {
        return pageHeaderTitle.getText();
    }

    public String getEngine() {
        return engine.getText();
    }

    public String getPageHeaderTertiaryRegistration() {
        return pageHeaderTertiaryRegistration.getText();
    }

    public String getPageHeaderTertiaryVin() {
        return pageHeaderTertiaryVin.getText();
    }

    public String getVinNumber() {
        return vinNumber.getText();
    }

    public void verifyVehicleRegistrationAndVin(Vehicle vehicle) {
        assertThat("The registration is as expected", getRegistrationNumber(),
                is(vehicle.getDvsaRegistration()));
        assertThat("The Vin is as expected", getVinNumber(),
                is(vehicle.getVin()));
    }

    public ChangeEnginePage clickChangeEngineLink() {
        changeEngineLink.click();
        return new ChangeEnginePage(driver);
    }

    public ChangeMotTestClassPage clickChangeMotTestClassLink() {
        changeMotTestClassLink.click();
        return new ChangeMotTestClassPage(driver);
    }

    public String getMotTestClass() {
        return motTestClass.getText();
    }

    public String getCountryOfRegistration() {
        return countryOfRegistration.getText();
    }

    public ChangeCountryOfRegistrationPage clickChangeCountryOfRegistrationLink() {
        changeCountryOfRegistrationLink.click();
        return new ChangeCountryOfRegistrationPage(driver);
    }

    public ChangeMakePage clickChangeMakeAndModelLink() {
        changeMakeAndModelLink.click();
        return new ChangeMakePage(driver);
    }

    public ChangeColourPage clickChangeColourLink() {
        changeColourLink.click();
        return new ChangeColourPage(driver);
    }

    public ChangeFirstDateUsedPage clickChangeFirstDateUsedLink() {
        changeFirstDateUsedLink.click();
        return new ChangeFirstDateUsedPage(driver);
    }
}
