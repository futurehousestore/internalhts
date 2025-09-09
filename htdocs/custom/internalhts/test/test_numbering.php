<?php
/* Copyright (C) 2024
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

/**
 * \file    test/test_numbering.php
 * \ingroup internalhts
 * \brief   Test script for numbering module
 */

// Load Dolibarr environment
$res = 0;
// Try main.inc.php into web root known defined into CONTEXT_DOCUMENT_ROOT (not always defined)
if (!$res && !empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) {
    $res = @include $_SERVER["CONTEXT_DOCUMENT_ROOT"]."/main.inc.php";
}
// Try main.inc.php into web root detected using web root calculated from SCRIPT_FILENAME
$tmp = empty($_SERVER['SCRIPT_FILENAME']) ? '' : $_SERVER['SCRIPT_FILENAME']; $tmp2 = realpath(__FILE__); $i = strlen($tmp) - 1; $j = strlen($tmp2) - 1;
while ($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i] == $tmp2[$j]) {
    $i--; $j--;
}
if (!$res && $i > 0 && file_exists(substr($tmp, 0, ($i + 1))."/main.inc.php")) {
    $res = @include substr($tmp, 0, ($i + 1))."/main.inc.php";
}
if (!$res && $i > 0 && file_exists(dirname(substr($tmp, 0, ($i + 1)))."/main.inc.php")) {
    $res = @include dirname(substr($tmp, 0, ($i + 1)))."/main.inc.php";
}
// Try main.inc.php using relative path
if (!$res && file_exists("../../main.inc.php")) {
    $res = @include "../../main.inc.php";
}
if (!$res && file_exists("../../../main.inc.php")) {
    $res = @include "../../../main.inc.php";
}
if (!$res && file_exists("../../../../main.inc.php")) {
    $res = @include "../../../../main.inc.php";
}
if (!$res) {
    die("Include of main fails");
}

dol_include_once('/internalhts/class/internalhts.class.php');
dol_include_once('/internalhts/core/modules/internalhts/mod_internalhts_standard.php');

/**
 * Unit tests for InternalHTS numbering
 */
class InternalHTSNumberingTest
{
    private $db;
    private $errors = array();
    private $successes = array();

    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * Test numbering module
     */
    public function testNumberingModule()
    {
        echo "<h2>Testing Numbering Module</h2>\n";

        // Test numbering module instantiation
        $numbering = new mod_internalhts_standard($this->db);
        if (!$numbering) {
            $this->errors[] = "Failed to instantiate numbering module";
            return false;
        }
        $this->successes[] = "Numbering module instantiated successfully";

        // Test example generation
        $example = $numbering->getExample();
        if (empty($example)) {
            $this->errors[] = "Failed to generate example number";
            return false;
        }
        $this->successes[] = "Example number generated: " . $example;

        // Verify example format
        if (!preg_match('/^IH-\d{4}-\d{5}$/', $example)) {
            $this->errors[] = "Example number does not match expected format (IH-YYYY-NNNNN): " . $example;
            return false;
        }
        $this->successes[] = "Example number matches expected format";

        // Test with dummy object
        $object = new InternalHTS($this->db);
        $object->initAsSpecimen();
        
        $nextValue = $numbering->getNextValue($object);
        if (empty($nextValue)) {
            $this->errors[] = "Failed to generate next value";
            return false;
        }
        $this->successes[] = "Next value generated: " . $nextValue;

        // Verify next value format
        if (!preg_match('/^IH-\d{4}-\d{5}$/', $nextValue)) {
            $this->errors[] = "Next value does not match expected format (IH-YYYY-NNNNN): " . $nextValue;
            return false;
        }
        $this->successes[] = "Next value matches expected format";

        return true;
    }

    /**
     * Test line calculations
     */
    public function testLineCalculations()
    {
        echo "<h2>Testing Line Calculations</h2>\n";

        dol_include_once('/internalhts/class/internalhtsline.class.php');

        $line = new InternalHTSLine($this->db);
        
        // Test basic calculation
        $qty = 10;
        $unit_price = 25.50;
        $customs_unit_value = 20.00;
        
        $expected_total_ht = $qty * $unit_price; // 255.00
        $expected_total_customs = $qty * $customs_unit_value; // 200.00
        
        $line->qty = $qty;
        $line->unit_price = $unit_price;
        $line->customs_unit_value = $customs_unit_value;
        $line->total_ht = $qty * $unit_price;
        $line->total_customs_value = $qty * $customs_unit_value;
        
        if ($line->total_ht != $expected_total_ht) {
            $this->errors[] = "Total HT calculation error. Expected: $expected_total_ht, Got: " . $line->total_ht;
            return false;
        }
        $this->successes[] = "Total HT calculation correct: " . $line->total_ht;
        
        if ($line->total_customs_value != $expected_total_customs) {
            $this->errors[] = "Total customs value calculation error. Expected: $expected_total_customs, Got: " . $line->total_customs_value;
            return false;
        }
        $this->successes[] = "Total customs value calculation correct: " . $line->total_customs_value;

        // Test decimal calculations
        $qty = 2.5;
        $unit_price = 10.99;
        $customs_unit_value = 8.75;
        
        $expected_total_ht = $qty * $unit_price; // 27.475
        $expected_total_customs = $qty * $customs_unit_value; // 21.875
        
        $line->qty = $qty;
        $line->unit_price = $unit_price;
        $line->customs_unit_value = $customs_unit_value;
        $line->total_ht = $qty * $unit_price;
        $line->total_customs_value = $qty * $customs_unit_value;
        
        if (abs($line->total_ht - $expected_total_ht) > 0.001) {
            $this->errors[] = "Decimal total HT calculation error. Expected: $expected_total_ht, Got: " . $line->total_ht;
            return false;
        }
        $this->successes[] = "Decimal total HT calculation correct: " . $line->total_ht;
        
        if (abs($line->total_customs_value - $expected_total_customs) > 0.001) {
            $this->errors[] = "Decimal total customs value calculation error. Expected: $expected_total_customs, Got: " . $line->total_customs_value;
            return false;
        }
        $this->successes[] = "Decimal total customs value calculation correct: " . $line->total_customs_value;

        return true;
    }

    /**
     * Test HTS operations
     */
    public function testHTSOperations()
    {
        echo "<h2>Testing HTS Operations</h2>\n";

        dol_include_once('/internalhts/class/hts.class.php');

        $hts = new HTS($this->db);
        
        // Test CSV import functionality (dry run)
        $csvdata = array(
            array('USA', '1234.56.78', 'Test HTS code 1'),
            array('CAN', '9876.54.32', 'Test HTS code 2'),
            array('MEX', '5555.55.55', 'Test HTS code 3'),
            array('', '1111.11.11', 'Invalid country'), // Should fail
            array('INVALID', '2222.22.22', 'Invalid country code'), // Should fail
        );
        
        global $user;
        $result = $hts->importFromCSV($user, $csvdata, true); // Dry run
        
        if ($result['success'] != 3) {
            $this->errors[] = "CSV import test failed. Expected 3 successes, got: " . $result['success'];
            return false;
        }
        $this->successes[] = "CSV import dry run successful: " . $result['success'] . " records";
        
        if (count($result['errors']) != 2) {
            $this->errors[] = "CSV import test failed. Expected 2 errors, got: " . count($result['errors']);
            return false;
        }
        $this->successes[] = "CSV import error handling correct: " . count($result['errors']) . " errors";

        return true;
    }

    /**
     * Run all tests
     */
    public function runAllTests()
    {
        echo "<h1>InternalHTS Unit Tests</h1>\n";
        
        $allPassed = true;
        
        $allPassed &= $this->testNumberingModule();
        $allPassed &= $this->testLineCalculations();
        $allPassed &= $this->testHTSOperations();
        
        echo "<h2>Test Results</h2>\n";
        
        if (!empty($this->successes)) {
            echo "<h3 style='color: green;'>Successes (" . count($this->successes) . ")</h3>\n";
            echo "<ul>\n";
            foreach ($this->successes as $success) {
                echo "<li style='color: green;'>✓ " . htmlspecialchars($success) . "</li>\n";
            }
            echo "</ul>\n";
        }
        
        if (!empty($this->errors)) {
            echo "<h3 style='color: red;'>Errors (" . count($this->errors) . ")</h3>\n";
            echo "<ul>\n";
            foreach ($this->errors as $error) {
                echo "<li style='color: red;'>✗ " . htmlspecialchars($error) . "</li>\n";
            }
            echo "</ul>\n";
        }
        
        if ($allPassed) {
            echo "<h2 style='color: green;'>All tests passed!</h2>\n";
        } else {
            echo "<h2 style='color: red;'>Some tests failed!</h2>\n";
        }
        
        return $allPassed;
    }
}

// Only run if called directly (not included)
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    // Set content type for HTML output
    if (php_sapi_name() !== 'cli') {
        header('Content-Type: text/html; charset=UTF-8');
        echo "<!DOCTYPE html><html><head><title>InternalHTS Tests</title></head><body>\n";
    }
    
    // Check if module is enabled
    if (!isModEnabled('internalhts')) {
        echo "<h1 style='color: red;'>InternalHTS module is not enabled!</h1>\n";
        exit(1);
    }
    
    $test = new InternalHTSNumberingTest($db);
    $result = $test->runAllTests();
    
    if (php_sapi_name() !== 'cli') {
        echo "</body></html>\n";
    }
    
    exit($result ? 0 : 1);
}