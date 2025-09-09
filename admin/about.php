<?php
/* Copyright (C) 2024 FutureHouse Store
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
 * \file        admin/about.php
 * \ingroup     internalhts
 * \brief       InternalHTS about page.
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
if (!$res && file_exists("../main.inc.php")) {
	$res = @include "../main.inc.php";
}
if (!$res && file_exists("../../main.inc.php")) {
	$res = @include "../../main.inc.php";
}
if (!$res && file_exists("../../../main.inc.php")) {
	$res = @include "../../../main.inc.php";
}
if (!$res) {
	die("Include of main fails");
}

global $langs, $user;

// Libraries
require_once DOL_DOCUMENT_ROOT."/core/lib/admin.lib.php";
require_once '../lib/internalhts.lib.php';

// Translations
$langs->loadLangs(array("admin", "internalhts@internalhts"));

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

// None

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

// About
print '<table class="noborder centpercent">';
print '<tr class="liste_titre">';
print '<td>'.$langs->trans("Module").'</td>';
print '<td>'.$langs->trans("Description").'</td>';
print '</tr>';

print '<tr class="oddeven">';
print '<td width="200"><strong>'.$langs->trans("ModuleInternalHTSName").'</strong></td>';
print '<td>'.$langs->trans("ModuleInternalHTSDesc").'</td>';
print '</tr>';

print '<tr class="oddeven">';
print '<td>'.$langs->trans("Version").'</td>';
print '<td>1.0.0</td>';
print '</tr>';

print '<tr class="oddeven">';
print '<td>'.$langs->trans("Author").'</td>';
print '<td>FutureHouse Store</td>';
print '</tr>';

print '<tr class="oddeven">';
print '<td>'.$langs->trans("License").'</td>';
print '<td>GPLv3+</td>';
print '</tr>';

print '</table>';

print '<br>';

print '<div class="fichecenter">';
print '<div class="fichethirdleft">';

print '<h3>'.$langs->trans("Features").'</h3>';
print '<ul>';
print '<li>'.$langs->trans("InternalHTSInvoice").' - '.$langs->trans("ManageInternalInvoicesWithHTSCodes").'</li>';
print '<li>'.$langs->trans("HTSMapping").' - '.$langs->trans("MapProductsToHTSCodes").'</li>';
print '<li>'.$langs->trans("ExportCommercialInvoice").' - '.$langs->trans("GeneratePDFCommercialInvoices").'</li>';
print '<li>'.$langs->trans("ExportPackingList").' - '.$langs->trans("GeneratePDFPackingLists").'</li>';
print '<li>'.$langs->trans("ExportBrokerCSV").' - '.$langs->trans("ExportDataForCustomsBrokers").'</li>';
print '<li>'.$langs->trans("ExportBrokerJSON").' - '.$langs->trans("ExportDataInJSONFormat").'</li>';
print '</ul>';

print '</div>';
print '<div class="fichetwothirdright">';

print '<h3>'.$langs->trans("Requirements").'</h3>';
print '<ul>';
print '<li>Dolibarr 11.0+</li>';
print '<li>PHP 7.0+</li>';
print '<li>MySQL/MariaDB</li>';
print '</ul>';

print '<h3>'.$langs->trans("Installation").'</h3>';
print '<ol>';
print '<li>'.$langs->trans("ExtractFilesToDolibarrCustomFolder").'</li>';
print '<li>'.$langs->trans("ActivateModuleFromModuleSetup").'</li>';
print '<li>'.$langs->trans("ConfigureModuleFromThisPage").'</li>';
print '<li>'.$langs->trans("ImportHTSCodesFromCSVFile").'</li>';
print '</ol>';

print '</div>';
print '</div>';

print '<div class="clearboth"></div>';

print '<h3>'.$langs->trans("Support").'</h3>';
print '<p>'.$langs->trans("ForSupportContactDeveloper").'</p>';

// Page end
print dol_get_fiche_end();

llxFooter();
$db->close();