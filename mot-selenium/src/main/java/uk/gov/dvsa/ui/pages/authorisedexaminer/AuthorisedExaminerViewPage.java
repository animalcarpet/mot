package uk.gov.dvsa.ui.pages.authorisedexaminer;

import org.openqa.selenium.By;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.domain.model.AeDetails;
import uk.gov.dvsa.domain.navigation.MotPageFactory;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;
import uk.gov.dvsa.ui.pages.authorisedexaminer.Aep.CreateAepPage;
import uk.gov.dvsa.ui.pages.cpms.BuyTestSlotsPage;
import uk.gov.dvsa.ui.pages.cpms.TransactionHistoryPage;
import uk.gov.dvsa.ui.pages.vts.DisassociateASitePage;

import static org.hamcrest.MatcherAssert.assertThat;
import static org.hamcrest.core.Is.is;
import static org.hamcrest.core.IsEqual.equalTo;

public abstract class AuthorisedExaminerViewPage extends Page {

    @FindBy(id = "change-contact-details") private WebElement changeContactDetails;
    @FindBy(id = "add-slots" ) private WebElement buySlots;
    @FindBy(id = "ae-name") private WebElement aeName;
    @FindBy(id = "ae-trading-name") private WebElement aeTradingName;
    @FindBy(id = "ae-type") private WebElement aeType;
    @FindBy(id = "ae-company-number") private WebElement aeCompanyNumber;
    @FindBy(id = "ae-dvsa-area-office") private WebElement aeDVSAAreaOffice;
    @FindBy(id = "reg-AE-address") private WebElement regAddress;
    @FindBy(id = "reg-email") private WebElement regEmail;
    @FindBy(id = "reg-telephone") private WebElement regTelephone;
    @FindBy(id = "cor-address") private WebElement corrAddress;
    @FindBy(id = "cor-email") private WebElement corrEmail;
    @FindBy(id = "cor-phone") private WebElement corrPhone;
    @FindBy(id = "content") private WebElement siteContent;
    @FindBy(id = "validation-message--success") private WebElement validationMessage;
    @FindBy(id = "add-aep") private WebElement createAEPLink;
    @FindBy(id = "slot-count") private WebElement numberOfSlots;
    @FindBy(id = "transaction-history") private WebElement transactionHistory;
    @FindBy(id = "test-quality-information") private WebElement testQualityInformationLink;
    private static final String removeSiteFromAeLinkLocator = "#vehicle-testing-station-%s td a";

    private WebElement getRemoveSiteFromAeLink(String vtsId) {
        return driver.findElement(By.cssSelector(String.format(removeSiteFromAeLinkLocator, vtsId)));
    }

    private static String pageTitle = "";
    public static final String PATH = "/authorised-examiner/%s";

    public AuthorisedExaminerViewPage(MotAppDriver driver, String title) {
        super(driver);
        pageTitle = title;
        selfVerify();
    }

    @Override public boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), pageTitle);
    }
    
    public BuyTestSlotsPage clickBuySlotsLink() {
        buySlots.click();
        return new BuyTestSlotsPage(driver);
    }

    public String getAeName() {
        return aeName.getText();
    }

    public String getAeTradeName() {
        return aeTradingName.getText();
    }

    public String getBusinessTypeWithCompanyNumber() {
        return aeType.getText();
    }

    public String getAeRegAddress() {
        return regAddress.getText();
    }

    public String getAeRegEmail() {
        return regEmail.getText();
    }

    public String getAeRegTelephoneNumber() {
        return regTelephone.getText();
    }

    public String getAeCorrAddress() {
        return corrAddress.getText();
    }

    public String getAeDVSAAreaOffice() {
        return aeDVSAAreaOffice.getText();
    }

    public String getAeCorrEmail() {
        return corrEmail.getText();
    }

    public String getAeCorrPhone() {
        return corrPhone.getText();
    }

    public int getSlotCount() {
        return Integer.valueOf(numberOfSlots.getText().split("\n")[0]);
    }

    private boolean verifyNewAeBusinessDetails(AeDetails aeDetails) {
        assertThat(getAeName(), equalTo(aeDetails.getAeBusinessDetails().getBusinessName()));
        assertThat(getAeTradeName(), equalTo(aeDetails.getAeBusinessDetails().getTradingName()));
        assertThat(getBusinessTypeWithCompanyNumber(), equalTo(aeDetails.getAeBusinessDetails().getBusinessType()
                + "\n" + aeDetails.getAeBusinessDetails().getCompanyNumber()));
        return true;
    }

    public boolean verifyNewAeAddressDetails(AeDetails aeDetails) {
        assertThat(getAeRegAddress(),
                equalTo(aeDetails.getAeContactDetails().getAddress().getLine1() + ",\n" +
                        aeDetails.getAeContactDetails().getAddress().getLine2() + ",\n" +
                        aeDetails.getAeContactDetails().getAddress().getLine3() + ",\n" +
                        aeDetails.getAeContactDetails().getAddress().getTown()  + ",\n" +
                        aeDetails.getAeContactDetails().getAddress().getPostcode()));
        return true;
    }

    public boolean verifyNewAeRegContact(AeDetails aeDetails) {

        assertThat(getAeRegTelephoneNumber(), equalTo(aeDetails.getAeContactDetails().getTelephoneNumber()));
        assertThat(getAeRegEmail(), equalTo(aeDetails.getAeContactDetails().getEmail()));
        return true;
    }

    public boolean verifyNewAeCreated(AeDetails aeDetails) {
        assertThat(verifyNewAeBusinessDetails(aeDetails), is(true));
        assertThat(verifyNewAeAddressDetails(aeDetails), is(true));
        assertThat(verifyNewAeRegContact(aeDetails), is(true));
        return true;
    }

    public DisassociateASitePage clickRemoveSiteLink(String vtsId) {
        getRemoveSiteFromAeLink(vtsId).click();
        return new DisassociateASitePage(driver);
    }

    public String getSiteContentText() {
        return siteContent.getText();
    }

    public String getValidationMessage() {
        return validationMessage.getText();
    }

    public CreateAepPage clickCreateAepLink() {
        createAEPLink.click();
        return new CreateAepPage(driver);
    }

    public TransactionHistoryPage clickTransactionHistoryLink() {
        transactionHistory.click();
        return MotPageFactory.newPage(driver, TransactionHistoryPage.class);
    }

    public AETestQualityInformationPage clickTestQualityInformationLink()
    {
        testQualityInformationLink.click();
        return MotPageFactory.newPage(driver, AETestQualityInformationPage.class);
    }

    public boolean isTestQualityInformationLinkDisplayed()
    {
        return PageInteractionHelper.isElementDisplayed(testQualityInformationLink);
    }
}
