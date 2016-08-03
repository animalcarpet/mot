package uk.gov.dvsa.ui.pages.vts;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.FormDataHelper;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;

public class ChangeTestingFacilitiesPage extends Page {

    public static final String PATH = "/vehicle-testing-station/%s/testing-facilities";
    private static final String PAGE_TITLE = "Change testing facilities";

    @FindBy(id = "facilityTptl") private WebElement twoPersonTestLaneDropDown;
    @FindBy(id = "facilityOptl") private  WebElement onePersonTestLaneDropDown;
    @FindBy(id = "submitTestingFacilitiesUpdate") private WebElement submitTestingFacilitiesButton;

    public ChangeTestingFacilitiesPage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE);
    }

    public ChangeTestingFacilitiesPage selectOnePersonTestLaneNumber(String onePersonTestLandNumber){
        FormDataHelper.selectFromDropDownByValue(onePersonTestLaneDropDown,onePersonTestLandNumber);
        return this;
    }

    public ChangeTestingFacilitiesPage selectTwoPersonTestLaneNumber(String twoPersonTestLaneNumber){
        FormDataHelper.selectFromDropDownByValue(twoPersonTestLaneDropDown,twoPersonTestLaneNumber);
        return this;
    }

    public ConfirmTestFacilitiesPage clickOnSaveTestFacilitiesButton(){
        submitTestingFacilitiesButton.click();
        return new ConfirmTestFacilitiesPage(driver);
    }
}
