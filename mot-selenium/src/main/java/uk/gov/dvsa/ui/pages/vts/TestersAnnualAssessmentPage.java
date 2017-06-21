package uk.gov.dvsa.ui.pages.vts;

import org.openqa.selenium.By;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.domain.navigation.MotPageFactory;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;
import uk.gov.dvsa.ui.pages.profile.annualassessmentcertificates.AnnualAssessmentCertificatesIndexPage;

import java.util.List;

public class TestersAnnualAssessmentPage extends Page {

    private static final String PAGE_TITLE = "Tester annual assessment";

    @FindBy(id="certificate-table-group-a") private WebElement tableGroupA;
    @FindBy(id="certificate-table-group-b") private WebElement tableGroupB;
    @FindBy(css = "#certificate-table-group-a tbody tr:first-child")
    private WebElement firstCertificateRow;

    public TestersAnnualAssessmentPage(MotAppDriver driver) {
        super(driver);
    }

    @Override protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE);
    }

    public AnnualAssessmentCertificatesIndexPage goToAnnualAssessmentCertificatesIndexPage(String userName, String group){
        List<WebElement> tableRows = driver.findElements(By.cssSelector("table#certificate-table-group-" + group + " > tbody > tr"));

        for (WebElement row: tableRows){
            if(row.getText().contains(userName)) {
                row.findElement(By.cssSelector("a")).click();
                return new AnnualAssessmentCertificatesIndexPage(driver);
            }
        }
        return null;
    }

    public String getFirstCertificateGroupAUserInformation() {
        return this.firstCertificateRow.findElement(By.cssSelector("td:nth-child(1)")).getText();
    }

    public Object getFirstCertificateGroupADateAwarded() {
        return this.firstCertificateRow.findElement(By.cssSelector("td:nth-child(2)")).getText();
    }
}
