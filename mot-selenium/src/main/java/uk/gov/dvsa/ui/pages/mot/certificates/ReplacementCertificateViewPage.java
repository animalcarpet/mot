package uk.gov.dvsa.ui.pages.mot.certificates;

import org.openqa.selenium.By;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;
import uk.gov.dvsa.ui.pages.mot.certificates.ReplacementCertificateUpdatePage;

public class ReplacementCertificateViewPage extends Page {
    private static final String PAGE_TITLE = "MOT test result";

    @FindBy (id = "reprint-certificate") private WebElement reprintButton;
    private By editButtonBy = By.id("edit");

    public ReplacementCertificateViewPage(MotAppDriver driver) {
        super(driver);
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE);
    }

    public ReplacementCertificateUpdatePage edit() {
        editButtonBy = By.id("edit");
        WebElement editButton = driver.findElement(editButtonBy);
        editButton.click();
        return new ReplacementCertificateUpdatePage(driver);
    }

    public boolean isReprintButtonDisplayed(){
        return reprintButton.isDisplayed();
    }

    public boolean isEditButtonDisplayed() {
        return PageInteractionHelper.isElementDisplayed(editButtonBy);
    }


}
