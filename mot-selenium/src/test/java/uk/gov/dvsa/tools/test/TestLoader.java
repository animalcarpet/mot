package uk.gov.dvsa.tools.test;

import org.reflections.Reflections;
import org.reflections.scanners.ResourcesScanner;
import org.reflections.scanners.SubTypesScanner;
import org.reflections.util.ClasspathHelper;
import org.reflections.util.ConfigurationBuilder;
import org.reflections.util.FilterBuilder;
import org.testng.annotations.Test;
import uk.gov.dvsa.ui.DslTest;

import java.util.*;
import java.util.stream.Collectors;

public class TestLoader {
    public Map<String, List<TestMethod>> getTests() {
        return getClassesWithTestMethods();
    }

    private Map<String, List<TestMethod>> getClassesWithTestMethods() {
        String pkg = "uk.gov.dvsa";
        return getClassSet(pkg).stream().map(aClass ->
                new AbstractMap.SimpleEntry<>(aClass.getSimpleName(), Arrays.stream(aClass.getDeclaredMethods())
                        .filter(method ->
                                Arrays.stream(method.getAnnotations())
                                        .anyMatch(annotation -> annotation.annotationType().equals(Test.class)))
                        .map(method ->
                                new TestMethod(method.getName(),
                                        TestMethodDescriptionHelper
                                            .useNameAsDescriptionWhereEmpty(
                                                method.getName(), method.getAnnotation(Test.class).description())))
                        .collect(Collectors.toList())))
            .collect(Collectors.toMap(item -> item.getKey(), item -> item.getValue()));
    }

    private Set<Class<? extends DslTest>> getClassSet(String pkg) {
        Reflections reflections = new Reflections(
                new ConfigurationBuilder()
                .setScanners(new SubTypesScanner(false), new ResourcesScanner())
                .setUrls(ClasspathHelper.forClassLoader(ClasspathHelper.classLoaders(new ClassLoader[0])))
                .filterInputsBy(new FilterBuilder().includePackage(pkg)));

        return reflections.getSubTypesOf(DslTest.class);
    }
}
