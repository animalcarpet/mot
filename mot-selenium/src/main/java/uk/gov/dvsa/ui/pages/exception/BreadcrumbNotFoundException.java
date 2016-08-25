package uk.gov.dvsa.ui.pages.exception;

public class BreadcrumbNotFoundException extends RuntimeException {

    public BreadcrumbNotFoundException(final String message) {
        super(message);
    }
}
