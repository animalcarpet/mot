package uk.gov.dvsa.helper.enums;

public enum Comparison {
    DISREGARD("Disregard", "0"),
    OVERRULED_MARGINALLY_WRONG("0 - Overruled, marginally wrong", "0"),
    OBVIOUSLY_WRONG("5 - Obviously wrong", "5"),
    SIGNIFICANTLY_WRONG("10 - Significantly wrong", "10"),
    NO_DEFECT("20 - No defect", "20"),
    NOT_TESTABLE("20 - Not testable", "20"),
    EXS_CORR_WEAR_DAMAGE_MISSED("30 - Exs. corr/wear/damage missed", "30"),
    RISK_OF_INJURY_MISSED("40 - Risk of injury missed", "40");

    private String text;
    private String scoreValue;

    Comparison(String text, String scoreValue) {
        this.text = text;
        this.scoreValue = scoreValue;
    }

    @Override
    public String toString(){
        return text;
    }

    public String scoreValue(){
        return scoreValue;
    }
}
