package uk.gov.dvsa.ui.pages.events;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.FormDataHelper;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;

public class CreateNewEventPageTwo extends Page {

    private static final String PAGE_TITLE = "Record an event outcome";

    @FindBy(id = "outcomeCode") private WebElement eventOutcome;
    @FindBy(id = "notes") private WebElement notesSection;
    @FindBy(id = "continue") private WebElement continueButton;

    public CreateNewEventPageTwo(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    public NewEventSummaryPage submitEventOutcomeAndDescription( String note) {
        FormDataHelper.selectFromDropDownByValue(eventOutcome, "NAPRJ");
        FormDataHelper.enterText(notesSection, note);
        continueButton.click();
        return new NewEventSummaryPage(driver);
    }

    @Override
    public boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(getTitle(), PAGE_TITLE);
    }
}
