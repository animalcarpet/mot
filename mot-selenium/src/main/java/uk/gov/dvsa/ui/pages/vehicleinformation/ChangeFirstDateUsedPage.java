package uk.gov.dvsa.ui.pages.vehicleinformation;

import org.joda.time.DateTime;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.FormDataHelper;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;

public class ChangeFirstDateUsedPage extends Page {
    public static final String PATH = "/change/country";
    private static final String PAGE_TITLE = "Change date of first use";

    @FindBy(id = "date-day") WebElement dayInput;
    @FindBy(id = "date-month") WebElement monthInput;
    @FindBy(id = "date-year") WebElement yearInput;
    @FindBy(id = "submitUpdate") WebElement submit;

    public ChangeFirstDateUsedPage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE);
    }

    public ChangeFirstDateUsedPage selectFirstDateUsed(DateTime date) {
        FormDataHelper.enterText(dayInput, Integer.toString(date.getDayOfMonth()));
        FormDataHelper.enterText(monthInput, Integer.toString(date.getMonthOfYear()));
        FormDataHelper.enterText(yearInput, Integer.toString(date.getYear()));
        return this;
    }

    public VehicleInformationPage submit() {
        submit.click();
        return new VehicleInformationPage(driver);
    }
}
