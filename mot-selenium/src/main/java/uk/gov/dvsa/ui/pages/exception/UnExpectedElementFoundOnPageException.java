package uk.gov.dvsa.ui.pages.exception;

public class UnExpectedElementFoundOnPageException extends RuntimeException {

    public UnExpectedElementFoundOnPageException(String message) {
        super(message);
    }
}
