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
 * \file       admin/about.php
 * \ingroup    internalhts
 * \brief      About page of module InternalHTS.
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

// Translations
$langs->loadLangs(array("admin", "internalhts@internalhts"));

// Access control
if (!$user->admin) {
    accessforbidden();
}

// Parameters
$backtopage = GETPOST('backtopage', 'alpha');

/*
 * View
 */

$form = new Form($db);

$help_url = '';
$page_name = "InternalHTSAbout";

llxHeader('', $langs->trans($page_name), $help_url);

// Subheader
$linkback = '<a href="'.($backtopage ? $backtopage : DOL_URL_ROOT.'/admin/modules.php?restore_lastsearch_values=1').'">'.$langs->trans("BackToModuleList").'</a>';

print load_fiche_titre($langs->trans($page_name), $linkback, 'title_setup');

// Configuration header
$head = internalhtsAdminPrepareHead();
print dol_get_fiche_head($head, 'about', $langs->trans($page_name), -1, "internalhts@internalhts");

print '<div class="div-table-responsive-no-min">';
print '<table class="noborder centpercent">';

print '<tr class="liste_titre">';
print '<td colspan="2">'.$langs->trans("About").'</td>';
print '</tr>';

print '<tr class="oddeven">';
print '<td width="200">'.$langs->trans("Version").'</td>';
print '<td>0.1.0</td>';
print '</tr>';

print '<tr class="oddeven">';
print '<td>'.$langs->trans("Description").'</td>';
print '<td>'.$langs->trans("ModuleInternalHTSDesc").'</td>';
print '</tr>';

print '<tr class="oddeven">';
print '<td>'.$langs->trans("Author").'</td>';
print '<td>InternalHTS Development Team</td>';
print '</tr>';

print '<tr class="oddeven">';
print '<td>'.$langs->trans("License").'</td>';
print '<td>GPL v3+</td>';
print '</tr>';

print '</table>';
print '</div>';

print '<br>';

print '<div class="div-table-responsive-no-min">';
print '<table class="noborder centpercent">';

print '<tr class="liste_titre">';
print '<td colspan="2">'.$langs->trans("Features").'</td>';
print '</tr>';

print '<tr class="oddeven">';
print '<td width="200">HTS Code Management</td>';
print '<td>Import and manage HTS (Harmonized Tariff Schedule) codes by country</td>';
print '</tr>';

print '<tr class="oddeven">';
print '<td>Commercial Invoices</td>';
print '<td>Create internal commercial invoices with automatic calculations</td>';
print '</tr>';

print '<tr class="oddeven">';
print '<td>PDF Generation</td>';
print '<td>Generate PDF commercial invoices for international shipping</td>';
print '</tr>';

print '<tr class="oddeven">';
print '<td>CSV Import</td>';
print '<td>Bulk import HTS codes from CSV files with dry-run capability</td>';
print '</tr>';

print '<tr class="oddeven">';
print '<td>Multi-country Support</td>';
print '<td>Support for different HTS codes by country</td>';
print '</tr>';

print '<tr class="oddeven">';
print '<td>Auto-calculation</td>';
print '<td>Automatic calculation of line totals and customs values</td>';
print '</tr>';

print '<tr class="oddeven">';
print '<td>Status Management</td>';
print '<td>Draft/Validated status workflow with proper permissions</td>';
print '</tr>';

print '<tr class="oddeven">';
print '<td>Dark Mode</td>';
print '<td>Full dark mode support for better user experience</td>';
print '</tr>';

print '</table>';
print '</div>';

// Page end
print dol_get_fiche_end();

llxFooter();