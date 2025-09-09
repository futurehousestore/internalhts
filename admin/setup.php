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
 * \file        admin/setup.php
 * \ingroup     internalhts
 * \brief       InternalHTS setup page.
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

global $langs, $user, $conf;

// Libraries
require_once DOL_DOCUMENT_ROOT."/core/lib/admin.lib.php";
require_once '../lib/internalhts.lib.php';
require_once '../class/hts_mapping.class.php';

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
$modulepart = GETPOST('modulepart', 'aZ09');	// Used by actions_setmoduleoptions.inc.php

$value = GETPOST('value', 'alpha');
$label = GETPOST('label', 'alpha');
$scandir = GETPOST('scan_dir', 'alpha');
$type = 'internalhts';

$error = 0;
$setupnotempty = 0;

// Set this to 1 to use the factory to manage constants. Warning, the generated module will be compatible with version v15+ only
$useFormSetup = 1;
if (!class_exists('FormSetup')) {
	require_once DOL_DOCUMENT_ROOT.'/core/class/html.formsetup.class.php';
}
$formSetup = new FormSetup($db);

$setuparray = array();

// Example with a string
$item = $formSetup->newItem('INTERNALHTS_INVOICE_ADDON');
$item->setAsString();
$item->defaultFieldValue = 'mod_internalhts_standard';
$item->cssClass = 'minwidth500';
$item->nameText = $langs->trans('InternalHTSInvoiceNumberingModule');
$item->helpText = $langs->trans('InternalHTSInvoiceNumberingModuleHelp');
$setuparray[] = $item;

// Example with a select
$item = $formSetup->newItem('INTERNALHTS_ADDON_PDF');
$item->setAsSelect(array('standard' => 'Standard', 'commercial' => 'Commercial'));
$item->defaultFieldValue = 'standard';
$item->nameText = $langs->trans('InternalHTSPDFModel');
$item->helpText = $langs->trans('InternalHTSPDFModelHelp');
$setuparray[] = $item;

// Example with a multiselect
$item = $formSetup->newItem('INTERNALHTS_DEFAULT_COUNTRIES');
$countries = array(
	'US' => 'United States',
	'CA' => 'Canada',
	'MX' => 'Mexico',
	'CN' => 'China',
	'DE' => 'Germany',
	'FR' => 'France'
);
$item->setAsMultiSelect($countries);
$item->nameText = $langs->trans('DefaultCountriesOrigin');
$item->helpText = $langs->trans('DefaultCountriesOriginHelp');
$setuparray[] = $item;

// Example with a yes/no
$item = $formSetup->newItem('INTERNALHTS_AUTO_WEIGHT_CALC');
$item->setAsYesNo();
$item->nameText = $langs->trans('AutoCalculateWeight');
$item->helpText = $langs->trans('AutoCalculateWeightHelp');
$setuparray[] = $item;

$formSetup->form = $setuparray;

$dirmodels = array_merge(array('/'), (array) $conf->modules_parts['models']);

/*
 * Actions
 */

include DOL_DOCUMENT_ROOT.'/core/actions_setmoduleoptions.inc.php';

if ($action == 'updateMask') {
	$maskconstinternalhts = GETPOST('maskconstinternalhts', 'aZ09');
	$maskinternalhts = GETPOST('maskinternalhts', 'alpha');

	if ($maskconstinternalhts && preg_match('/_MASK$/', $maskconstinternalhts)) {
		$res = dolibarr_set_const($db, $maskconstinternalhts, $maskinternalhts, 'chaine', 0, '', $conf->entity);
		if (!($res > 0)) {
			$error++;
		}
	}

	if (!$error) {
		setEventMessages($langs->trans("SetupSaved"), null, 'mesgs');
	} else {
		setEventMessages($langs->trans("Error"), null, 'errors');
	}
} elseif ($action == 'setmod') {
	// TODO Check if numbering module chosen can be activated by calling method canBeActivated
	$tmpobjectkey = GETPOST('object', 'aZ09');
	if (!empty($tmpobjectkey)) {
		$constforval = 'INTERNALHTS_'.strtoupper($tmpobjectkey)."_ADDON";
		dolibarr_set_const($db, $constforval, $value, 'chaine', 0, '', $conf->entity);
	}
} elseif ($action == 'set') {
	// Activate a model
	$ret = addDocumentModel($value, $type, $label, $scandir);
} elseif ($action == 'del') {
	$ret = delDocumentModel($value, $type);
	if ($ret > 0) {
		$tmpobjectkey = GETPOST('object', 'aZ09');
		if (!empty($tmpobjectkey)) {
			$constforval = 'INTERNALHTS_'.strtoupper($tmpobjectkey).'_ADDON_PDF';
			if ($conf->global->$constforval == "$value") {
				dolibarr_del_const($db, $constforval, $conf->entity);
			}
		}
	}
} elseif ($action == 'setdoc') {
	// Set or unset default model
	$tmpobjectkey = GETPOST('object', 'aZ09');
	if (!empty($tmpobjectkey)) {
		$constforval = 'INTERNALHTS_'.strtoupper($tmpobjectkey).'_ADDON_PDF';
		if (dolibarr_set_const($db, $constforval, $value, 'chaine', 0, '', $conf->entity)) {
			// The constant that was read before the new set
			// We therefore requires a variable to have a coherent view
			$conf->global->$constforval = $value;
		}

		// We disable/enable the document template (into llx_document_model table)
		$ret = delDocumentModel($value, $type);
		if ($ret > 0) {
			$ret = addDocumentModel($value, $type, $label, $scandir);
		}
	}
} elseif ($action == 'unsetdoc') {
	$tmpobjectkey = GETPOST('object', 'aZ09');
	if (!empty($tmpobjectkey)) {
		$constforval = 'INTERNALHTS_'.strtoupper($tmpobjectkey).'_ADDON_PDF';
		dolibarr_del_const($db, $constforval, $conf->entity);
	}
}

// Handle HTS code import
if ($action == 'import_hts') {
	$upload_dir = $conf->internalhts->dir_temp;
	if (!dol_is_dir($upload_dir)) {
		dol_mkdir($upload_dir);
	}

	if (isset($_FILES['hts_file']) && $_FILES['hts_file']['error'] == 0) {
		$target_file = $upload_dir . '/' . basename($_FILES['hts_file']['name']);
		
		if (move_uploaded_file($_FILES['hts_file']['tmp_name'], $target_file)) {
			$imported = HTSMapping::importHTSCodes($db, $target_file, $user);
			
			if ($imported > 0) {
				setEventMessages($langs->trans("HTSCodesImported", $imported), null, 'mesgs');
			} else {
				setEventMessages($langs->trans("ErrorImportFailed"), null, 'errors');
			}
			
			// Clean up
			unlink($target_file);
		} else {
			setEventMessages($langs->trans("ErrorImportFailed"), null, 'errors');
		}
	} else {
		setEventMessages($langs->trans("ErrorInvalidFile"), null, 'errors');
	}
}

/*
 * View
 */

$form = new Form($db);

$help_url = '';
$page_name = "InternalHTSSetup";

llxHeader('', $langs->trans($page_name), $help_url);

// Subheader
$linkback = '<a href="'.($backtopage ? $backtopage : DOL_URL_ROOT.'/admin/modules.php?restore_lastsearch_values=1').'">'.$langs->trans("BackToModuleList").'</a>';

print load_fiche_titre($langs->trans($page_name), $linkback, 'title_setup');

// Configuration header
$head = internalhtsAdminPrepareHead();
print dol_get_fiche_head($head, 'settings', $langs->trans($page_name), -1, "internalhts@internalhts");

// Setup page goes here
print info_admin($langs->trans("InternalHTSSetupPage"));

if ($action == 'edit') {
	print $formSetup->generateOutput(true);
	print '<br>';
} elseif (!empty($formSetup->items)) {
	print '<form method="POST" action="'.$_SERVER['PHP_SELF'].'">';
	print '<input type="hidden" name="token" value="'.newToken().'">';
	print '<input type="hidden" name="action" value="edit">';
	print $formSetup->generateOutput();
	print '<div class="tabsAction">';
	print '<input type="submit" class="button button-edit" name="edit" value="'.$langs->trans("Modify").'">';
	print '</div>';
	print '</form>';
}

print '<br>';

// HTS Code Import Section
print load_fiche_titre($langs->trans("HTSCodeImport"), '', '');

print '<form method="POST" action="'.$_SERVER['PHP_SELF'].'" enctype="multipart/form-data">';
print '<input type="hidden" name="token" value="'.newToken().'">';
print '<input type="hidden" name="action" value="import_hts">';

print '<table class="noborder centpercent">';
print '<tr class="liste_titre">';
print '<td>'.$langs->trans("ImportHTSCodesFile").'</td>';
print '<td>'.$langs->trans("Action").'</td>';
print '</tr>';

print '<tr class="oddeven">';
print '<td>';
print '<input type="file" name="hts_file" accept=".csv" required>';
print '<br><small>'.$langs->trans("HTSCodeImportHelp").'</small>';
print '</td>';
print '<td>';
print '<input type="submit" class="button" value="'.$langs->trans("Import").'">';
print '</td>';
print '</tr>';

print '</table>';
print '</form>';

print '<br>';

// Numbering models for InternalHTS Invoice
print load_fiche_titre($langs->trans("InternalHTSInvoiceNumberingModels"), '', '');

print '<table class="noborder centpercent">';
print '<tr class="liste_titre">';
print '<td>'.$langs->trans("Name").'</td>';
print '<td>'.$langs->trans("Description").'</td>';
print '<td class="nowrap">'.$langs->trans("Example").'</td>';
print '<td class="center" width="60">'.$langs->trans("Status").'</td>';
print '<td class="center" width="16">'.$langs->trans("ShortInfo").'</td>';
print '</tr>'."\n";

clearstatcache();

$dir = DOL_DOCUMENT_ROOT."/core/modules/internalhts/";
if (is_dir($dir)) {
	$handle = opendir($dir);
	if (is_resource($handle)) {
		while (($file = readdir($handle)) !== false) {
			if (preg_match('/^(mod_.*)\.php$/i', $file, $reg)) {
				$file = $reg[1];
				$classname = $file;

				require_once $dir.$file.'.php';

				$module = new $file($db);

				// Show modules according to features level
				if ($module->version == 'development' && $conf->global->MAIN_FEATURES_LEVEL < 2) {
					continue;
				}
				if ($module->version == 'experimental' && $conf->global->MAIN_FEATURES_LEVEL < 1) {
					continue;
				}

				if ($module->isEnabled()) {
					print '<tr class="oddeven"><td width="100">';
					print (empty($module->name) ? $classname : $module->name);
					print "</td><td>\n";
					print $module->info();
					print '</td>';

					// Show example of numbering model
					print '<td class="nowrap">';
					$tmp = $module->getExample();
					if (preg_match('/^Error/', $tmp)) {
						$langs->load("errors");
						print '<div class="error">'.$langs->trans($tmp).'</div>';
					} elseif ($tmp == 'NotConfigured') {
						print '<span class="opacitymedium">'.$langs->trans($tmp).'</span>';
					} else {
						print $tmp;
					}
					print '</td>'."\n";

					print '<td class="center">';
					$constforvar = 'INTERNALHTS_INTERNALHTS_INVOICE_ADDON';
					if ($conf->global->$constforvar == $file) {
						print img_picto($langs->trans("Activated"), 'switch_on');
					} else {
						print '<a href="'.$_SERVER["PHP_SELF"].'?action=setmod&token='.newToken().'&object=internalhts_invoice&value='.urlencode($file).'">';
						print img_picto($langs->trans("Disabled"), 'switch_off');
						print '</a>';
					}
					print '</td>';

					$internalhts_invoice = new InternalHTSInvoice($db);
					$internalhts_invoice->initAsSpecimen();

					// Info
					$htmltooltip = '';
					$htmltooltip .= ''.$langs->trans("Version").': <b>'.$module->getVersion().'</b><br>';

					$nextval = $module->getNextValue($internalhts_invoice);
					if ("$nextval" != $langs->trans("NotAvailable")) {  // Keep " on nextval
						$htmltooltip .= ''.$langs->trans("NextValue").': ';
						if ($nextval) {
							if (preg_match('/^Error/', $nextval) || $nextval == 'NotConfigured') {
								$nextval = $langs->trans($nextval);
							}
							$htmltooltip .= $nextval.'<br>';
						} else {
							$htmltooltip .= $langs->trans($module->error).'<br>';
						}
					}

					print '<td class="center">';
					print $form->textwithpicto('', $htmltooltip, 1, 0);
					print '</td>';

					print "</tr>\n";
				}
			}
		}
		closedir($handle);
	}
}

print '</table><br>'."\n";

// Page end
print dol_get_fiche_end();

llxFooter();
$db->close();