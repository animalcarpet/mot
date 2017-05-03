package uk.gov.dvsa.ui.pages.authorisedexaminer;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.PageInteractionHelper;

import java.util.Arrays;
import java.util.List;

public class TesterAuthorisedExaminerViewPage extends AuthorisedExaminerViewPage {
    private static String PAGE_TITLE = "Authorised Examiner";

    @FindBy(id = "ae-auth-status") private WebElement aeAuthStatus;
    @FindBy(id = "ae-name-change") private WebElement changeNameLink;
    @FindBy(id = "ae-trading-name-change") private WebElement changeTradingNameLink;
    @FindBy(id = "ae-type-change") private WebElement changeBusinessTypeLink;
    @FindBy(id = "ae-auth-status-change") private WebElement changeAuthStatusLink;
    @FindBy(id = "ae-auth-status") private WebElement authStatus;
    @FindBy(id = "ae-dvsa-area-office-change") private WebElement changeDAOLink;
    @FindBy(id = "reg-AE-address-change") private WebElement changeRegOfficeAddressLink;
    @FindBy(id = "reg-email-change") private WebElement changeRegOfficeEmailLink;
    @FindBy(id = "reg-telephone-change") private WebElement changeRegOfficeTelephoneLink;
    @FindBy(id = "cor-address-change") private WebElement changeCorrespondenceAddressLink;
    @FindBy(id = "cor-email-change") private WebElement changeCorrespondenceEmailLink;
    @FindBy(id = "cor-phone-change") private WebElement changeCorrespondenceTelephoneLink;

    public TesterAuthorisedExaminerViewPage(MotAppDriver driver) {
        super(driver, PAGE_TITLE );
        selfVerify();
    }

    public boolean isChangeNameLinkDisplayed() {
        return PageInteractionHelper.isElementDisplayed(changeNameLink);
    }

    public boolean isChangeTradingNameLinkDisplayed() {
        return PageInteractionHelper.isElementDisplayed(changeTradingNameLink);
    }

    public boolean isChangeAEStatusLinkDisplayed() {
        return PageInteractionHelper.isElementDisplayed(changeAuthStatusLink);
    }

    public boolean isChangeRegOfficeAddressLinkDisplayed() {
        return PageInteractionHelper.isElementDisplayed(changeRegOfficeAddressLink);
    }

    public boolean isChangeRegOfficeEmailLinkDisplayed() {
        return PageInteractionHelper.isElementDisplayed(changeRegOfficeEmailLink);
    }

    public boolean isChangeRegOfficeTelephoneLinkDisplayed() {
        return PageInteractionHelper.isElementDisplayed(changeRegOfficeTelephoneLink);
    }

    public boolean isChangeCorrespondenceAddressLinkDisplayed() {
        return PageInteractionHelper.isElementDisplayed(changeCorrespondenceAddressLink);
    }

    public boolean isChangeCorrespondenceEmailLinkDisplayed() {
        return PageInteractionHelper.isElementDisplayed(changeCorrespondenceEmailLink);
    }

    public boolean isChangeCorrespondenceTelephoneLinkDisplayed() {
        return PageInteractionHelper.isElementDisplayed(changeCorrespondenceTelephoneLink);
    }

    public boolean isChangeDVSAAreaOfficeLinkDisplayed() {
        return PageInteractionHelper.isElementDisplayed(changeDAOLink);
    }

    public boolean isChangeBusinessTypeLinkDisplayed() {
        return PageInteractionHelper.isElementDisplayed(changeBusinessTypeLink);
    }

    public boolean isAEStatusRowDisplayed() {
        return PageInteractionHelper.isElementDisplayed(authStatus);
    }

    public boolean areAllChangeLinksHidden(){
        List<WebElement> webElements = Arrays.asList(
                aeAuthStatus,
                changeNameLink,
                changeTradingNameLink,
                changeBusinessTypeLink,
                changeAuthStatusLink,
                authStatus,
                changeDAOLink,
                changeRegOfficeAddressLink,
                changeRegOfficeEmailLink,
                changeRegOfficeTelephoneLink,
                changeCorrespondenceAddressLink,
                changeCorrespondenceEmailLink,
                changeCorrespondenceTelephoneLink
        );

        for (WebElement webElement: webElements) {
            if(PageInteractionHelper.isElementDisplayed(webElement)){
                return false;
            }
        }

        return true;
    }
}
