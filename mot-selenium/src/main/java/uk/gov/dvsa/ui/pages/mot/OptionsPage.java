package uk.gov.dvsa.ui.pages.mot;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.HomePage;
import uk.gov.dvsa.ui.pages.Page;

import java.net.MalformedURLException;
import java.net.URI;
import java.net.URISyntaxException;
import java.net.URL;

public abstract class OptionsPage extends Page {
    private String page_title = "";

    @FindBy(id = "sign-out") private WebElement signOut;
    @FindBy(id = "return_to_home") private WebElement returnToHome;
    @FindBy(id = "print-inspection-sheet") private WebElement printInspectionSheet;

    public OptionsPage(MotAppDriver driver, String page_title) {
        super(driver);
        this.page_title = page_title;
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), page_title);
    }


    public String getMotTestPath() throws URISyntaxException {
        URI uri = new URI(driver.getCurrentUrl());
        String path = uri.getPath();

        return path.substring(0, path.lastIndexOf('/'));
    }

    public HomePage clickReturnToHome() {
        returnToHome.click();

        return new HomePage(driver);
    }

    public boolean printInspectionSheetIsDisplayed() {
        return printInspectionSheet.isDisplayed();
    }

    public String getPrintInspectionSheetLink() throws MalformedURLException {
        return new URL(printInspectionSheet.getAttribute("href")).getPath();
    }
}
