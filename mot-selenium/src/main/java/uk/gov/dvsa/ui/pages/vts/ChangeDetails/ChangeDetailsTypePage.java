package uk.gov.dvsa.ui.pages.vts.ChangeDetails;

import org.openqa.selenium.By;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.domain.model.site.Type;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.FormDataHelper;

public class ChangeDetailsTypePage extends ChangeDetailsPage {
    public static final String PATH = "/vehicle-testing-station/%s/type/change";
    public static final String PAGE_TITLE = "Change site type";

    @FindBy(id = "submitUpdate") private WebElement submitButton;

    public ChangeDetailsTypePage(MotAppDriver driver) {
        super(driver, PAGE_TITLE);
    }

    public ChangeDetailsTypePage chooseOption(Type type) {
        FormDataHelper.selectInputBox(driver.findElement(By.cssSelector(String.format("input[value=%s]",type.getSiteTypeCode()))));
        return this;
    }
}
