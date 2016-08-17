package uk.gov.dvsa.ui.pages;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.domain.navigation.MotPageFactory;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.ui.pages.mot.StartTestConfirmationPage;
import uk.gov.dvsa.ui.pages.mot.retest.ConfirmVehicleRetestPage;

public class VehicleSearchResultsPage extends AbstractVehicleSearchResultsPage {

    @FindBy(id = "search-again") private WebElement searchAgainLink;
    @FindBy(id= "vehicle-search-retest") private WebElement vehicleRetestStatus;
    @FindBy(css= "#results-table a") private WebElement vehicleLink;

    public VehicleSearchResultsPage(MotAppDriver driver) {
        super(driver);
    }

    public boolean isBasePageContentCorrect() {
        return super.isBasePageContentCorrect() && this.isSearchAgainDisplayed() && this.isResultVehicleDisplayed();
    }

    public <T extends Page> T selectVehicle(Class<T> clazz){
        vehicleLink.click();
        return MotPageFactory.newPage(driver, clazz);
    }

    public String getVehicleRetestStatus() {
        return vehicleRetestStatus.getText();
    }

    public boolean isResultVehicleDisplayed() {
        return vehicleLink.isDisplayed();
    }

    public VehicleSearchResultsPage clickSearchAgain() {
        searchAgainLink.click();
        return this;
    }

    public boolean isSearchAgainDisplayed(){
        return searchAgainLink.isDisplayed();
    }
}
