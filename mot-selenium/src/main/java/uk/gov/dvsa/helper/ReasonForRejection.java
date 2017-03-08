package uk.gov.dvsa.helper;

public class ReasonForRejection {

    public final int reasonId;
    public final String testItemCategoryName;
    public final String reasonDescription;
    public final String advisoryText;
    public final String type;

    public ReasonForRejection(int reasonId, String testItemCategoryName, String reasonDescription,
                              String advisoryText, String failureType) {
        super();
        this.reasonId = reasonId;
        this.testItemCategoryName = testItemCategoryName;
        this.reasonDescription = reasonDescription;
        this.advisoryText = advisoryText;
        this.type = failureType;
    }

    // Lamps, Reflectors and Electrical Equipment >> Horn
    public static final ReasonForRejection HORN_CONTROL_MISSING =
            new ReasonForRejection(7162, "Lamps, Reflectors and Electrical Equipment > Horn",
                    "control missing", "Test failure", "FAIL");

    //Warning Lamp
    public static final ReasonForRejection WARNING_LAMP_MISSING =
            new ReasonForRejection(114, "Motorcycle lighting and signalling > Stop lamp", "missing",
                    "Test failure", "FAIL");

    //Brakes, Brake performance, Brake performance not tested
    public static final ReasonForRejection BRAKE_PERFORMANCE_NOT_TESTED =
            new ReasonForRejection(10102, "Brakes > Brake performance", "not tested",
                    "Test failure", "FAIL");

    //Body, Structure and General Items
    public static final ReasonForRejection BODY_CORROSION =
            new ReasonForRejection(8460, "Body, Structure and General Items > Body condition", "missing",
                    "Test advisory", "ADVISORY");

    //Brakes, ABS
    public static final ReasonForRejection BRAKES_COMPONENT_MISSING =
            new ReasonForRejection(1125, "Brakes > Abs", "missing",
                    "Test PRS", "PRS");
}
