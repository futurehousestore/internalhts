<?php
/**
 * Simple validation script for InternalHTS module
 * This can be run without a full Dolibarr installation to validate basic structure
 */

echo "InternalHTS Module Structure Validation\n";
echo "=====================================\n\n";

$errors = 0;
$warnings = 0;
$successes = 0;

$baseDir = __DIR__ . '/htdocs/custom/internalhts';

// Check required directories
$requiredDirs = [
    'admin',
    'api/class',
    'class',
    'core/modules',
    'core/modules/internalhts',
    'core/modules/internalhts/doc',
    'css',
    'langs/en_US',
    'lib',
    'sql',
    'test'
];

echo "Checking directory structure...\n";
foreach ($requiredDirs as $dir) {
    $fullPath = $baseDir . '/' . $dir;
    if (is_dir($fullPath)) {
        echo "✓ Directory exists: $dir\n";
        $successes++;
    } else {
        echo "✗ Missing directory: $dir\n";
        $errors++;
    }
}

// Check required files
$requiredFiles = [
    'core/modules/modInternalHTS.class.php',
    'core/modules/internalhts/modules_internalhts.php',
    'core/modules/internalhts/mod_internalhts_standard.php',
    'core/modules/internalhts/doc/pdf_internalhts.modules.php',
    'class/internalhts.class.php',
    'class/internalhtsline.class.php',
    'class/hts.class.php',
    'sql/llx_internalhts.sql',
    'sql/migrate-0.1.sql',
    'langs/en_US/internalhts.lang',
    'css/internalhts.css.php',
    'lib/internalhts.lib.php',
    'admin/setup.php',
    'admin/hts_import.php',
    'admin/about.php',
    'api/class/api_internalhts.class.php',
    'list.php',
    'card.php',
    'internalhtsindex.php',
    'test/test_numbering.php'
];

echo "\nChecking required files...\n";
foreach ($requiredFiles as $file) {
    $fullPath = $baseDir . '/' . $file;
    if (file_exists($fullPath)) {
        echo "✓ File exists: $file\n";
        $successes++;
    } else {
        echo "✗ Missing file: $file\n";
        $errors++;
    }
}

// Check PHP syntax in key files
echo "\nChecking PHP syntax...\n";
$phpFiles = [
    'core/modules/modInternalHTS.class.php',
    'class/internalhts.class.php',
    'class/internalhtsline.class.php',
    'class/hts.class.php',
    'list.php',
    'card.php'
];

foreach ($phpFiles as $file) {
    $fullPath = $baseDir . '/' . $file;
    if (file_exists($fullPath)) {
        $output = [];
        $return_var = 0;
        exec("php -l \"$fullPath\" 2>&1", $output, $return_var);
        if ($return_var === 0) {
            echo "✓ Syntax OK: $file\n";
            $successes++;
        } else {
            echo "✗ Syntax error in $file:\n";
            echo "  " . implode("\n  ", $output) . "\n";
            $errors++;
        }
    }
}

// Check for common patterns and requirements
echo "\nChecking code patterns...\n";

// Check if module descriptor has proper structure
$modFile = $baseDir . '/core/modules/modInternalHTS.class.php';
if (file_exists($modFile)) {
    $content = file_get_contents($modFile);
    
    if (strpos($content, 'class modInternalHTS extends DolibarrModules') !== false) {
        echo "✓ Module descriptor class properly defined\n";
        $successes++;
    } else {
        echo "✗ Module descriptor class not properly defined\n";
        $errors++;
    }
    
    if (strpos($content, '$this->numero = 500000') !== false) {
        echo "✓ Module number defined\n";
        $successes++;
    } else {
        echo "! Module number should be defined (found in file but checking pattern)\n";
        $warnings++;
    }
    
    if (strpos($content, '$this->rights') !== false) {
        echo "✓ Permissions defined\n";
        $successes++;
    } else {
        echo "✗ Permissions not defined\n";
        $errors++;
    }
    
    if (strpos($content, '$this->menu') !== false) {
        echo "✓ Menu entries defined\n";
        $successes++;
    } else {
        echo "✗ Menu entries not defined\n";
        $errors++;
    }
}

// Check numbering module
$numFile = $baseDir . '/core/modules/internalhts/mod_internalhts_standard.php';
if (file_exists($numFile)) {
    $content = file_get_contents($numFile);
    
    if (strpos($content, 'IH-') !== false && strpos($content, 'YYYY') !== false) {
        echo "✓ Numbering format IH-YYYY-NNNNN implemented\n";
        $successes++;
    } else {
        echo "✗ Expected numbering format not found\n";
        $errors++;
    }
}

// Check database schema
$sqlFile = $baseDir . '/sql/llx_internalhts.sql';
if (file_exists($sqlFile)) {
    $content = file_get_contents($sqlFile);
    
    $expectedTables = [
        'llx_internalhts_hts',
        'llx_internalhts_productmap', 
        'llx_internalhts_doc',
        'llx_internalhts_docline'
    ];
    
    $foundTables = 0;
    foreach ($expectedTables as $table) {
        if (strpos($content, "CREATE TABLE $table") !== false) {
            $foundTables++;
        }
    }
    
    if ($foundTables == count($expectedTables)) {
        echo "✓ All required database tables defined\n";
        $successes++;
    } else {
        echo "✗ Missing database tables (found $foundTables of " . count($expectedTables) . ")\n";
        $errors++;
    }
}

// Check CSS for dark mode support
$cssFile = $baseDir . '/css/internalhts.css.php';
if (file_exists($cssFile)) {
    $content = file_get_contents($cssFile);
    
    if (strpos($content, '@media (prefers-color-scheme: dark)') !== false || 
        strpos($content, 'body.dark') !== false) {
        echo "✓ Dark mode CSS support implemented\n";
        $successes++;
    } else {
        echo "! Dark mode CSS support not detected\n";
        $warnings++;
    }
}

// Summary
echo "\n" . str_repeat("=", 40) . "\n";
echo "VALIDATION SUMMARY\n";
echo str_repeat("=", 40) . "\n";
echo "Successes: $successes\n";
echo "Warnings:  $warnings\n";
echo "Errors:    $errors\n";

if ($errors == 0) {
    echo "\n✓ Module structure validation PASSED\n";
    echo "The InternalHTS module appears to be properly structured.\n";
    exit(0);
} else {
    echo "\n✗ Module structure validation FAILED\n";
    echo "Please fix the errors above before using the module.\n";
    exit(1);
}