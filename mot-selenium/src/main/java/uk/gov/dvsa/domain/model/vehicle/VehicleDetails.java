package uk.gov.dvsa.domain.model.vehicle;

public enum VehicleDetails {
    BMW_ALPINA(100024,"18811","BMW",104420,"01459","ALPINA"),
    FORD_MONDEO(100062,"18837","FORD",104995,"01698","MONDEO"),
    FORD_STREETKA(100062,"18837","FORD",105005,"016A2","STREETKA"),
    FUSO_CANTER(100065,"1883A","FUSO",105019,"016B0","CANTER"),
    HARLEY_DAVIDSON_FLHC(100071,"18840","HARLEY DAVIDSON",105059,"016D8","FLHC"),
    HYUNDAI_I40(100084,"1884D","HYUNDAI",105514,"0189F","I40"),
    INDIAN_DAKOTA(100085,"1884E","INDIAN",105532,"018B1","DAKOTA"),
    KAWASAKI_ZRX1100(100102,"1885F","KAWASAKI",105738,"0197F","ZRX1100"),
    KAWASAKI_ZRX1200R(100102,"1885F","KAWASAKI",105739,"01980","ZRX1200R"),
    MERCEDES_190(100133,"1887E","MERCEDES",106087,"01ADC","190"),
    MERCEDES_300_D(100133,"1887E","MERCEDES",106117,"01AFA","300 D"),
    PIAGGIO_NRG(100167,"188A0","PIAGGIO",106690,"01D37","NRG"),
    PORSCHE_BOXSTER(100169,"188A2","PORSCHE",106729,"01D5E","BOXSTER"),
    RENAULT_CLIO(100176,"188A9","RENAULT",106782,"01D93","CLIO"),
    SUBARU_IMPREZA(100199,"188C0","SUBARU",107045,"01E9A","IMPREZA"),
    SUZUKI_BALENO(100201,"188C2","SUZUKI",107075,"01EB8","BALENO"),
    SUZUKI_CAPPUCCINO(100201,"188C2","SUZUKI",107077,"01EBA","CAPPUCCINO"),
    VAUXHALL_ASTRA(100217,"188D2","VAUXHALL",107390,"01FF3","ASTRA"),
    VOLKSWAGEN_PASSAT(100220,"188D5","VOLKSWAGEN",107458,"02037","PASSAT"),
    OTHER(-1,"other","OTHER",-1,"other","OTHER"); // todo (ABN) this isn't right

    private Integer makeId;
    private final String makeCode;
    private final String makeName;

    private Integer modelId;
    private final String modelCode;
    private final String modelName;

    private VehicleDetails(
            Integer vehicleMakeId,
            String vehicleMakeCode,
            String vehicleMakeName,
            Integer vehicleModelId,
            String vehicleModelCode,
            String vehicleModelName
    ) {
        this.makeId = vehicleMakeId;
        this.makeCode = vehicleMakeCode;
        this.makeName = vehicleMakeName;
        this.modelId = vehicleModelId;
        this.modelCode = vehicleModelCode;
        this.modelName = vehicleModelName;
    }

    public Integer getMakeId() {
        return this.makeId;
    }

    public String getMakeCode() {
        return this.makeCode;
    }

    public String getMakeName() {
        return this.makeName;
    }


    public Integer getModelId() {
        return this.modelId;
    }

    public String getModelCode() {
        return this.modelCode;
    }

    public String getModelName() {
        return this.modelName;
    }

    public static VehicleDetails getRandomData() {
        return values()[(int) (Math.random() * values().length)];
    }
}
