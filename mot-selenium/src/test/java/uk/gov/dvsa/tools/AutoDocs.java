package uk.gov.dvsa.tools;

import uk.gov.dvsa.helper.Utilities;
import uk.gov.dvsa.tools.test.TestLoader;
import uk.gov.dvsa.tools.test.TestMethod;

import java.io.BufferedWriter;
import java.io.IOException;
import java.nio.file.Files;
import java.nio.file.NoSuchFileException;
import java.nio.file.Path;
import java.nio.file.Paths;
import java.util.List;
import java.util.stream.Collectors;

import static java.nio.file.Files.newBufferedWriter;
import static java.util.stream.Collectors.joining;

public class AutoDocs {
    private final TestLoader testLoader = new TestLoader();
    private static final String HTML_TEMPLATE = "<html><head><link rel=\"stylesheet\" type=\"text/css\" href=\"styles.css\"></head><body>%s</body></html>";
    private static final String HTML_TABLE_TEMPLATE = "<table><thead><tr><th>Test Name</th><th>Test Description</th></tr></thead><tbody>%s</tbody></table>";
    private static final String HTML_TABLE_ROW_TEMPLATE = "<tr><td>%s</td><td>%s</td></tr>";
    private static final String HTML_HEADER_TEMPLATE = "<h2>%s</h2> %s";

    private void writeReportToHtmlFile() throws IOException {
        Path path = createReportPath();
        String userDir = System.getProperty("user.dir");
        try {
            Utilities.Logger.LogInfo("Running:  Generate static test coverage report");
            BufferedWriter out = newBufferedWriter(path);
            out.write(generateTestReport());
            out.close();
            Utilities.Logger.LogInfo(String.format("Complete: Report saved to :%s/%s", userDir, path.toString()));
        } catch (NoSuchFileException e) {
            Utilities.Logger.LogError(String.format("report.html not found in %s", userDir));
        }
    }

    private String generateTestReport() throws IOException {
        return String.format(HTML_TEMPLATE, generateTestReportContent());
    }

    private String generateTestReportContent() throws IOException {
        return testLoader.getTests().entrySet().stream()
            .map(entry -> String.format(HTML_HEADER_TEMPLATE, entry.getKey(), addTestNameAndDescription(entry.getValue())))
            .collect(joining());
    }

    private static Path createReportPath() throws IOException {
        Path directoryPath = Paths.get("target/static");
        Path filePath = directoryPath.resolve("report.html");
        if (!Files.exists(directoryPath)) {
            Files.createDirectory(directoryPath);
            Files.createFile(filePath);
        }
        return filePath;
    }

    private String addTestNameAndDescription(List<TestMethod> listOfMethods) {
            return String.format(HTML_TABLE_TEMPLATE,
                listOfMethods.stream()
                    .map(i -> String.format(HTML_TABLE_ROW_TEMPLATE, i.getName(), i.getDescription()))
                    .collect(joining()));
    }

    public static void main(String...args)  {
        try {
            new AutoDocs().writeReportToHtmlFile();
        } catch (IOException e) {
            Utilities.Logger.LogError("Failed to execute main method", e);
        }
    }
}
