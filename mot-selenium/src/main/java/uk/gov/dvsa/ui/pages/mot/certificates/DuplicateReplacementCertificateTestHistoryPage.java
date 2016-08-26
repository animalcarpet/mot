package uk.gov.dvsa.ui.pages.mot.certificates;

import org.openqa.selenium.By;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.domain.navigation.MotPageFactory;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;

public class DuplicateReplacementCertificateTestHistoryPage extends Page {

    private static final String PAGE_TITLE = "Duplicate or replacement certificate";
    private String viewButton = "view-%s";
    private String editButton = "edit-%s";

    public DuplicateReplacementCertificateTestHistoryPage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE);
    }

    public ReplacementCertificateUpdatePage clickEditButton(String motTestId) {
        WebElement editButton = driver.findElement(By.id(String.format("edit-%s", motTestId)));
        editButton.click();
        return new ReplacementCertificateUpdatePage(driver);
    }

    public <T extends Page> T viewTest(String testId, Class<T> clazz){
        driver.findElement(By.id(String.format(viewButton, testId))).click();
        return MotPageFactory.newPage(driver, clazz);
    }

    public <T extends Page> T editTest(String testId, Class<T> clazz){
        driver.findElement(By.id(String.format(editButton, testId))).click();
        return MotPageFactory.newPage(driver, clazz);
    }
}