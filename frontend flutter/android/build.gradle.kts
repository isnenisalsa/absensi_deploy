allprojects {
    repositories {
        google()
        mavenCentral()
    }
}

val newBuildDir: Directory =
    rootProject.layout.buildDirectory
        .dir("../../build")
        .get()
rootProject.layout.buildDirectory.value(newBuildDir)

subprojects {
    val newSubprojectBuildDir: Directory = newBuildDir.dir(project.name)
    project.layout.buildDirectory.value(newSubprojectBuildDir)
}

subprojects {
    if (project.name != "app") {
        project.evaluationDependsOn(":app")
    }
}

subprojects {
    plugins.withId("com.android.library") {
        val android = project.extensions.getByType<com.android.build.gradle.BaseExtension>()
        if (android.namespace == null) {
            android.namespace = "com.example." + project.name.replace("-", ".")
        }
        
        // Force JVM target 11 for plugins (libraries)
        android.compileOptions {
            sourceCompatibility = JavaVersion.VERSION_11
            targetCompatibility = JavaVersion.VERSION_11
        }

        // Final fix for "Incorrect package found in source AndroidManifest.xml"
        // This programmatically removes the 'package' attribute from the manifest of the plugins during build
        // to satisfy AGP 8.0+ requirements when a namespace is also specified.
        tasks.matching { it.name.contains("process") && it.name.contains("Manifest") }.configureEach {
            doFirst {
                val manifestFile = file("src/main/AndroidManifest.xml")
                if (manifestFile.exists()) {
                    var content = manifestFile.readText()
                    if (content.contains("package=")) {
                        println("Patching manifest for ${project.name}: Removing deprecated package attribute.")
                        content = content.replace(Regex("""package="[^"]*""""), "")
                        manifestFile.writeText(content)
                    }
                }
            }
        }
    }
    
    plugins.withId("com.android.application") {
        val android = project.extensions.getByType<com.android.build.gradle.BaseExtension>()
        if (android.namespace == null) {
            android.namespace = "com.example." + project.name.replace("-", ".")
        }
        // Note: Main app's compileOptions are already set in its own build.gradle.kts
    }

    // Aligns JVM target versions for all subprojects (especially plugins)
    // to resolve "Inconsistent JVM-target compatibility" errors.
    tasks.withType<org.jetbrains.kotlin.gradle.tasks.KotlinCompile>().configureEach {
        kotlinOptions {
            jvmTarget = "11"
        }
    }
}

tasks.register<Delete>("clean") {
    delete(rootProject.layout.buildDirectory)
}
