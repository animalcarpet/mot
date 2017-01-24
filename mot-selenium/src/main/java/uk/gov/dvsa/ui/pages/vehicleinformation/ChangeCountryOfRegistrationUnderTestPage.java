package uk.gov.dvsa.ui.pages.vehicleinformation;


import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.domain.model.vehicle.CountryOfRegistration;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.FormDataHelper;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;
import uk.gov.dvsa.ui.pages.mot.StartTestConfirmationPage;

public class ChangeCountryOfRegistrationUnderTestPage extends Page {

    private static final String PAGE_TITLE = "What is the vehicle's country of registration?";

    @FindBy(id = "countryOfRegistration")
    WebElement countryOfRegistrationDropdown;
    @FindBy(id = "submitUpdate")
    WebElement submit;

    public ChangeCountryOfRegistrationUnderTestPage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE);
    }

    public ChangeCountryOfRegistrationUnderTestPage selectCountryOfRegistration(CountryOfRegistration countryOfRegistration) {
        FormDataHelper.selectFromDropDownByValue(countryOfRegistrationDropdown, countryOfRegistration.getRegistrationId().toString());
        return this;
    }

    public StartTestConfirmationPage submit() {
        submit.click();
        return new StartTestConfirmationPage(driver);
    }
}
