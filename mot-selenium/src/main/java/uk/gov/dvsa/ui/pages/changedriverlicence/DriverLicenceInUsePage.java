package uk.gov.dvsa.ui.pages.changedriverlicence;

import org.openqa.selenium.By;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;

public class DriverLicenceInUsePage extends Page {

    private static final String PAGE_TITLE = "Driving licence already in use";
    @FindBy(linkText = "Back") private WebElement backLink;
    @FindBy(className = "message--failure") private WebElement failureMessage;
    @FindBy(className = "matched-records") private WebElement matchedRecordsTable;


    public DriverLicenceInUsePage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(getTitle(), PAGE_TITLE);
    }

    public ReviewDrivingLicencePage clickBack() {
        backLink.click();
        return new ReviewDrivingLicencePage(driver);
    }

    public String getFailureMessage() {
        return failureMessage.findElement(By.tagName("h2")).getText();
    }

    public boolean isMatchedRecordsTableDisplayed() {
        return matchedRecordsTable.isDisplayed();
    }
}
