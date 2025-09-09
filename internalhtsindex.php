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
 * \file       internalhtsindex.php
 * \ingroup    internalhts
 * \brief      Home page of internalhts top menu
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

require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';
dol_include_once('/internalhts/class/internalhts_invoice.class.php');
dol_include_once('/internalhts/class/hts_mapping.class.php');

// Load translation files required by the page
$langs->loadLangs(array("internalhts@internalhts"));

$action = GETPOST('action', 'aZ09');

// Security check
if (!$user->rights->internalhts->read) {
	accessforbidden();
}

// Initialize technical object to manage hooks of page. Note that conf->hooks_modules contains array of hook context
$hookmanager->initHooks(array('internalhtsindex'));

/*
 * Actions
 */

// None

/*
 * View
 */

$form = new Form($db);
$formfile = new FormFile($db);

llxHeader("", $langs->trans("InternalHTSArea"));

print load_fiche_titre($langs->trans("InternalHTSArea"), '', 'internalhts.png@internalhts');

print '<div class="fichecenter"><div class="fichethirdleft">';

// Statistics boxes
$boxstat = '';

// Latest invoices
if ($user->rights->internalhts->read) {
	$sql = "SELECT i.rowid, i.ref, i.date_invoice, i.total_ttc, i.status, s.nom as customer_name";
	$sql .= " FROM ".MAIN_DB_PREFIX."internalhts_invoice as i";
	$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."societe as s ON i.fk_soc = s.rowid";
	$sql .= " WHERE i.entity IN (".getEntity('internalhts_invoice').")";
	$sql .= " ORDER BY i.date_creation DESC";
	$sql .= $db->plimit(5);

	$resql = $db->query($sql);
	if ($resql) {
		$num = $db->num_rows($resql);
		
		print '<div class="div-table-responsive-no-min">';
		print '<table class="noborder centpercent">';
		print '<tr class="liste_titre">';
		print '<th colspan="4">'.$langs->trans("LatestInternalHTSInvoices").'</th>';
		print '</tr>';
		
		if ($num > 0) {
			$i = 0;
			while ($i < $num) {
				$obj = $db->fetch_object($resql);
				
				$invoice = new InternalHTSInvoice($db);
				$invoice->id = $obj->rowid;
				$invoice->ref = $obj->ref;
				$invoice->status = $obj->status;
				
				print '<tr class="oddeven">';
				print '<td class="nowrap">'.$invoice->getNomUrl(1).'</td>';
				print '<td>'.dol_print_date($db->jdate($obj->date_invoice), 'day').'</td>';
				print '<td class="right">'.price($obj->total_ttc).'</td>';
				print '<td class="right">'.$invoice->getLibStatut(5).'</td>';
				print '</tr>';
				
				$i++;
			}
		} else {
			print '<tr class="oddeven"><td colspan="4" class="opacitymedium">'.$langs->trans("NoInvoices").'</td></tr>';
		}
		
		print '</table>';
		print '</div>';
		print '<br>';
		
		$db->free($resql);
	} else {
		dol_print_error($db);
	}
}

print '</div><div class="fichetwothirdright">';

// Statistics
if ($user->rights->internalhts->read) {
	// Count invoices by status
	$sql = "SELECT status, COUNT(*) as nb FROM ".MAIN_DB_PREFIX."internalhts_invoice";
	$sql .= " WHERE entity IN (".getEntity('internalhts_invoice').")";
	$sql .= " GROUP BY status";
	
	$resql = $db->query($sql);
	if ($resql) {
		$stats = array();
		while ($obj = $db->fetch_object($resql)) {
			$stats[$obj->status] = $obj->nb;
		}
		$db->free($resql);
		
		print '<div class="div-table-responsive-no-min">';
		print '<table class="noborder centpercent">';
		print '<tr class="liste_titre">';
		print '<th colspan="2">'.$langs->trans("Statistics").'</th>';
		print '</tr>';
		
		$invoice = new InternalHTSInvoice($db);
		
		print '<tr class="oddeven">';
		print '<td>'.$langs->trans("Draft").'</td>';
		print '<td class="right">'.($stats[InternalHTSInvoice::STATUS_DRAFT] ?? 0).'</td>';
		print '</tr>';
		
		print '<tr class="oddeven">';
		print '<td>'.$langs->trans("Validated").'</td>';
		print '<td class="right">'.($stats[InternalHTSInvoice::STATUS_VALIDATED] ?? 0).'</td>';
		print '</tr>';
		
		print '<tr class="oddeven">';
		print '<td>'.$langs->trans("Paid").'</td>';
		print '<td class="right">'.($stats[InternalHTSInvoice::STATUS_PAID] ?? 0).'</td>';
		print '</tr>';
		
		print '</table>';
		print '</div>';
		print '<br>';
	}
}

// Count HTS mappings
if ($user->rights->internalhts->read) {
	$sql = "SELECT COUNT(*) as nb FROM ".MAIN_DB_PREFIX."internalhts_hts_mapping WHERE active = 1";
	$resql = $db->query($sql);
	if ($resql) {
		$obj = $db->fetch_object($resql);
		$nb_mappings = $obj->nb;
		$db->free($resql);
		
		print '<div class="div-table-responsive-no-min">';
		print '<table class="noborder centpercent">';
		print '<tr class="liste_titre">';
		print '<th colspan="2">'.$langs->trans("HTSMapping").'</th>';
		print '</tr>';
		
		print '<tr class="oddeven">';
		print '<td>'.$langs->trans("ActiveMappings").'</td>';
		print '<td class="right">'.$nb_mappings.'</td>';
		print '</tr>';
		
		print '</table>';
		print '</div>';
		print '<br>';
	}
}

print '</div></div>';

// Quick actions
print '<div class="clearboth"></div>';
print '<div class="fichecenter">';

if ($user->rights->internalhts->write) {
	print '<div class="tabsAction">';
	print '<a class="butAction" href="'.dol_buildpath('/internalhts/internalhts_invoice_card.php?action=create', 1).'">'.$langs->trans("NewInternalHTSInvoice").'</a>';
	if ($user->admin) {
		print '<a class="butAction" href="'.dol_buildpath('/internalhts/hts_mapping_card.php?action=create', 1).'">'.$langs->trans("NewHTSMapping").'</a>';
	}
	print '</div>';
	print '<br>';
}

print '</div>';

// Show hook
$parameters = array();
$reshook = $hookmanager->executeHooks('internalhtsindex', $parameters, $object, $action); // Note that $action and $object may have been modified by some hooks
if ($reshook < 0) {
	setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');
}

// End of page
llxFooter();
$db->close();