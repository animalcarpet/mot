package uk.gov.dvsa.domain.model.vehicle;

import uk.gov.dvsa.ui.pages.exception.LookupNamingException;

public enum Colour {
    NoOtherColour(0, "", "No other colour"),
    Beige(1, "S", "Beige"),
    Black(2, "P", "Black"),
    Bronze(3, "B", "Bronze"),
    Brown(4, "A", "Brown"),
    Cream(5, "V", "Cream"),
    Gold(6, "G", "Gold"),
    Green(7, "H", "Green"),
    Grey(8, "L", "Grey"),
    Maroon(9, "T", "Maroon"),
    Purple(10, "K", "Purple"),
    Orange(11, "E", "Orange"),
    Pink(12, "D", "Pink"),
    Red(13, "C", "Red"),
    Silver(14, "M", "Silver"),
    Turquoise(15, "U", "Turquoise"),
    White(16, "N", "White"),
    Yellow(17, "F", "Yellow"),
    MultiColour(18, "R", "Multi-colour"),
    NotStated(19, "W", "Not Stated"),
    Blue(20, "J", "Blue");

    private int id;
    private final String code;
    private final String name;

    private Colour(Integer colourId, String colourCode, String colourName) {
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

    public static Colour findByName(String name) {
        for(Colour colour : values()){
            if( colour.getName().equals(name)){
                return colour;
            }
        }

        throw new LookupNamingException("Colour " + name + " not found");
    }
}
