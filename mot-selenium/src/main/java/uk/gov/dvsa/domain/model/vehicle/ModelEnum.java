package uk.gov.dvsa.domain.model.vehicle;

import uk.gov.dvsa.ui.pages.exception.LookupNamingException;

public enum ModelEnum {
    BMW_ALPINA(104420,"01459","ALPINA"),
    FORD_MONDEO(104995,"01698","MONDEO"),
    FORD_STREETKA(105005,"016A2","STREETKA"),
    FUSO_CANTER(105019,"016B0","CANTER"),
    HARLEY_DAVIDSON_FLHC(105059,"016D8","FLHC"),
    HYUNDAI_I40(105514,"0189F","I40"),
    INDIAN_DAKOTA(105532,"018B1","DAKOTA"),
    KAWASAKI_ZRX1100(105738,"0197F","ZRX1100"),
    KAWASAKI_ZRX1200R(105739,"01980","ZRX1200R"),
    MERCEDES_190(106087,"01ADC","190"),
    MERCEDES_300_D(106117,"01AFA","300 D"),
    PIAGGIO_NRG(106690,"01D37","NRG"),
    PORSCHE_BOXSTER(106729,"01D5E","BOXSTER"),
    RENAULT_CLIO(106782,"01D93","CLIO"),
    SUBARU_IMPREZA(107045,"01E9A","IMPREZA"),
    SUZUKI_BALENO(107075,"01EB8","BALENO"),
    SUZUKI_CAPPUCCINO(107077,"01EBA","CAPPUCCINO"),
    VAUXHALL_ASTRA(107390,"01FF3","ASTRA"),
    VOLKSWAGEN_PASSAT(107458,"02037","PASSAT");

    private int id;
    private final String code;
    private final String name;

    private ModelEnum(Integer colourId, String colourCode, String colourName) {
        id = colourId;
        code = colourCode;
        name = colourName;
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

    public static ModelEnum findByName(String name) {
        for(ModelEnum model : values()){
            if( model.getName().equals(name)){
                return model;
            }
        }

        throw new LookupNamingException("ModelEnum " + name + " not found");
    }
}
