package uk.gov.dvsa.domain.shared.role;


public enum TradeRoles implements Role {

    TESTER ("TESTER"),
    SITE_ADMIN ("SITE-ADMIN"),
    SITE_MANAGER("SITE-MANAGER"),
    AUTHORISED_EXAMINER_DESIGNATED_MANAGER ("AUTHORISED-EXAMINER-DESIGNATED-MANAGER"),
    AUTHORISED_EXAMINER_DELEGATE("AUTHORISED-EXAMINER-DELEGATE");

    private String name;

    TradeRoles(String name) {
        this.name = name;
    }

    public String getRoleName(){
        return name;
    }
}
