package uk.gov.dvsa.ui.pages.authorisedexaminer.ChangeDetails;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.FormDataHelper;

public class ChangeAEDetailsTradingNamePage extends ChangeAEDetailsPage {
    public static final String PATH = "/authorised-examiner/%s/trading-name/change";
    public static final String PAGE_TITLE = "Change trading name";

    @FindBy(id = "aeTradingNameTextBox") private WebElement tradingName;

    public ChangeAEDetailsTradingNamePage(MotAppDriver driver) {
        super(driver, PAGE_TITLE);
    }

    public ChangeAEDetailsTradingNamePage inputTradingName(String name) {
        FormDataHelper.enterText(tradingName, name);
        return this;
    }
}
