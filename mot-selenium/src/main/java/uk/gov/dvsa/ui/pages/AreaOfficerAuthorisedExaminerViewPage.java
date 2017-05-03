package uk.gov.dvsa.ui.pages;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.ui.pages.authorisedexaminer.Aep.RemoveAepPage;
import uk.gov.dvsa.ui.pages.authorisedexaminer.AuthorisedExaminerViewPage;
import uk.gov.dvsa.ui.pages.authorisedexaminer.ChangeDetails.*;

public class AreaOfficerAuthorisedExaminerViewPage extends AuthorisedExaminerViewPage {

    private static final String PAGE_TITLE = "Authorised Examiner";
    @FindBy(id = "ae-auth-status") private WebElement aeAuthStatus;
    @FindBy(id = "ae-name-change") private WebElement changeNameLink;
    @FindBy(id = "ae-trading-name-change") private WebElement changeTradingNameLink;
    @FindBy(id = "ae-type-change") private WebElement changeBusinessTypeLink;
    @FindBy(id = "ae-auth-status-change") private WebElement changeAuthStatusLink;
    @FindBy(id = "ae-dvsa-area-office-change") private WebElement changeDAOLink;
    @FindBy(id = "reg-AE-address-change") private WebElement changeRegOfficeAddressLink;
    @FindBy(id = "reg-email-change") private WebElement changeRegOfficeEmailLink;
    @FindBy(id = "reg-telephone-change") private WebElement changeRegOfficeTelephoneLink;
    @FindBy(id = "cor-address-change") private WebElement changeCorrespondenceAddressLink;
    @FindBy(id = "cor-email-change") private WebElement changeCorrespondenceEmailLink;
    @FindBy(id = "cor-phone-change") private WebElement changeCorrespondenceTelephoneLink;
    @FindBy(css = "#aeps a.remove-aep") private WebElement removeFirstAepLink;

    public AreaOfficerAuthorisedExaminerViewPage(MotAppDriver driver) {
        super(driver, PAGE_TITLE);
    }

    public ChangeAEDetailsNamePage clickChangeNameLink() {
        changeNameLink.click();
        return new ChangeAEDetailsNamePage(driver);
    }

    public ChangeAEDetailsTradingNamePage clickChangeTradingNameLink() {
        changeTradingNameLink.click();
        return new ChangeAEDetailsTradingNamePage(driver);
    }

    public ChangeAEDetailsStatusPage clickChangeAEStatusLink() {
        changeAuthStatusLink.click();
        return new ChangeAEDetailsStatusPage(driver);
    }

    public ChangeAERegisteredOfficeAddressPage clickChangeRegOfficeAddressLink() {
        changeRegOfficeAddressLink.click();
        return new ChangeAERegisteredOfficeAddressPage(driver);
    }

    public ChangeAERegOfficeEmailPage clickChangeRegOfficeEmailLink() {
        changeRegOfficeEmailLink.click();
        return new ChangeAERegOfficeEmailPage(driver);
    }

    public ChangeAERegOfficePhonePage clickChangeRegOfficeTelephoneLink() {
        changeRegOfficeTelephoneLink.click();
        return new ChangeAERegOfficePhonePage(driver);
    }

    public ChangeAECorrespondenceAddressPage clickChangeCorrespondenceAddressLink() {
        changeCorrespondenceAddressLink.click();
        return new ChangeAECorrespondenceAddressPage(driver);
    }

    public ChangeAECorrespondenceEmailPage clickChangeCorrespondenceEmailLink() {
        changeCorrespondenceEmailLink.click();
        return new ChangeAECorrespondenceEmailPage(driver);
    }

    public ChangeAECorrespondencePhonePage clickChangeCorrespondenceTelephoneLink() {
        changeCorrespondenceTelephoneLink.click();
        return new ChangeAECorrespondencePhonePage(driver);
    }

    public ChangeAEDetailsAreaOfficePage clickChangeDVSAAreaOfficeLink() {
        changeDAOLink.click();
        return new ChangeAEDetailsAreaOfficePage(driver);
    }

    public ChangeBusinessTypePage clickChangeBusinessTypeLink() {
        changeBusinessTypeLink.click();
        return new ChangeBusinessTypePage(driver);
    }

    public RemoveAepPage clickRemoveAep() {
        removeFirstAepLink.click();
        return new RemoveAepPage(driver);
    }

    public String getAEAuthStatus() {
        return aeAuthStatus.getText();
    }
}
