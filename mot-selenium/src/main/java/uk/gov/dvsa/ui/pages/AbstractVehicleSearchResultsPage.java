package uk.gov.dvsa.ui.pages;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.ui.pages.vehicleinformation.CreateNewVehicleRecordIdentificationPage;

public abstract class AbstractVehicleSearchResultsPage extends AbstractVehicleSearchPage {

    @FindBy(id = "main-message") private WebElement searchSummary;
    @FindBy(id = "new-vehicle-record-link") private WebElement createNewVehicleLink;

    public AbstractVehicleSearchResultsPage(MotAppDriver driver) {
        super(driver);
    }

    public boolean isBasePageContentCorrect() {
        return super.isBasePageContentCorrect()
                && this.isCreateNewVehicleRecordLinkDisplayed()
                && searchSummary.isDisplayed();
    }

    public String getSearchSummaryText() {
        return searchSummary.getText();
    }

    public CreateNewVehicleRecordIdentificationPage createNewVehicle() {
        createNewVehicleLink.click();
        return new CreateNewVehicleRecordIdentificationPage(driver);
    }

    public boolean isCreateNewVehicleRecordLinkDisplayed() {
        return createNewVehicleLink.isDisplayed();
    }

}
