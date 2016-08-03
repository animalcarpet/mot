package uk.gov.dvsa.helper.enums;


public class BrakeTestConstants {

    public enum BrakeTestType {
        Decelerometer("DECEL", "Decelerometer"),
        Floor("FLOOR", "Floor"),
        Gradient("GRADT", "Gradient"),
        Plate("PLATE", "Plate"),
        Roller("ROLLR", "Roller"),
        ClassB("ClassB", "ClassB");

        private String id;
        private String description;

        BrakeTestType(String id, String description) {
            this.id = id;
            this.description = description;
        }

        public String getId() {
            return this.id;
        }

        public String getDescription() {
            return this.description;
        }

        public String toString() {
            return getDescription();
        }
    }


    public enum BrakeLineType {Single, Dual}


    public enum VehicleType {Passenger, Goods}


    public enum SingleWheelPosition {Front, Rear}


    public enum ParkingBrakeOperatedOn {One, Two}


    public enum ServiceBrakeControls {One, Two}


    public enum ParkingBrakeLocation {Front, Rear}


    public enum FieldType {Input, Dropdown, Radiobutton, Checkbox}

    public enum NumberOfAxles {
        Two("two", "2 axles", 2), Three("three", "3 axles", 3);

        private String id;
        private String description;
        private int intValue;

        private NumberOfAxles(String id, String description, int intValue) {
            this.id = id;
            this.description = description;
            this.intValue = intValue;
        }

        public String getId() {
            return this.id;
        }

        public String getDescription() {
            return this.description;
        }

        public int getNumberOfAxles() {
            return this.intValue;
        }

        public String toString() {
            return getDescription();
        }
    }
}
