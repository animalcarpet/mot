package uk.gov.dvsa.ui.pages.profile.annualassessmentcertificates;

import org.openqa.selenium.By;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;

import java.util.List;

public class AnnualAssessmentCertificatesIndexPage extends Page {

    @FindBy(id = "add-group-A")
    private WebElement addGroupA;
    @FindBy(id = "add-group-B")
    private WebElement addGroupB;
    @FindBy(css = "#certificate-table-group-A tbody tr:first-child")
    private WebElement firstCertificateRow;
    @FindBy(id = "validation-message--success")
    private WebElement messageSuccess;
    @FindBy(css = "#certificate-table-group-B tbody tr:first-child")
    private WebElement firstCertificateBRow;
    @FindBy(id = "return")
    private WebElement returnButton;

    private static final String PAGE_TITLE = "Annual assessment certificates";

    public AnnualAssessmentCertificatesIndexPage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE);
    }

    public AnnualAssessmentCertificatesAddPage clickAddGroupA() {
        addGroupA.click();
        return new AnnualAssessmentCertificatesAddPage(driver);
    }

    public AnnualAssessmentCertificatesEditPage clickEditGroupB(String oldCertificateNumber) {
        List<WebElement> tableRows = driver.findElements(By.cssSelector("#certificate-table-group-B > tbody > tr"));

        for (WebElement row: tableRows){
            if(row.getText().contains(oldCertificateNumber)){
                row.findElement(By.cssSelector("a[id^=change]")).click();
                return new AnnualAssessmentCertificatesEditPage(driver);
            }
        }

        return null;
    }

    public String getFirstCertificateGroupANumber() {
        return this.firstCertificateRow.findElement(By.cssSelector("td:nth-child(1)")).getText();
    }

    public String getFirstCertificateGroupADate() {
        return this.firstCertificateRow.findElement(By.cssSelector("td:nth-child(2)")).getText();
    }

    public Object getFirstCertificateGroupAScore() {
        return this.firstCertificateRow.findElement(By.cssSelector("td:nth-child(3)")).getText();
    }

    public AnnualAssessmentCertificatesRemovePage clickRemoveButtonForGroupA(String certificateNumber) {
        return clickRemoveButtonForGroup(certificateNumber, "A");
    }

    public AnnualAssessmentCertificatesRemovePage clickRemoveButtonForGroupB(String certificateNumber) {
        return clickRemoveButtonForGroup(certificateNumber, "B");
    }

    protected AnnualAssessmentCertificatesRemovePage clickRemoveButtonForGroup(String certificateNumber, String groupName) {
        List<WebElement> tableRows = driver.findElements(By.cssSelector("#certificate-table-group-" + groupName + " > tbody > tr"));

        for (WebElement row: tableRows){
            if(row.getText().contains(certificateNumber)){
                row.findElement(By.cssSelector("a[id^=remove]")).click();
                return new AnnualAssessmentCertificatesRemovePage(driver);
            }
        }

        return null;
    }

    public boolean verifySuccessfulMessageForRemoveGroupBCertificate(String message) {
        return messageSuccess.getText().equals(message);
    }

    public boolean thereIsNoAnyCertificateTable() {
        return !PageInteractionHelper.isElementPresent(By.id("dataTable"));
    }

    public String getFirstCertificateGroupBNumber() {
        return this.firstCertificateBRow.findElement(By.cssSelector("td:nth-child(1)")).getText();
    }

    public String getFirstCertificateGroupBDate() {
        return this.firstCertificateBRow.findElement(By.cssSelector("td:nth-child(2)")).getText();
    }

    public Object getFirstCertificateGroupBScore() {
        return this.firstCertificateBRow.findElement(By.cssSelector("td:nth-child(3)")).getText();
    }

    public boolean verifySuccessfulMessageForChangeGroupBCertificate(String message) {
        return messageSuccess.getText().equals(message);
    }

    public String gerReturnButtonText() {
        return returnButton.getText();
    }
}
