package uk.gov.dvsa.ui.pages.vts.ChangeDetails;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.FormDataHelper;

public class ChangeDetailsTelephonePage extends ChangeDetailsPage {
    public static final String PAGE_TITLE = "Change telephone number";
    public static final String PATH = "/vehicle-testing-station/%s/telephone/change";

    @FindBy (id = "phoneTextBox") private WebElement telephoneField;

    public ChangeDetailsTelephonePage(MotAppDriver driver) {
        super(driver, PAGE_TITLE);
    }

    public ChangeDetailsTelephonePage inputContactDetailsTelephone(String newTelephone) {
        FormDataHelper.enterText(telephoneField, newTelephone);
        return this;
    }
}