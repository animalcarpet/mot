package uk.gov.dvsa.helper;

import org.apache.commons.codec.binary.Base64;
import org.joda.time.DateTime;
import org.joda.time.DateTimeZone;
import org.joda.time.format.DateTimeFormat;
import org.testng.Reporter;

import java.io.PrintWriter;
import java.io.StringWriter;
import java.text.Format;
import java.text.ParseException;
import java.text.SimpleDateFormat;
import java.util.Calendar;
import java.util.Date;

public class Utilities {

    public static class Logger {

        /**
         * Logs a message in TestNG and Standard log<p>
         * <b>RETURNS:</b><p>
         * N/A
         */
        public static void LogInfo(String logText) {
            String logInfo = ("INFO: [" + logText + "]");
            //Output to TestNG log and Standard log
            Reporter.log(logInfo, true);
        }

        /**
         * Logs an error to TestNG and Standard log<p>
         * <b>RETURNS:</b><p>
         * N/A
         */
        public static void LogError(String logText) {
            String logErr = ("ERROR: [" + logText + "]");
            //Output to TestNG log and Standard log
            Reporter.log(logErr, true);
        }

        /**
         * Logs an error and prints the exception details to TestNG and Standard log<p>
         * <b>RETURNS:</b><p>
         * N/A
         */
        public static void LogError(String logText, Exception e) {
            StringWriter stringWriter = new StringWriter();
            PrintWriter printWriter = new PrintWriter(stringWriter);
            e.printStackTrace(printWriter);

            String logErr = ("ERROR: [" + logText + "]\n" +
                    stringWriter.toString());

            //Output to TestNG log and Standard log
            Reporter.log(logErr, true);
        }
    }

    public static String convertBase64ToString(String s) {
        byte[] decoded = Base64.decodeBase64(s.getBytes());
        return new String(decoded);
    }

    public static String dateTimeToString(DateTime date) {
        return date.withZone(DateTimeZone.UTC)
                .toString(DateTimeFormat.forPattern("YYYY-MM-dd"));
    }

    public static Date getDate(int year, int month, int day) {
        Calendar c = Calendar.getInstance();
        c.set(year, month, day);
        return new Date(c.getTimeInMillis());
    }

    public static DateTime getPreviousMonthsDate() {
        DateTime dt = DateTime.now();
        return dt.minusMonths(1);
    }
    
    public static String getTodaysDate() {
        return String.valueOf(DateTime.now().getDayOfMonth());
    }
    
    public static String getFutureDate() {
        Calendar cal = Calendar.getInstance();
        cal.add(Calendar.DATE, 1);
        SimpleDateFormat format = new SimpleDateFormat("yyyy-MM-dd");
        String formatted = format.format(cal.getTime());
        return formatted;
    }

    public static String convertDateToGDSFormat(DateTime date) {
        return (date.toString(DateTimeFormat.forPattern("d MMMM YYYY")));
    }

    public static String  getSystemDateAndTime(){
        Calendar date=Calendar.getInstance();
        date.setTime(new Date());
        Format f = new SimpleDateFormat("ddMMyyhhmmss");
        String d = f.format(date.getTime());
        return d;

    }

    public static long getTimeDifference(String appTime){
        Calendar date=Calendar.getInstance();
        date.setTime(new Date());

        SimpleDateFormat formatter = new SimpleDateFormat("h:mmaa");
        String d = formatter.format(date.getTime());
        Date appDate = null, sysDate = null;
        try{
            appDate = formatter.parse(appTime);
            sysDate = formatter.parse(d);
        }
        catch (ParseException e){
            e.printStackTrace();
        }
        long diff = 0;
        if(appDate.getTime() >= sysDate.getTime())
            diff = appDate.getTime()-sysDate.getTime();
        else
            diff = sysDate.getTime() - appDate.getTime();
        return diff/(60 * 1000);
    }
}
