package uk.gov.dvsa.domain.shared.role;

public enum DvsaRoles implements Role {
    VEHICLE_EXAMINER ("VEHICLE-EXAMINER"),
    AO1("DVSA-AREA-OFFICE-1");

    private String name;

    DvsaRoles(String name) {
        this.name = name;
    }

    public String getRoleName(){
        return name;
    }
}
