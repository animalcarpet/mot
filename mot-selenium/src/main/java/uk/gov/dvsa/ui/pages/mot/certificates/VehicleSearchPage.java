package uk.gov.dvsa.ui.pages.mot.certificates;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;

import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;

import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;

public class VehicleSearchPage extends Page {

    private static final String PAGE_TITLE = "Duplicate or replacement certificate";

    @FindBy (id = "submit") private WebElement searchButton;
    @FindBy (id = "navigation-link-") private WebElement cancelButton;

    public VehicleSearchPage(MotAppDriver driver) {
        super(driver);
    }

    @Override
    public boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE);
    }

    protected ReplacementCertificateResultsPage search() {
        searchButton.click();
        return new ReplacementCertificateResultsPage(driver);
    }
}
