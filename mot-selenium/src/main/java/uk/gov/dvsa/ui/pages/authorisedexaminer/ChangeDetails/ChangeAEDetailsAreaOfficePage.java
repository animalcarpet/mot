package uk.gov.dvsa.ui.pages.authorisedexaminer.ChangeDetails;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.FormDataHelper;

public class ChangeAEDetailsAreaOfficePage extends ChangeAEDetailsPage {
    public static final String PATH = "/authorised-examiner/%s/areaoffice/change";
    public static final String PAGE_TITLE = "Change area office";

    @FindBy(id = "aeAreaOfficeSelectSet") private WebElement areaOfficeSelect;

    public ChangeAEDetailsAreaOfficePage(MotAppDriver driver) {
        super(driver, PAGE_TITLE);
    }

    public ChangeAEDetailsAreaOfficePage changeAreaOffice(String value) {
        FormDataHelper.selectFromDropDownByVisibleText(areaOfficeSelect, value);
        return this;
    }
}
