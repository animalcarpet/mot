package uk.gov.dvsa.helper;

public enum CompanyType {
    Company("RC", "Company"),
    Partnership("P", "Partnership"),
    SoleTrader("ST","Sole trader"),
    PublicBody("PA","Public body");

    private String name;
    private String value;

    CompanyType(String name, String value) {
        this.name = name;
        this.value = value;
    }

    public String getName() {
        return name;
    }

    public String getValue() {
        return value;
    }
}
