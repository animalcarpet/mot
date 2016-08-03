package uk.gov.dvsa.domain.model.vehicle;

import uk.gov.dvsa.ui.pages.exception.LookupNamingException;

public enum Make {
    BMW(100024,"18811","BMW"),
    FORD(100062,"18837","FORD"),
    FUSO(100065,"1883A","FUSO"),
    HARLEY_DAVIDSON(100071,"18840","HARLEY DAVIDSON"),
    HYUNDAI(100084,"1884D","HYUNDAI"),
    INDIAN(100085,"1884E","INDIAN"),
    KAWASAKI(100102,"1885F","KAWASAKI"),
    MERCEDES(100133,"1887E","MERCEDES"),
    PIAGGIO(100167,"188A0","PIAGGIO"),
    PORSCHE(100169,"188A2","PORSCHE"),
    RENAULT(100176,"188A9","RENAULT"),
    SUBARU(100199,"188C0","SUBARU"),
    SUZUKI(100201,"188C2","SUZUKI"),
    VAUXHALL(100217,"188D2","VAUXHALL"),
    VOLKSWAGEN(100220,"188D5","VOLKSWAGEN");

    private int id;
    private final String code;
    private final String name;

    private Make(Integer makeId, String makeCode, String makeName) {
        id = makeId;
        code = makeCode;
        name = makeName;
    }

    public String getName() {
        return name;
    }

    public String getCode() {
        return code;
    }

    public Integer getId() {
        return id;
    }

    public static Make findByName(String name) {
        for(Make make : values()){
            if( make.getName().equals(name)){
                return make;
            }
        }

        throw new LookupNamingException("Make " + name + " not found");
    }
}
