package uk.gov.dvsa.ui.pages;

public interface Notification {

    Notification acceptNomination();
    Notification rejectNomination();
    InboxNotificationPage archiveNomination();
    String getConfirmationText();
    String getNotificationText();
    String getTitle();
    boolean isAcceptButtonDisplayed();
    boolean isRejectButtonDisplayed();
    void clickActivateCard();
    void clickOrderCard();
}
