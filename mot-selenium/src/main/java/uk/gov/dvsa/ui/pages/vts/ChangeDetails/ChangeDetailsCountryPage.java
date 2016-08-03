package uk.gov.dvsa.ui.pages.vts.ChangeDetails;

import org.openqa.selenium.By;
import uk.gov.dvsa.domain.model.site.ContactDetailsCountry;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.FormDataHelper;

public class ChangeDetailsCountryPage extends ChangeDetailsPage {
    public static final String PAGE_TITLE = "Change country";
    public static final String PATH = "/vehicle-testing-station/%s/country/change";

    public ChangeDetailsCountryPage(MotAppDriver driver) {
        super(driver, PAGE_TITLE);
    }

    public ChangeDetailsCountryPage chooseOption(ContactDetailsCountry name) {
        FormDataHelper.selectInputBox(driver.findElement(By.cssSelector(String.format("input[value=%s]", name.getSiteContactDetailsCountryCode()))));
        return this;
    }
}