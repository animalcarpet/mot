package uk.gov.dvsa.ui.pages;

import org.openqa.selenium.By;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.domain.model.vehicle.Vehicle;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.FormDataHelper;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.mot.StartTestConfirmationPage;
import uk.gov.dvsa.ui.pages.mot.retest.ConfirmVehicleRetestPage;
import uk.gov.dvsa.ui.pages.vehicleinformation.CreateNewVehicleRecordIdentificationPage;


public class VehicleSearchPage extends Page {

    private static final String PAGE_TITLE = "Find a vehicle";
    public static final String PATH = "/vehicle-search";
    public static final String REPLACEMENT_PATH = "/replacement-certificate-vehicle-search";
    public static final String TRAINING_TEST_PATH = "/training-test-vehicle-search";

    @FindBy(className = "content-header__type") private WebElement pageHeader;
    
    @FindBy(className = "group") private WebElement breadCrumbList;
    
    @FindBy(id = "vin-info") private WebElement vinInfo;

    @FindBy(id = "main-message") private WebElement mainMessage;

    @FindBy(id = "additional-message") private WebElement additionalMessage;

    @FindBy(name = "registration") protected WebElement registrationField;

    @FindBy(name = "vin") private WebElement vinField;

    @FindBy(id = "cancel_vehicle_search") private WebElement cancelButton;

    @FindBy(id = "vehicle-search") private WebElement searchButton;

    @FindBy(id = "global-breadcrumb") private WebElement stepInfo;

    @FindBy(id = "vin-type-select") private WebElement vinTypeSelect;

    @FindBy(id = "VehicleSearch") private WebElement vehicleSearchForm;

    @FindBy(id = "new-vehicle-record-link") private WebElement createNewVehicleLink;

    @FindBy(id = "search-again") private WebElement searchAgainLink;
    @FindBy(xpath = ".//p[contains(., 'No matches were found for VIN')]") private WebElement noVinMatchErrorBox;
    @FindBy(id= "results-table") private WebElement vehicleInfoTable;

    @FindBy(id = "new-vehicle-record-info" ) private WebElement createNewVehicleInfo;

    private By searchResultsTable = By.cssSelector("#results-table a");

    private By testStatus = By.id("vehicle-search-retest");

    public VehicleSearchPage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE);
    }

    public VehicleSearchPage searchVehicle(Vehicle vehicle){
        String regNumber = vehicle.getDvsaRegistration() == null ?
                vehicle.getDvlaRegistration() :
                vehicle.getDvsaRegistration();

        FormDataHelper.enterText(registrationField, regNumber);
        FormDataHelper.enterText(vinField, vehicle.getVin());

        searchButton.click();

        return this;
    }

    public VehicleSearchPage searchVehicle(String registration, String vin) {
        registrationField.clear();
        vinField.clear();
        registrationField.sendKeys(registration);
        vinField.sendKeys(vin);
        searchButton.click();

        return this;
    }

    public void searchVehicle() {
        searchButton.click();
    }

    public VehicleSearchPage selectVehicle(){
        WebElement vehicleLink = driver.findElement(searchResultsTable);
        vehicleLink.click();

        return this;
    }

    public StartTestConfirmationPage selectVehicleForTest(){
        WebElement vehicleLink = driver.findElement(searchResultsTable);
        vehicleLink.click();

        return new StartTestConfirmationPage(driver);
    }

    public ConfirmVehicleRetestPage selectVehicleForRetest() {
        WebElement vehicleLink = driver.findElement(searchResultsTable);
        vehicleLink.click();

        return new ConfirmVehicleRetestPage(driver);
    }

    public CreateNewVehicleRecordIdentificationPage createNewVehicle() {
        createNewVehicleLink.click();
        return new CreateNewVehicleRecordIdentificationPage(driver);
    }

    public String getVehicleSearchStepNumber() {
        return stepInfo.getText();
    }

    public boolean isCreateNewVehicleRecordLinkDisplayed() {
        return createNewVehicleLink.isDisplayed();
    }

    public boolean isCreateNewVehicleInfoDisplayed() {
        return createNewVehicleInfo.isDisplayed();
    }

    public String getMainMessageText() {
        return mainMessage.getText();
    }

    public String getTestStatus() {
        return driver.findElement(testStatus).getText();
    }

    public boolean isResultVehicleDisplayed() {
        return driver.findElement(searchResultsTable).isDisplayed();
    }

    public VehicleSearchPage clickSearchAgain() {
        searchAgainLink.click();
        return this;
    }

    public boolean isSearchAgainDisplyed(){
       return searchAgainLink.isDisplayed();
    }

    public  String getSearchResultMessage() {
        return mainMessage.getText();
    }

    public String getPageHeader() {
        return breadCrumbList.getText();
    }
}
