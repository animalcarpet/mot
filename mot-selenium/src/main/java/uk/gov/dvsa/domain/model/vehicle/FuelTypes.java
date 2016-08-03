package uk.gov.dvsa.domain.model.vehicle;

import uk.gov.dvsa.ui.pages.exception.LookupNamingException;

public enum FuelTypes {
    Petrol(1, "PE", "Petrol"),
    Diesel(2, "DI", "Diesel"),
    Electric(3, "EL", "Electric"),
    Steam(4, "ST", "Steam"),
    LPG(5, "LP", "LPG"),
    CNG(6, "CN", "CNG"),
    LNG(7, "LN", "LNG"),
    FuelCells(8, "FC", "Fuel Cells"),
    Other(9, "OT", "Other"),
    Gas(10, "GA", "Gas"),
    GasBiFuel(11, "GB", "Gas Bi-Fuel"),
    HybridElectric(12, "HY", "Hybrid Electric (Clean)"),
    GasDiesel(13, "GD", "Gas Diesel"),
    ElectricDiesel(14, "ED", "Electric Diesel");

    private int id;
    private final String name;
    private final String code;

    private FuelTypes(Integer fuelTypeId, String fuelTypeCode, String fuelTypeName) {
        id = fuelTypeId;
        code = fuelTypeCode;
        name = fuelTypeName;
    }

    public Integer getId() {
        return id;
    }

    public String getCode() {
        return code;
    }

    public String getName() {
        return name;
    }

    public static FuelTypes getRandomFuelType() {
        return values()[(int) (Math.random() * values().length)];
    }

    public static FuelTypes findByName(String name) {
        for(FuelTypes fuelTypes : values()){
            if( fuelTypes.getName().equals(name)){
                return fuelTypes;
            }
        }

        throw new LookupNamingException("FuelType: " + name + " not found");
    }
}
