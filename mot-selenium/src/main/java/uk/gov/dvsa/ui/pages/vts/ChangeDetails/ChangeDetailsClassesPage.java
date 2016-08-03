package uk.gov.dvsa.ui.pages.vts.ChangeDetails;

import org.openqa.selenium.By;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.domain.model.vehicle.VehicleClass;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.FormDataHelper;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;
import uk.gov.dvsa.ui.pages.vts.ConfirmChangeDetails.ConfirmChangeDetailsClassesPage;

import java.util.List;

public class ChangeDetailsClassesPage extends Page {
    public static final String PATH = "/vehicle-testing-station/%s/classes/change";
    public static final String PAGE_TITLE = "Change classes";

    @FindBy(id = "submitUpdate") private WebElement submitButton;

    public ChangeDetailsClassesPage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE);
    }

    public ChangeDetailsClassesPage uncheckAllSelectedClasses() {
        List <WebElement> allElements = driver.findElements(By.cssSelector("input:checked[type='checkbox']"));
        for (WebElement element : allElements) {
            element.click();
        }
        return this;
    }

    public ChangeDetailsClassesPage chooseOption(VehicleClass className) {
        FormDataHelper.selectInputBox(driver.findElement(By.cssSelector(String.format("input[value='%s']", className.getId()))));
        return this;
    }

    public ConfirmChangeDetailsClassesPage clickConfirmationSubmitButton() {
        submitButton.click();
        return new ConfirmChangeDetailsClassesPage(driver);
    }
}
