package uk.gov.dvsa.ui.pages.vehicleinformation;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.domain.model.vehicle.CountryOfRegistration;
import uk.gov.dvsa.domain.model.vehicle.FuelTypes;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.FormDataHelper;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;

public class ChangeCountryOfRegistrationPage extends Page {

    public static final String PATH = "/change/country";
    private static final String PAGE_TITLE = "Change country of registration";

    @FindBy(id = "countryOfRegistration") WebElement countryDropdown;
    @FindBy(id = "submitUpdate") WebElement submit;

    public ChangeCountryOfRegistrationPage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE);
    }

    public ChangeCountryOfRegistrationPage selectCountryOfRegistration(CountryOfRegistration countryOfRegistration) {
        FormDataHelper.selectFromDropDownByVisibleText(countryDropdown, countryOfRegistration.getCountry());
        return this;
    }

    public VehicleInformationPage submit() {
        submit.click();
        return new VehicleInformationPage(driver);
    }
}
