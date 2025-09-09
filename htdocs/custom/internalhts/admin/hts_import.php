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
 * \file       admin/hts_import.php
 * \ingroup    internalhts
 * \brief      HTS Import page.
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

global $langs, $user;

dol_include_once('/internalhts/lib/internalhts.lib.php');
dol_include_once('/internalhts/class/hts.class.php');

// Translations
$langs->loadLangs(array("admin", "internalhts@internalhts"));

// Initialize technical object to manage hooks of page. Note that conf->hooks_modules contains array of hook context
$hookmanager->initHooks(array('internalhtssetup', 'globalsetup'));

// Access control
if (!$user->admin) {
    accessforbidden();
}

// Parameters
$action = GETPOST('action', 'aZ09');
$backtopage = GETPOST('backtopage', 'alpha');

/*
 * Actions
 */

$error = 0;
$importResults = array();

if ($action == 'import') {
    $dryrun = GETPOST('dryrun', 'int') ? true : false;
    
    // Check if file was uploaded
    if (!empty($_FILES['csvfile']['tmp_name'])) {
        $tmpfile = $_FILES['csvfile']['tmp_name'];
        $filename = $_FILES['csvfile']['name'];
        
        // Check file extension
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        if (strtolower($ext) != 'csv') {
            setEventMessages('File must be a CSV file', null, 'errors');
            $error++;
        }
        
        if (!$error) {
            // Read CSV file
            $csvdata = array();
            if (($handle = fopen($tmpfile, "r")) !== FALSE) {
                while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                    $csvdata[] = $data;
                }
                fclose($handle);
            } else {
                setEventMessages('Error reading CSV file', null, 'errors');
                $error++;
            }
            
            if (!$error && !empty($csvdata)) {
                $hts = new HTS($db);
                $importResults = $hts->importFromCSV($user, $csvdata, $dryrun);
                
                if ($importResults['success'] > 0) {
                    if ($dryrun) {
                        setEventMessages($importResults['success'] . ' records would be imported (dry run)', null, 'mesgs');
                    } else {
                        setEventMessages($importResults['success'] . ' HTS codes imported successfully', null, 'mesgs');
                    }
                }
                
                if (!empty($importResults['errors'])) {
                    foreach ($importResults['errors'] as $errorMsg) {
                        setEventMessages($errorMsg, null, 'warnings');
                    }
                }
            }
        }
    } else {
        setEventMessages('Please select a CSV file to import', null, 'errors');
        $error++;
    }
}

/*
 * View
 */

$form = new Form($db);

$help_url = '';
$page_name = "HTSImport";

llxHeader('', $langs->trans($page_name), $help_url);

// Subheader
$linkback = '<a href="'.($backtopage ? $backtopage : DOL_URL_ROOT.'/admin/modules.php?restore_lastsearch_values=1').'">'.$langs->trans("BackToModuleList").'</a>';

print load_fiche_titre($langs->trans($page_name), $linkback, 'title_setup');

// Configuration header
$head = internalhtsAdminPrepareHead();
print dol_get_fiche_head($head, 'hts_import', $langs->trans($page_name), -1, "internalhts@internalhts");

// Import form
print '<form method="POST" action="'.$_SERVER["PHP_SELF"].'" enctype="multipart/form-data">';
print '<input type="hidden" name="token" value="'.newToken().'">';
print '<input type="hidden" name="action" value="import">';

print '<div class="div-table-responsive-no-min">';
print '<table class="noborder centpercent">';
print '<tr class="liste_titre">';
print '<td colspan="2">'.$langs->trans("HTSImportTitle").'</td>';
print '</tr>';

print '<tr class="oddeven">';
print '<td width="200">'.$langs->trans("CSVFile").'</td>';
print '<td>';
print '<input type="file" name="csvfile" accept=".csv" required>';
print '</td>';
print '</tr>';

print '<tr class="oddeven">';
print '<td>'.$langs->trans("DryRun").'</td>';
print '<td>';
print '<input type="checkbox" name="dryrun" value="1" checked> '.$langs->trans("DryRun").' ('.$langs->trans("TestImportWithoutSaving").')';
print '</td>';
print '</tr>';

print '</table>';
print '</div>';

print '<div class="center">';
print '<input type="submit" class="button" value="'.$langs->trans("Import").'">';
print '</div>';

print '</form>';

// Show import format help
print '<br>';
print '<div class="info">';
print '<strong>'.$langs->trans("ExpectedFormat").':</strong><br>';
print 'country,code,description<br>';
print 'USA,1234.56.78,Description of HTS code<br>';
print 'CAN,9876.54.32,Another HTS code description<br>';
print '</div>';

// Show import results
if (!empty($importResults)) {
    print '<br>';
    print '<div class="hts-import-results">';
    
    if ($importResults['success'] > 0) {
        print '<div class="hts-import-success">';
        print '<strong>'.$langs->trans("SuccessfulImports").':</strong> ' . $importResults['success'];
        print '</div>';
        
        if (!empty($importResults['imported'])) {
            print '<div style="max-height: 300px; overflow-y: auto; border: 1px solid #ddd; margin-top: 10px; padding: 10px;">';
            print '<table class="noborder centpercent">';
            print '<tr class="liste_titre">';
            print '<td>'.$langs->trans("Country").'</td>';
            print '<td>'.$langs->trans("HTSCode").'</td>';
            print '<td>'.$langs->trans("Description").'</td>';
            print '</tr>';
            
            foreach ($importResults['imported'] as $record) {
                print '<tr class="oddeven">';
                print '<td>'.dol_escape_htmltag($record['country']).'</td>';
                print '<td>'.dol_escape_htmltag($record['code']).'</td>';
                print '<td>'.dol_escape_htmltag($record['description']).'</td>';
                print '</tr>';
            }
            print '</table>';
            print '</div>';
        }
    }
    
    if (!empty($importResults['errors'])) {
        print '<div class="hts-import-error">';
        print '<strong>'.$langs->trans("ImportErrors").':</strong><br>';
        foreach ($importResults['errors'] as $errorMsg) {
            print '- '.dol_escape_htmltag($errorMsg).'<br>';
        }
        print '</div>';
    }
    
    print '</div>';
}

// Show existing HTS codes
print '<br>';
print load_fiche_titre($langs->trans("ExistingHTSCodes"), '', '');

$hts = new HTS($db);
$htsList = $hts->fetchAll('', 'country,code', 'ASC', 50);

if (!empty($htsList)) {
    print '<div class="div-table-responsive">';
    print '<table class="noborder centpercent">';
    print '<tr class="liste_titre">';
    print '<td>'.$langs->trans("Country").'</td>';
    print '<td>'.$langs->trans("HTSCode").'</td>';
    print '<td>'.$langs->trans("Description").'</td>';
    print '<td>'.$langs->trans("Status").'</td>';
    print '</tr>';
    
    foreach ($htsList as $htsRecord) {
        print '<tr class="oddeven">';
        print '<td>'.dol_escape_htmltag($htsRecord->country).'</td>';
        print '<td class="internalhts-hts-code">'.dol_escape_htmltag($htsRecord->code).'</td>';
        print '<td>'.dol_escape_htmltag($htsRecord->description).'</td>';
        print '<td>';
        if ($htsRecord->status == 1) {
            print '<span class="badge badge-status4 badge-status">Active</span>';
        } else {
            print '<span class="badge badge-status8 badge-status">Disabled</span>';
        }
        print '</td>';
        print '</tr>';
    }
    print '</table>';
    print '</div>';
    
    if (count($htsList) >= 50) {
        print '<div class="center"><em>'.$langs->trans("OnlyFirst50Shown").'</em></div>';
    }
} else {
    print '<div class="center opacitymedium">'.$langs->trans("NoHTSCodesFound").'</div>';
}

// Page end
print dol_get_fiche_end();

llxFooter();