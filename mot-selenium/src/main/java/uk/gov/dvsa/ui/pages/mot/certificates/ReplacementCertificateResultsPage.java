package uk.gov.dvsa.ui.pages.mot.certificates;

import org.openqa.selenium.By;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;

public class ReplacementCertificateResultsPage extends Page {

    public static final String PAGE_TITLE = "MOT test certificates";
    @FindBy(id = "vrm")
    private WebElement textField;

    public ReplacementCertificateResultsPage(MotAppDriver driver) {
        super(driver);
    }

    @Override
    public boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE);
    }

    public ReplacementCertificateViewPage viewTest(String testId){
        driver.findElement(By.id(String.format("view-%s", testId))).click();
        return new ReplacementCertificateViewPage(driver);
    }

    public ReplacementCertificateViewPage viewOlderTest(String registration, String testId) {
        WebElement showOlderTests = driver.findElement(By.id("show-older-tests-" + registration));
        if(showOlderTests.isDisplayed()) {
            showOlderTests.click();
        }
        return viewTest(testId);
    }
}
